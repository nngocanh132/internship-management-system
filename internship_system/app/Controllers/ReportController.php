<?php
require_once __DIR__ . '/../Controllers/BaseController.php';
require_once __DIR__ . '/../Models/ReportModel.php';
require_once __DIR__ . '/../Models/RegistrationModel.php';

class ReportController extends BaseController
{
    private ReportModel       $reportModel;
    private RegistrationModel $regModel;

    public function __construct($conn)
    {
        parent::__construct($conn);
        $this->reportModel = new ReportModel($conn);
        $this->regModel    = new RegistrationModel($conn);
    }

    // ── Lecturer: trang riêng duyệt báo cáo (form inline) ───────────
    public function reviewByLecturer(): void
    {
        requireRole('lecturer');

        $uid = $_SESSION['user_id'];
        $lq  = $this->conn->prepare("SELECT lecturer_id FROM lecturer_profiles WHERE user_id=?");
        $lid = 0;
        if ($lq) { $lq->bind_param('i', $uid); $lq->execute(); $lid = $lq->get_result()->fetch_assoc()['lecturer_id'] ?? 0; }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $rid     = (int)($_POST['report_id'] ?? 0);
            $comment = sanitize($_POST['comment'] ?? '');
            $action  = sanitize($_POST['action'] ?? '');
            $status  = $action === 'approve' ? 'approved' : 'rejected';

            $u = $this->conn->prepare("UPDATE internship_reports SET status=?,lecturer_comment=?,reviewed_at=NOW() WHERE report_id=? AND registration_id IN (SELECT registration_id FROM internship_registrations WHERE lecturer_id=?)");
            if ($u) { $u->bind_param('ssii', $status, $comment, $rid, $lid); $u->execute(); }

            if ($action === 'approve') {
                $this->reportModel->approve($rid);
                setFlash('success', '✅ Đã duyệt báo cáo — Kỳ thực tập đã hoàn thành!');
            } else {
                setFlash('info', '📝 Đã yêu cầu sinh viên chỉnh sửa báo cáo.');
            }
            redirect('review.php');
        }

        $rows = [];
        if ($lid) {
            $rq = $this->conn->prepare(
                "SELECT rp.*,sp.full_name,sp.student_code,sp.avatar AS s_av,cp.company_name,i.title
                 FROM internship_reports rp
                 JOIN internship_registrations ir ON rp.registration_id=ir.registration_id
                 JOIN student_profiles sp ON ir.student_id=sp.student_id
                 JOIN internships i ON ir.internship_id=i.internship_id
                 JOIN company_profiles cp ON ir.company_id=cp.company_id
                 WHERE ir.lecturer_id=? ORDER BY rp.submitted_at DESC"
            );
            if ($rq) { $rq->bind_param('i', $lid); $rq->execute(); $rows = $rq->get_result()->fetch_all(MYSQLI_ASSOC); }
        }

        $this->render(BASE_PATH_FS . 'app/Views/reports/review.php', ['rows' => $rows]);
    }

    // ── Admin/Lecturer: danh sách báo cáo ────────────────────────────
    public function list(): void
    {
        requireRole(['admin', 'lecturer']);

        $uid  = $_SESSION['user_id'];
        $role = getRole();

        if (isset($_GET['approve'])) {
            if ($role !== 'lecturer') { setFlash('error', '❌ Chỉ Giảng viên hướng dẫn mới có quyền duyệt.'); redirect('list.php'); }
            $this->reportModel->approve((int)$_GET['approve']);
            setFlash('success', '✅ Đã duyệt báo cáo — Kỳ thực tập hoàn thành!');
            redirect('list.php');
        }
        if (isset($_GET['reject'])) {
            if ($role !== 'lecturer') { setFlash('error', '❌ Chỉ Giảng viên hướng dẫn mới có quyền từ chối.'); redirect('list.php'); }
            $note = sanitize($_GET['note'] ?? 'Cần chỉnh sửa thêm.');
            $this->reportModel->reject((int)$_GET['reject'], $note);
            setFlash('info', 'Đã yêu cầu sinh viên chỉnh sửa.');
            redirect('list.php');
        }

        $reports = $this->reportModel->getList($role, $uid);

        $this->render(BASE_PATH_FS . 'app/Views/reports/list.php', [
            'reports' => $reports,
            'role'    => $role,
        ]);
    }

    // ── Student: nộp/cập nhật báo cáo ────────────────────────────────
    public function submit(): void
    {
        requireRole('student');

        $uid = $_SESSION['user_id'];
        $sq  = $this->conn->prepare("SELECT student_id FROM student_profiles WHERE user_id=?");
        $sq->bind_param('i', $uid); $sq->execute();
        $sid = $sq->get_result()->fetch_assoc()['student_id'] ?? 0;

        $rq = $this->conn->prepare(
            "SELECT ir.*,i.title,cp.company_name,lp.full_name AS lecturer_name
             FROM internship_registrations ir
             JOIN internships i ON ir.internship_id=i.internship_id
             JOIN company_profiles cp ON ir.company_id=cp.company_id
             LEFT JOIN lecturer_profiles lp ON ir.lecturer_id=lp.lecturer_id
             WHERE ir.student_id=? ORDER BY ir.created_at DESC LIMIT 1"
        );
        $rq->bind_param('i', $sid); $rq->execute();
        $reg = $rq->get_result()->fetch_assoc();

        if (!$reg) { setFlash('error', 'Bạn chưa có kỳ thực tập nào.'); redirect(getDashboardUrl()); }

        $existing = $this->reportModel->getByRegistration($reg['registration_id']);
        $errors   = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $file_path = $existing['report_file'] ?? null;
            if (!empty($_FILES['report_file']['tmp_name'])) {
                $up = uploadFile($_FILES['report_file'], 'reports', ['pdf', 'doc', 'docx'], 20);
                if ($up['ok']) $file_path = $up['path'];
                else $errors[] = 'File: ' . $up['err'];
            }
            if (!$file_path) $errors[] = 'Vui lòng upload file báo cáo (PDF/DOC).';

            if (empty($errors)) {
                if ($existing)
                    $this->reportModel->update($existing['report_id'], $file_path);
                else
                    $this->reportModel->create($reg['registration_id'], $file_path);

                setFlash('success', '✅ Đã nộp báo cáo! Giảng viên sẽ xem xét và phản hồi.');
                redirect(getDashboardUrl());
            }
        }

        $this->render(BASE_PATH_FS . 'app/Views/reports/submit.php', [
            'reg'      => $reg,
            'existing' => $existing,
            'errors'   => $errors,
        ]);
    }
}
