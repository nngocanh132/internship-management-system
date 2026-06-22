<?php
require_once __DIR__ . '/../Controllers/BaseController.php';
require_once __DIR__ . '/../Models/ApplicationModel.php';
require_once __DIR__ . '/../Models/InternshipModel.php';
require_once __DIR__ . '/../Models/RegistrationModel.php';

class ApplicationController extends BaseController
{
    private ApplicationModel  $appModel;
    private InternshipModel   $jobModel;
    private RegistrationModel $regModel;

    public function __construct($conn)
    {
        parent::__construct($conn);
        $this->appModel = new ApplicationModel($conn);
        $this->jobModel = new InternshipModel($conn);
        $this->regModel = new RegistrationModel($conn);
    }

    // ── Admin: danh sách tất cả đơn ──────────────────────────────────
    public function list(): void
    {
        requireRole('admin');

        $status_f = sanitize($_GET['status'] ?? '');
        $apps     = $this->appModel->getAllWithFilter($status_f);
        $counts   = $this->appModel->countByStatus();

        $this->render(BASE_PATH_FS . 'app/Views/applications/list.php', [
            'apps'     => $apps,
            'counts'   => $counts,
            'status_f' => $status_f,
        ]);
    }

    // ── Admin: xét duyệt 1 đơn ───────────────────────────────────────
    public function review(): void
    {
        requireRole('admin');

        $id = (int)($_GET['id'] ?? 0);
        if (!$id) redirect('list.php');

        $a = $this->appModel->getDetailById($id);
        if (!$a) { setFlash('error', 'Không tìm thấy đơn.'); redirect('list.php'); }

        $others = $this->appModel->getOthersByStudent($a['student_id'], $id);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = sanitize($_POST['action'] ?? '');
            $note   = sanitize($_POST['admin_note'] ?? '');
            if ($action === 'approve') {
                $this->appModel->approve($id, $note);
                setFlash('success', '✅ Đã duyệt — Chuyển sang công ty xét duyệt.');
            } elseif ($action === 'reject') {
                $this->appModel->reject($id, $note);
                setFlash('success', 'Đã từ chối đơn ứng tuyển.');
            }
            redirect('list.php');
        }

        [$lbl, $bg, $c] = appStatusLabel($a['status']);
        $av    = $a['s_avatar']
               ? UPLOAD_URL . '/' . $a['s_avatar']
               : 'https://ui-avatars.com/api/?name=' . urlencode($a['full_name']) . '&background=5D7B6F&color=fff&size=100';
        $clogo = $a['c_logo']
               ? UPLOAD_URL . '/' . $a['c_logo']
               : 'https://ui-avatars.com/api/?name=' . urlencode($a['company_name']) . '&background=A4C3A2&color=2A3F38&size=60';

        $this->render(BASE_PATH_FS . 'app/Views/applications/review.php', [
            'a'      => $a,
            'others' => $others,
            'lbl'    => $lbl,
            'bg'     => $bg,
            'c'      => $c,
            'av'     => $av,
            'clogo'  => $clogo,
        ]);
    }

    // ── Student: đơn của tôi ─────────────────────────────────────────
    public function myApplications(): void
    {
        requireRole('student');

        $uid = $_SESSION['user_id'];
        $sp  = $this->conn->prepare("SELECT student_id FROM student_profiles WHERE user_id=?");
        $sp->bind_param('i', $uid); $sp->execute();
        $sid = $sp->get_result()->fetch_assoc()['student_id'] ?? 0;

        if ($sid) $this->appModel->syncCompletedStatus($sid);
        $rows = $sid ? $this->appModel->getByStudent($sid) : [];

        $this->render(BASE_PATH_FS . 'app/Views/applications/my_applications.php', [
            'rows' => $rows,
        ]);
    }

    // ── Student: nộp đơn ứng tuyển ───────────────────────────────────
    public function apply(): void
    {
        requireRole('student');
        requireProfileComplete($this->conn);

        $uid = $_SESSION['user_id'];
        $sp  = $this->conn->prepare("SELECT * FROM student_profiles WHERE user_id=?");
        $sp->bind_param('i', $uid); $sp->execute();
        $student = $sp->get_result()->fetch_assoc();

        if (!$student) {
            setFlash('error', 'Không tìm thấy hồ sơ sinh viên.');
            redirect('../internships/browse.php');
        }
        $sid = $student['student_id'];

        $iid = (int)($_GET['internship_id'] ?? $_POST['internship_id'] ?? 0);
        if (!$iid) redirect('../internships/browse.php');

        $existing_reg = $this->regModel->getExistingByStudent($sid);
        if ($existing_reg) {
            $msg = $existing_reg['status'] === 'completed'
                ? 'Bạn đã hoàn thành kỳ thực tập. Không thể nộp đơn mới.'
                : 'Bạn đang trong kỳ thực tập. Không thể nộp đơn mới.';
            setFlash('error', $msg);
            redirect('../internships/browse.php');
        }

        $job = $this->jobModel->getOpenById($iid);
        if (!$job) { setFlash('error', 'Vị trí không còn nhận đơn.'); redirect('../internships/browse.php'); }

        $existing = $this->appModel->getExistingApplication($sid, $iid);
        $errors   = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($existing) { setFlash('info', 'Bạn đã nộp đơn vị trí này rồi.'); redirect('my_applications.php'); }

            $cv_path = null;
            if (!empty($_FILES['cv']['tmp_name'])) {
                $up = uploadFile($_FILES['cv'], 'cvs', ['pdf', 'doc', 'docx'], 5);
                if ($up['ok']) $cv_path = $up['path'];
                else $errors[] = 'CV: ' . $up['err'];
            }
            if (!$cv_path) $errors[] = 'Vui lòng upload file CV (PDF/DOC).';

            if (empty($errors)) {
                if ($this->appModel->create($sid, $iid, $cv_path)) {
                    setFlash('success', '✅ Đã nộp đơn ứng tuyển! Vui lòng chờ nhà trường xét duyệt.');
                    redirect('my_applications.php');
                } else {
                    $errors[] = str_contains($this->appModel->lastError(), 'Duplicate')
                        ? 'Bạn đã ứng tuyển vị trí này rồi.'
                        : 'Lỗi: ' . $this->appModel->lastError();
                }
            }
        }

        $logoUrl = $job['logo']
            ? UPLOAD_URL . '/' . $job['logo']
            : 'https://ui-avatars.com/api/?name=' . urlencode($job['company_name']) . '&background=A4C3A2&color=2A3F38&size=80';

        $this->render(BASE_PATH_FS . 'app/Views/applications/apply.php', [
            'job'      => $job,
            'student'  => $student,
            'existing' => $existing,
            'errors'   => $errors,
            'iid'      => $iid,
            'logoUrl'  => $logoUrl,
        ]);
    }

    // ── Company: xem hồ sơ ứng viên đã được trường duyệt ────────────
    public function companyReview(): void
    {
        requireRole('company');

        $uid = $_SESSION['user_id'];
        $cq  = $this->conn->prepare("SELECT company_id FROM company_profiles WHERE user_id=?");
        $cid = 0;
        if ($cq) { $cq->bind_param('i', $uid); $cq->execute(); $cid = $cq->get_result()->fetch_assoc()['company_id'] ?? 0; }

        if (isset($_GET['approve']) && $cid) {
            $id = (int)$_GET['approve'];
            $u  = $this->conn->prepare("UPDATE applications SET status='approved_company' WHERE application_id=? AND internship_id IN (SELECT internship_id FROM internships WHERE company_id=?)");
            $u->bind_param('ii', $id, $cid); $u->execute();
            setFlash('success', '✅ Đã chấp nhận sinh viên! Có thể nhắn tin hẹn lịch phỏng vấn.');
            redirect('company_review.php');
        }
        if (isset($_GET['reject']) && $cid) {
            $id = (int)$_GET['reject'];
            $u  = $this->conn->prepare("UPDATE applications SET status='rejected_company' WHERE application_id=? AND internship_id IN (SELECT internship_id FROM internships WHERE company_id=?)");
            $u->bind_param('ii', $id, $cid); $u->execute();
            setFlash('success', 'Đã từ chối ứng viên.');
            redirect('company_review.php');
        }

        $apps = $this->conn->prepare(
            "SELECT a.*,sp.full_name,sp.student_code,sp.gpa,sp.major,sp.avatar AS s_avatar,sp.student_id,
             i.title,u.email
             FROM applications a
             JOIN internships i ON a.internship_id=i.internship_id
             JOIN student_profiles sp ON a.student_id=sp.student_id
             JOIN users u ON sp.user_id=u.user_id
             WHERE i.company_id=? AND a.status='approved_admin'
             ORDER BY sp.gpa DESC, a.applied_at ASC"
        );
        $apps->bind_param('i', $cid); $apps->execute();
        $rows = $apps->get_result()->fetch_all(MYSQLI_ASSOC);

        $this->render(BASE_PATH_FS . 'app/Views/applications/company_review.php', [
            'rows' => $rows,
        ]);
    }

    // ── Company: danh sách ứng viên ───────────────────────────────────
    public function companyCandidates(): void
    {
        requireRole('company');

        $uid = $_SESSION['user_id'];
        $cq  = $this->conn->prepare("SELECT company_id FROM company_profiles WHERE user_id=?");
        $cid = 0;
        if ($cq) { $cq->bind_param('i', $uid); $cq->execute(); $cid = $cq->get_result()->fetch_assoc()['company_id'] ?? 0; }

        $job_filter = (int)($_GET['job'] ?? 0);
        $redir = 'company_candidates.php' . ($job_filter ? "?job=$job_filter" : '');

        if (isset($_GET['approve']) && $cid) {
            $this->appModel->approveByCompany((int)$_GET['approve'], $cid);
            setFlash('success', '✅ Đã chấp nhận! Nhắn tin để hẹn lịch phỏng vấn.');
            redirect($redir);
        }
        if (isset($_GET['reject']) && $cid) {
            $this->appModel->rejectByCompany((int)$_GET['reject'], $cid);
            setFlash('info', 'Đã từ chối ứng viên.');
            redirect($redir);
        }
        if (isset($_GET['pass_interview']) && $cid) {
            $id = (int)$_GET['pass_interview'];
            $this->appModel->markInterviewPassed($id, $cid);
            $ins = $this->conn->prepare("INSERT INTO interviews (application_id,result) VALUES (?,'passed') ON DUPLICATE KEY UPDATE result='passed'");
            if ($ins) { $ins->bind_param('i', $id); $ins->execute(); }
            setFlash('success', '🎉 Đánh dấu đậu phỏng vấn!');
            redirect($redir);
        }
        if (isset($_GET['fail_interview']) && $cid) {
            $id = (int)$_GET['fail_interview'];
            $this->appModel->markInterviewFailed($id, $cid);
            $ins = $this->conn->prepare("INSERT INTO interviews (application_id,result) VALUES (?,'failed') ON DUPLICATE KEY UPDATE result='failed'");
            if ($ins) { $ins->bind_param('i', $id); $ins->execute(); }
            setFlash('info', 'Đã đánh dấu rớt phỏng vấn.');
            redirect($redir);
        }
        if (isset($_GET['start_internship']) && $cid) {
            $id  = (int)$_GET['start_internship'];
            $app = $this->appModel->getForInternshipStart($id, $cid);
            if ($app) {
                $this->appModel->setActive($id);
                $this->regModel->create($app['student_id'], $cid, $app['internship_id']);
                setFlash('success', '🚀 Sinh viên đã bắt đầu thực tập! Admin sẽ phân công GVHD.');
            }
            redirect($redir);
        }
        if (isset($_GET['delete']) && $cid) {
            $this->appModel->delete((int)$_GET['delete'], $cid);
            setFlash('success', 'Đã xóa đơn ứng tuyển.');
            redirect($redir);
        }

        $my_jobs    = $cid ? safeQuery($this->conn, "SELECT internship_id,title FROM internships WHERE company_id=$cid ORDER BY created_at DESC") : [];
        $candidates = $this->appModel->getCandidatesByCompany($cid, $job_filter);

        $cnt = [];
        foreach (['pending_admin','approved_admin','approved_company','rejected_company','interview_passed','internship_active'] as $s)
            $cnt[$s] = 0;
        foreach ($candidates as $row) $cnt[$row['status']] = ($cnt[$row['status']] ?? 0) + 1;

        $this->render(BASE_PATH_FS . 'app/Views/applications/company_candidates.php', [
            'candidates' => $candidates,
            'my_jobs'    => $my_jobs,
            'job_filter' => $job_filter,
            'cnt'        => $cnt,
        ]);
    }
}
