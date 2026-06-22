<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../Models/UserModel.php';

class UserController extends BaseController
{
    private UserModel $userModel;

    public function __construct($conn)
    {
        parent::__construct($conn);
        $this->userModel = new UserModel($conn);
    }

    // ── Admin: danh sách người dùng ───────────────────────────────────
    public function list(): void
    {
        requireRole('admin');

        if (isset($_GET['delete'])) {
            $del_id = (int)$_GET['delete'];
            $cur_id = (int)$_SESSION['user_id'];
            if ($del_id === $cur_id) {
                setFlash('error', '❌ Không thể xóa tài khoản đang đăng nhập.');
            } else {
                $admin_cnt = (int)$this->conn->query("SELECT COUNT(*) c FROM users WHERE role='admin'")->fetch_assoc()['c'];
                $del_role  = $this->conn->query("SELECT role FROM users WHERE user_id=$del_id")->fetch_assoc()['role'] ?? '';
                if ($del_role === 'admin' && $admin_cnt <= 1) {
                    setFlash('error', '❌ Không thể xóa admin duy nhất.');
                } else {
                    $d = $this->conn->prepare("DELETE FROM users WHERE user_id=?");
                    if ($d) { $d->bind_param('i', $del_id); $d->execute(); setFlash('success', '✅ Đã xóa người dùng.'); }
                }
            }
            redirect('list.php?role=' . urlencode($_GET['role'] ?? '') . '&q=' . urlencode($_GET['q'] ?? ''));
        }

        $role_f = sanitize($_GET['role'] ?? '');
        $search = sanitize($_GET['q'] ?? '');
        $users  = $this->userModel->getAll($role_f, $search);
        $counts = [];
        foreach (['student', 'company', 'lecturer', 'admin'] as $r)
            $counts[$r] = (int)$this->conn->query("SELECT COUNT(*) c FROM users WHERE role='$r'")->fetch_assoc()['c'];

        $this->render(BASE_PATH_FS . 'app/Views/users/list.php', [
            'users'  => $users,
            'counts' => $counts,
            'role_f' => $role_f,
            'search' => $search,
        ]);
    }

    // ── Admin: tạo tài khoản giảng viên ──────────────────────────────
    public function createLecturer(): void
    {
        requireRole('admin');

        $errors = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email    = sanitize($_POST['email'] ?? '');
            $pw       = sanitize($_POST['password'] ?? '');
            $fullname = sanitize($_POST['full_name'] ?? '');
            $dept     = sanitize($_POST['department'] ?? '');
            $phone    = sanitize($_POST['phone'] ?? '');

            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email không hợp lệ.';
            if (strlen($pw) < 6)  $errors[] = 'Mật khẩu tối thiểu 6 ký tự.';
            if (empty($fullname)) $errors[] = 'Họ tên bắt buộc.';

            if (empty($errors) && $this->userModel->emailExists($email)) $errors[] = 'Email đã tồn tại.';

            if (empty($errors)) {
                $ins = $this->conn->prepare("INSERT INTO users (email,password,role,is_profile_completed) VALUES (?,?,'lecturer',1)");
                $hash = md5($pw);
                $ins->bind_param('ss', $email, $hash);
                if ($ins->execute()) {
                    $uid  = $this->conn->insert_id;
                    $lins = $this->conn->prepare("INSERT INTO lecturer_profiles (user_id,full_name,department,phone,email) VALUES (?,?,?,?,?)");
                    $lins->bind_param('issss', $uid, $fullname, $dept, $phone, $email);
                    $lins->execute();
                    setFlash('success', "✅ Đã tạo tài khoản giảng viên cho $fullname.");
                    redirect('list.php?role=lecturer');
                } else { $errors[] = 'Lỗi: ' . $this->conn->error; }
            }
        }

        $this->render(BASE_PATH_FS . 'app/Views/users/create_lecturer.php', ['errors' => $errors]);
    }
}
