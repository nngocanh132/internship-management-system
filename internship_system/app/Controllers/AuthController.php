<?php
require_once __DIR__ . '/../Controllers/BaseController.php';
require_once __DIR__ . '/../Models/UserModel.php';

class AuthController extends BaseController
{
    private UserModel $userModel;

    public function __construct($conn)
    {
        parent::__construct($conn);
        $this->userModel = new UserModel($conn);
    }

    // ── Đăng nhập ─────────────────────────────────────────────────────
    public function login(): void
    {
        if (isLoggedIn()) redirect(getDashboardUrl());

        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $pw    = $_POST['password'] ?? '';

            if (empty($email) || empty($pw)) {
                $error = 'Vui lòng nhập đầy đủ thông tin.';
            } else {
                $u  = $this->userModel->getByEmail($email);
                $ok = false;

                if ($u) {
                    if (strlen($u['password']) === 32) {
                        $ok = ($u['password'] === md5($pw));
                    } else {
                        $ok = password_verify($pw, $u['password']);
                        if ($ok) {
                            $h = md5($pw);
                            $this->userModel->updatePassword($u['user_id'], $h);
                        }
                    }
                }

                if ($ok) {
                    $_SESSION['user_id']   = $u['user_id'];
                    $_SESSION['role']      = $u['role'];
                    $_SESSION['email']     = $u['email'];
                    $_SESSION['full_name'] = $this->userModel->getDisplayName($u['user_id'], $u['role'], $u['email']);
                    redirect(getDashboardUrl());
                } else {
                    $error = 'Email hoặc mật khẩu không đúng.';
                }
            }
        }

        $this->render(BASE_PATH_FS . 'app/Views/auth/login.php', [
            'error' => $error,
        ]);
    }

    // ── Đăng ký ───────────────────────────────────────────────────────
    public function register(): void
    {
        if (isLoggedIn()) redirect(getDashboardUrl());

        $errors  = [];
        $success = false;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email    = sanitize($_POST['email'] ?? '');
            $pw       = $_POST['password'] ?? '';
            $pw2      = $_POST['confirm_password'] ?? '';
            $role     = sanitize($_POST['role'] ?? '');
            $fullname = sanitize($_POST['full_name'] ?? '');

            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email không hợp lệ.';
            if (strlen($pw) < 6)      $errors[] = 'Mật khẩu tối thiểu 6 ký tự.';
            if ($pw !== $pw2)         $errors[] = 'Xác nhận mật khẩu không khớp.';
            if (!in_array($role, ['student', 'company'])) $errors[] = 'Vui lòng chọn loại tài khoản.';
            if (empty($fullname))     $errors[] = 'Tên không được để trống.';

            if (empty($errors) && $this->userModel->emailExists($email))
                $errors[] = 'Email đã tồn tại.';

            if (empty($errors)) {
                $hash = md5($pw);
                $uid  = $this->userModel->create($email, $hash, $role);
                if ($uid) {
                    if ($role === 'student')       $this->userModel->createStudentProfile($uid, $fullname);
                    elseif ($role === 'company')   $this->userModel->createCompanyProfile($uid, $fullname);
                    $success = true;
                } else {
                    $errors[] = 'Lỗi hệ thống: ' . $this->userModel->lastError();
                }
            }
        }

        $this->render(BASE_PATH_FS . 'app/Views/auth/register.php', [
            'errors'  => $errors,
            'success' => $success,
        ]);
    }

    // ── Đăng xuất ─────────────────────────────────────────────────────
    public function logout(): void
    {
        session_unset();
        session_destroy();
        redirect(BASE_PATH . '/auth/login.php');
    }
}
