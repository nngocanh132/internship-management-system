<?php
require_once __DIR__ . '/../Controllers/BaseController.php';
require_once __DIR__ . '/../Models/InternshipModel.php';
require_once __DIR__ . '/../Models/RegistrationModel.php';

class InternshipController extends BaseController
{
    private InternshipModel   $jobModel;
    private RegistrationModel $regModel;

    public function __construct($conn)
    {
        parent::__construct($conn);
        $this->jobModel = new InternshipModel($conn);
        $this->regModel = new RegistrationModel($conn);
    }

    // ── Admin: danh sách tất cả vị trí ──────────────────────────────
    public function list(): void
    {
        requireRole('admin');

        $search   = sanitize($_GET['q'] ?? '');
        $status_f = sanitize($_GET['status'] ?? '');
        $jobs     = $this->jobModel->getAllWithFilter($search, $status_f);

        $this->render(BASE_PATH_FS . 'app/Views/internships/list.php', [
            'jobs'     => $jobs,
            'search'   => $search,
            'status_f' => $status_f,
        ]);
    }

    // ── Student: tìm việc thực tập ───────────────────────────────────
    public function browse(): void
    {
        requireRole('student');

        $uid = $_SESSION['user_id'];
        $sp  = $this->conn->prepare("SELECT student_id FROM student_profiles WHERE user_id=?");
        $sp->bind_param('i', $uid); $sp->execute();
        $sid = $sp->get_result()->fetch_assoc()['student_id'] ?? 0;

        $has_reg       = false;
        $reg_completed = false;
        if ($sid) {
            $existing = $this->regModel->getExistingByStudent($sid);
            if ($existing) {
                $has_reg       = true;
                $reg_completed = ($existing['status'] === 'completed');
            }
        }

        $search = sanitize($_GET['q'] ?? '');
        $jobs   = $this->jobModel->getOpenJobs($sid, $search);

        $this->render(BASE_PATH_FS . 'app/Views/internships/browse.php', [
            'jobs'          => $jobs,
            'search'        => $search,
            'has_reg'       => $has_reg,
            'reg_completed' => $reg_completed,
        ]);
    }

    // ── Company: vị trí của tôi ──────────────────────────────────────
    public function myJobs(): void
    {
        requireRole('company');

        $uid = $_SESSION['user_id'];
        $cq  = $this->conn->prepare("SELECT company_id FROM company_profiles WHERE user_id=?");
        $cid = 0;
        if ($cq) { $cq->bind_param('i', $uid); $cq->execute(); $cid = $cq->get_result()->fetch_assoc()['company_id'] ?? 0; }

        if (isset($_GET['toggle']) && $cid) {
            $this->jobModel->toggleStatus((int)$_GET['toggle'], $cid);
            setFlash('success', 'Đã đổi trạng thái.');
            redirect('my_jobs.php');
        }
        if (isset($_GET['delete']) && $cid) {
            if ($this->jobModel->delete((int)$_GET['delete'], $cid))
                setFlash('success', 'Đã xóa.');
            else
                setFlash('error', 'Không thể xóa: đã có ứng viên.');
            redirect('my_jobs.php');
        }

        $errors    = [];
        $show_form = isset($_GET['new']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_job'])) {
            $title = sanitize($_POST['title'] ?? '');
            $desc  = sanitize($_POST['description'] ?? '');
            $req   = sanitize($_POST['requirements'] ?? '');
            $qty   = max(1, (int)($_POST['quantity'] ?? 1));
            $loc   = sanitize($_POST['location'] ?? '');
            $sd    = sanitize($_POST['start_date'] ?? '') ?: null;
            $ed    = sanitize($_POST['end_date'] ?? '') ?: null;

            if (empty($title)) $errors[] = 'Tiêu đề là bắt buộc.';
            if (!$cid)         $errors[] = 'Chưa có hồ sơ doanh nghiệp.';

            if (empty($errors)) {
                if ($this->jobModel->create($cid, $title, $desc, $req, $qty, $loc, $sd, $ed)) {
                    setFlash('success', '✅ Đã đăng vị trí thực tập!');
                    redirect('my_jobs.php');
                } else {
                    $errors[] = 'Lỗi hệ thống.';
                }
            }
            $show_form = true;
        }

        $jobs = $cid ? $this->jobModel->getByCompany($cid) : [];

        // my_jobs có form inline → render trực tiếp trong module file
        // Truyền dữ liệu ra ngoài để module file dùng
        $this->render(BASE_PATH_FS . 'app/Views/internships/my_jobs.php', [
            'jobs'      => $jobs,
            'errors'    => $errors,
            'show_form' => $show_form,
        ]);
    }

    // ── Company/Admin: sửa vị trí ────────────────────────────────────
    public function edit(): void
    {
        requireRole(['company', 'admin']);

        $uid  = $_SESSION['user_id'];
        $role = getRole();
        $id   = (int)($_GET['id'] ?? 0);
        if (!$id) redirect('my_jobs.php');

        $j = $this->jobModel->getById($id);
        if (!$j) { setFlash('error', 'Không tìm thấy.'); redirect('my_jobs.php'); }

        if ($role === 'company') {
            $cq = $this->conn->prepare("SELECT company_id FROM company_profiles WHERE user_id=?");
            $cq->bind_param('i', $uid); $cq->execute();
            $cid = $cq->get_result()->fetch_assoc()['company_id'] ?? 0;
            if ($j['company_id'] != $cid) { setFlash('error', 'Không có quyền.'); redirect('my_jobs.php'); }
        }

        $errors = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title  = sanitize($_POST['title'] ?? '');
            $desc   = sanitize($_POST['description'] ?? '');
            $req    = sanitize($_POST['requirements'] ?? '');
            $qty    = (int)($_POST['quantity'] ?? 1);
            $loc    = sanitize($_POST['location'] ?? '');
            $sd     = sanitize($_POST['start_date'] ?? '') ?: null;
            $ed     = sanitize($_POST['end_date'] ?? '') ?: null;
            $status = sanitize($_POST['status'] ?? 'open');

            if (empty($title)) $errors[] = 'Tiêu đề bắt buộc.';
            if (empty($errors)) {
                if ($this->jobModel->update($id, $title, $desc, $req, $qty, $loc, $sd, $ed, $status)) {
                    setFlash('success', 'Đã cập nhật.');
                    redirect($role === 'company' ? 'my_jobs.php' : 'list.php');
                } else {
                    $errors[] = 'Lỗi hệ thống.';
                }
            }
            $j = array_merge($j, $_POST);
        }

        $this->render(BASE_PATH_FS . 'app/Views/internships/edit.php', [
            'j'      => $j,
            'errors' => $errors,
            'role'   => $role,
        ]);
    }

    // ── Company: đăng vị trí mới ─────────────────────────────────────
    public function create(): void
    {
        requireRole('company');
        requireProfileComplete($this->conn);

        $uid = $_SESSION['user_id'];
        $cq  = $this->conn->prepare("SELECT company_id FROM company_profiles WHERE user_id=?");
        $cq->bind_param('i', $uid); $cq->execute();
        $cid = $cq->get_result()->fetch_assoc()['company_id'] ?? 0;
        if (!$cid) { setFlash('error', 'Không tìm thấy hồ sơ doanh nghiệp.'); redirect('../company_profiles/edit.php'); }

        $errors = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = sanitize($_POST['title'] ?? '');
            $desc  = sanitize($_POST['description'] ?? '');
            $req   = sanitize($_POST['requirements'] ?? '');
            $qty   = (int)($_POST['quantity'] ?? 1);
            $loc   = sanitize($_POST['location'] ?? '');
            $sd    = sanitize($_POST['start_date'] ?? '') ?: null;
            $ed    = sanitize($_POST['end_date'] ?? '') ?: null;

            if (empty($title)) $errors[] = 'Tiêu đề là bắt buộc.';
            if ($qty < 1)       $errors[] = 'Số lượng >= 1.';

            if (empty($errors)) {
                if ($this->jobModel->create($cid, $title, $desc, $req, $qty, $loc, $sd, $ed)) {
                    setFlash('success', '✅ Đã đăng vị trí thực tập!');
                    redirect('my_jobs.php');
                } else {
                    $errors[] = 'Lỗi hệ thống.';
                }
            }
        }

        $this->render(BASE_PATH_FS . 'app/Views/internships/create.php', [
            'errors' => $errors,
        ]);
    }
}
