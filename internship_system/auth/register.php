<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

if (isLoggedIn()) redirect(getBaseUrl() . '/index.php');

$errors  = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name    = sanitize($_POST['full_name'] ?? '');
    $email        = sanitize($_POST['email'] ?? '');
    $password     = $_POST['password'] ?? '';
    $confirm_pw   = $_POST['confirm_password'] ?? '';
    $role         = sanitize($_POST['role'] ?? '');
    $phone        = sanitize($_POST['phone'] ?? '');
    $student_code = sanitize($_POST['student_code'] ?? '');
    $major        = sanitize($_POST['major'] ?? '');

    // Validation
    if (empty($full_name))  $errors[] = 'Họ tên không được để trống.';
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email không hợp lệ.';
    if (strlen($password) < 6) $errors[] = 'Mật khẩu phải có ít nhất 6 ký tự.';
    if ($password !== $confirm_pw) $errors[] = 'Mật khẩu xác nhận không khớp.';
    if (!in_array($role, ['student', 'company'])) $errors[] = 'Vui lòng chọn loại tài khoản hợp lệ.';

    // Duplicate email
    if (empty($errors)) {
        $chk = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
        $chk->bind_param('s', $email);
        $chk->execute();
        if ($chk->get_result()->num_rows > 0) $errors[] = 'Email đã được sử dụng.';
    }

    // Duplicate student_code
    if (empty($errors) && $role === 'student' && !empty($student_code)) {
        $chk2 = $conn->prepare("SELECT user_id FROM users WHERE student_code = ?");
        $chk2->bind_param('s', $student_code);
        $chk2->execute();
        if ($chk2->get_result()->num_rows > 0) $errors[] = 'Mã sinh viên đã tồn tại.';
    }

    if (empty($errors)) {
        $hashed = md5($password);
        $code   = ($role === 'student' && !empty($student_code)) ? $student_code : null;
        $maj    = !empty($major) ? $major : null;
        $ph     = !empty($phone) ? $phone : null;

        $stmt = $conn->prepare(
            "INSERT INTO users (full_name, email, password, role, phone, student_code, major, status)
             VALUES (?, ?, ?, ?, ?, ?, ?, 'active')"
        );
        $stmt->bind_param('sssssss', $full_name, $email, $hashed, $role, $ph, $code, $maj);

        if ($stmt->execute()) {
            $success = true;
        } else {
            $errors[] = 'Lỗi khi tạo tài khoản: ' . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký · ISchool Internship</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --ivory:      #F4F3F1;
            --blush:      #DECEBF;
            --sage:       #A1A79E;
            --terracotta: #B57B66;
            --evergreen:  #68756D;
        }
        *, *::before, *::after { box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: var(--ivory);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 32px 24px;
        }
        .auth-card {
            background: #fff;
            border-radius: 24px;
            box-shadow: 0 20px 60px rgba(104,117,109,.15);
            width: 100%;
            max-width: 560px;
            overflow: hidden;
        }
        .auth-card-header {
            background: linear-gradient(135deg, var(--evergreen), #4a5c52);
            padding: 36px 44px 28px;
            color: #fff;
        }
        .auth-card-header .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 20px;
        }
        .auth-card-header .brand-icon {
            width: 44px; height: 44px;
            background: rgba(255,255,255,.15);
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.3rem;
        }
        .auth-card-header h3 {
            font-size: 1.4rem;
            font-weight: 800;
            margin: 0;
        }
        .auth-card-header p {
            font-size: .84rem;
            opacity: .75;
            margin: 4px 0 0;
        }
        .auth-card-body { padding: 36px 44px 40px; }

        .form-label {
            font-weight: 600;
            font-size: .82rem;
            color: #374151;
            margin-bottom: 6px;
        }
        .form-control, .form-select {
            border-radius: 10px;
            border: 1.5px solid var(--blush);
            font-size: .875rem;
            padding: 10px 14px;
            background: var(--ivory);
            transition: border-color .2s, box-shadow .2s;
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--evergreen);
            box-shadow: 0 0 0 3px rgba(104,117,109,.12);
            background: #fff;
        }
        .input-group .form-control { border-radius: 10px 0 0 10px; }
        .input-group .btn-toggle-pw {
            border: 1.5px solid var(--blush);
            border-left: none;
            border-radius: 0 10px 10px 0;
            background: var(--ivory);
            color: var(--sage);
            padding: 0 14px;
            cursor: pointer;
        }
        .input-group .btn-toggle-pw:hover { background: var(--blush); }

        .role-selector {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 4px;
        }
        .role-option {
            position: relative;
        }
        .role-option input[type="radio"] {
            position: absolute;
            opacity: 0;
            width: 0; height: 0;
        }
        .role-option label {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
            padding: 16px 12px;
            border-radius: 12px;
            border: 2px solid var(--blush);
            cursor: pointer;
            transition: all .18s;
            background: var(--ivory);
            text-align: center;
        }
        .role-option label i {
            font-size: 1.5rem;
            color: var(--sage);
            transition: color .18s;
        }
        .role-option label span {
            font-size: .82rem;
            font-weight: 600;
            color: #374151;
        }
        .role-option label small {
            font-size: .72rem;
            color: var(--sage);
        }
        .role-option input:checked + label {
            border-color: var(--evergreen);
            background: rgba(104,117,109,.08);
        }
        .role-option input:checked + label i { color: var(--evergreen); }

        .btn-register {
            background: linear-gradient(135deg, var(--evergreen), #4a5c52);
            color: #fff;
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-weight: 700;
            font-size: .9rem;
            width: 100%;
            transition: opacity .2s, transform .15s;
        }
        .btn-register:hover { opacity: .9; transform: translateY(-1px); color: #fff; }

        .alert-danger {
            background: #fef2f2;
            border: none;
            border-left: 4px solid var(--terracotta);
            border-radius: 10px;
            color: #7f1d1d;
            font-size: .84rem;
            padding: 12px 16px;
        }
        .alert-success {
            background: #f0fdf4;
            border: none;
            border-left: 4px solid #22c55e;
            border-radius: 10px;
            color: #166534;
            font-size: .9rem;
            padding: 16px 20px;
        }
        .auth-footer-link {
            text-align: center;
            margin-top: 20px;
            font-size: .82rem;
            color: var(--sage);
        }
        .auth-footer-link a {
            color: var(--evergreen);
            font-weight: 600;
            text-decoration: none;
        }
        .auth-footer-link a:hover { text-decoration: underline; }

        /* Conditional fields */
        #student-fields, #company-fields { display: none; }
    </style>
</head>
<body>

<div class="auth-card">
    <div class="auth-card-header">
        <div class="brand">
            <div class="brand-icon"><i class="bi bi-mortarboard-fill"></i></div>
            <div>
                <div style="font-size:.72rem;opacity:.7;font-weight:400;">ISchool Internship</div>
                <div style="font-size:.95rem;font-weight:700;">Management System</div>
            </div>
        </div>
        <h3>Tạo tài khoản mới</h3>
        <p>Điền thông tin để đăng ký tham gia hệ thống</p>
    </div>

    <div class="auth-card-body">
        <?php if ($success): ?>
        <div class="alert-success mb-4">
            <i class="bi bi-check-circle-fill me-2"></i>
            <strong>Đăng ký thành công!</strong> Tài khoản của bạn đã được tạo.
            <div class="mt-2">
                <a href="login.php" style="color:var(--evergreen);font-weight:700;">
                    <i class="bi bi-box-arrow-in-right me-1"></i>Đăng nhập ngay →
                </a>
            </div>
        </div>
        <?php else: ?>

        <?php if (!empty($errors)): ?>
        <div class="alert-danger mb-3">
            <i class="bi bi-exclamation-circle me-2"></i>
            <ul class="mb-0 ps-3 mt-1">
                <?php foreach ($errors as $e): ?><li><?= $e ?></li><?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <form method="POST" id="reg-form">
            <!-- Role selector -->
            <div class="mb-4">
                <label class="form-label">Loại tài khoản <span class="text-danger">*</span></label>
                <div class="role-selector">
                    <div class="role-option">
                        <input type="radio" name="role" id="role-student" value="student"
                               <?= ($_POST['role']??'')==='student' ? 'checked' : '' ?>>
                        <label for="role-student">
                            <i class="bi bi-mortarboard-fill"></i>
                            <span>Sinh viên</span>
                            <small>Đăng ký thực tập</small>
                        </label>
                    </div>
                    <div class="role-option">
                        <input type="radio" name="role" id="role-company" value="company"
                               <?= ($_POST['role']??'')==='company' ? 'checked' : '' ?>>
                        <label for="role-company">
                            <i class="bi bi-building-fill"></i>
                            <span>Doanh nghiệp</span>
                            <small>Đăng vị trí thực tập</small>
                        </label>
                    </div>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label">Họ và tên <span class="text-danger">*</span></label>
                    <input type="text" name="full_name" class="form-control"
                           value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>"
                           placeholder="Nguyễn Văn A" required>
                </div>
                <div class="col-12">
                    <label class="form-label">Email <span class="text-danger">*</span></label>
                    <input type="email" name="email" class="form-control"
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                           placeholder="example@email.com" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Mật khẩu <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="password" name="password" id="pw1" class="form-control"
                               placeholder="Tối thiểu 6 ký tự" minlength="6" required>
                        <button type="button" class="btn-toggle-pw" onclick="togglePw('pw1','ic1')">
                            <i class="bi bi-eye" id="ic1"></i>
                        </button>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Xác nhận mật khẩu <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="password" name="confirm_password" id="pw2" class="form-control"
                               placeholder="Nhập lại mật khẩu" required>
                        <button type="button" class="btn-toggle-pw" onclick="togglePw('pw2','ic2')">
                            <i class="bi bi-eye" id="ic2"></i>
                        </button>
                    </div>
                </div>
                <div class="col-12">
                    <label class="form-label">Số điện thoại</label>
                    <input type="text" name="phone" class="form-control"
                           value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>"
                           placeholder="0901234567">
                </div>
            </div>

            <!-- Student-specific fields -->
            <div id="student-fields" class="mt-3">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Mã sinh viên</label>
                        <input type="text" name="student_code" class="form-control"
                               value="<?= htmlspecialchars($_POST['student_code'] ?? '') ?>"
                               placeholder="SV001">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Chuyên ngành</label>
                        <input type="text" name="major" class="form-control"
                               value="<?= htmlspecialchars($_POST['major'] ?? '') ?>"
                               placeholder="Công nghệ thông tin">
                    </div>
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn-register">
                    <i class="bi bi-person-plus-fill me-2"></i>Tạo tài khoản
                </button>
            </div>
        </form>

        <div class="auth-footer-link">
            Đã có tài khoản? <a href="login.php">Đăng nhập</a>
        </div>

        <?php endif; ?>
    </div>
</div>

<script>
function togglePw(id, iconId) {
    const pw = document.getElementById(id);
    const ic = document.getElementById(iconId);
    if (pw.type === 'password') {
        pw.type = 'text';
        ic.className = 'bi bi-eye-slash';
    } else {
        pw.type = 'password';
        ic.className = 'bi bi-eye';
    }
}

// Show/hide conditional fields based on role
function updateRoleFields() {
    const role = document.querySelector('input[name="role"]:checked')?.value;
    document.getElementById('student-fields').style.display = (role === 'student') ? 'block' : 'none';
}

document.querySelectorAll('input[name="role"]').forEach(r => {
    r.addEventListener('change', updateRoleFields);
});
updateRoleFields(); // run on load
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
