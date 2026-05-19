<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Already logged in → redirect to dashboard
if (isLoggedIn()) {
    redirect(getBaseUrl() . '/index.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email))    $errors[] = 'Vui lòng nhập email.';
    if (empty($password)) $errors[] = 'Vui lòng nhập mật khẩu.';

    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND status = 'active' LIMIT 1");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();

        if ($user && md5($password) === $user['password']) {
            $_SESSION['user_id']   = $user['user_id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role']      = $user['role'];
            $_SESSION['email']     = $user['email'];

            // Redirect based on role
            switch ($user['role']) {
                default:
                    redirect(getBaseUrl() . '/index.php');
            }
        } else {
            $errors[] = 'Email hoặc mật khẩu không đúng, hoặc tài khoản đã bị vô hiệu hóa.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập · ISchool Internship</title>
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
            padding: 24px;
        }

        .auth-wrapper {
            display: flex;
            width: 100%;
            max-width: 960px;
            min-height: 560px;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(104,117,109,.18);
        }

        /* Left panel */
        .auth-left {
            flex: 1;
            background: linear-gradient(145deg, var(--evergreen) 0%, #4a5c52 100%);
            padding: 52px 44px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            color: #fff;
            position: relative;
            overflow: hidden;
        }
        .auth-left::before {
            content: '';
            position: absolute;
            width: 320px; height: 320px;
            border-radius: 50%;
            background: rgba(255,255,255,.06);
            top: -80px; right: -80px;
        }
        .auth-left::after {
            content: '';
            position: absolute;
            width: 200px; height: 200px;
            border-radius: 50%;
            background: rgba(255,255,255,.04);
            bottom: -40px; left: -40px;
        }
        .auth-brand {
            display: flex;
            align-items: center;
            gap: 14px;
            position: relative;
            z-index: 1;
        }
        .auth-brand-icon {
            width: 52px; height: 52px;
            background: rgba(255,255,255,.15);
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.5rem;
            backdrop-filter: blur(4px);
        }
        .auth-brand-title {
            font-size: 1.15rem;
            font-weight: 800;
            letter-spacing: .3px;
        }
        .auth-brand-sub {
            font-size: .72rem;
            opacity: .7;
            font-weight: 400;
        }
        .auth-hero {
            position: relative;
            z-index: 1;
        }
        .auth-hero h2 {
            font-size: 1.9rem;
            font-weight: 800;
            line-height: 1.25;
            margin-bottom: 14px;
        }
        .auth-hero p {
            font-size: .88rem;
            opacity: .78;
            line-height: 1.65;
        }
        .auth-features {
            list-style: none;
            padding: 0; margin: 0;
            position: relative;
            z-index: 1;
        }
        .auth-features li {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: .82rem;
            opacity: .82;
            margin-bottom: 10px;
        }
        .auth-features li i {
            width: 22px; height: 22px;
            background: rgba(255,255,255,.15);
            border-radius: 6px;
            display: flex; align-items: center; justify-content: center;
            font-size: .75rem;
            flex-shrink: 0;
        }

        /* Right panel */
        .auth-right {
            width: 420px;
            background: #fff;
            padding: 52px 44px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .auth-right h3 {
            font-size: 1.5rem;
            font-weight: 800;
            color: #1a1a1a;
            margin-bottom: 6px;
        }
        .auth-right .auth-sub {
            font-size: .84rem;
            color: var(--sage);
            margin-bottom: 32px;
        }

        .form-label {
            font-weight: 600;
            font-size: .82rem;
            color: #374151;
            margin-bottom: 6px;
        }
        .form-control {
            border-radius: 10px;
            border: 1.5px solid var(--blush);
            font-size: .875rem;
            padding: 10px 14px;
            transition: border-color .2s, box-shadow .2s;
            background: var(--ivory);
        }
        .form-control:focus {
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
            transition: background .2s;
        }
        .input-group .btn-toggle-pw:hover { background: var(--blush); }

        .btn-login {
            background: linear-gradient(135deg, var(--evergreen), #4a5c52);
            color: #fff;
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-weight: 700;
            font-size: .9rem;
            width: 100%;
            transition: opacity .2s, transform .15s;
            letter-spacing: .3px;
        }
        .btn-login:hover { opacity: .9; transform: translateY(-1px); color: #fff; }

        .divider {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 22px 0;
            color: var(--sage);
            font-size: .78rem;
        }
        .divider::before, .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--blush);
        }

        .role-pills {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin-bottom: 24px;
        }
        .role-pill {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: .75rem;
            font-weight: 600;
            border: 1.5px solid var(--blush);
            color: var(--sage);
            cursor: pointer;
            transition: all .18s;
            background: transparent;
        }
        .role-pill:hover, .role-pill.active {
            background: var(--evergreen);
            border-color: var(--evergreen);
            color: #fff;
        }

        .alert-danger {
            background: #fef2f2;
            border: none;
            border-left: 4px solid var(--terracotta);
            border-radius: 10px;
            color: #7f1d1d;
            font-size: .84rem;
            padding: 12px 16px;
        }

        .auth-footer-link {
            text-align: center;
            margin-top: 24px;
            font-size: .82rem;
            color: var(--sage);
        }
        .auth-footer-link a {
            color: var(--evergreen);
            font-weight: 600;
            text-decoration: none;
        }
        .auth-footer-link a:hover { text-decoration: underline; }

        @media (max-width: 768px) {
            .auth-left { display: none; }
            .auth-right { width: 100%; padding: 36px 28px; }
            .auth-wrapper { max-width: 440px; }
        }
    </style>
</head>
<body>

<div class="auth-wrapper">
    <!-- Left -->
    <div class="auth-left">
        <div class="auth-brand">
            <div class="auth-brand-icon"><i class="bi bi-mortarboard-fill"></i></div>
            <div>
                <div class="auth-brand-title">ISchool Internship</div>
                <div class="auth-brand-sub">Management System</div>
            </div>
        </div>

        <div class="auth-hero">
            <h2>Quản lý thực tập<br>toàn diện & hiệu quả</h2>
            <p>Nền tảng kết nối sinh viên, giảng viên và doanh nghiệp trong quá trình thực tập.</p>
        </div>

        <ul class="auth-features">
            <li><i class="bi bi-person-check-fill"></i> Theo dõi tiến độ sinh viên theo tuần</li>
            <li><i class="bi bi-building-fill"></i> Kết nối với hàng chục doanh nghiệp đối tác</li>
            <li><i class="bi bi-star-fill"></i> Đánh giá đa chiều từ DN & Giảng viên</li>
            <li><i class="bi bi-award-fill"></i> Tổng hợp điểm tự động cuối kỳ</li>
        </ul>
    </div>

    <!-- Right -->
    <div class="auth-right">
        <h3>Chào mừng trở lại</h3>
        <p class="auth-sub">Đăng nhập để tiếp tục vào hệ thống</p>

        <?php if (!empty($errors)): ?>
        <div class="alert alert-danger mb-3">
            <i class="bi bi-exclamation-circle me-2"></i>
            <?= implode('<br>', $errors) ?>
        </div>
        <?php endif; ?>

        <form method="POST" autocomplete="off">
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control"
                       placeholder="example@email.com"
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                       required autofocus>
            </div>
            <div class="mb-4">
                <label class="form-label">Mật khẩu</label>
                <div class="input-group">
                    <input type="password" name="password" id="pw" class="form-control"
                           placeholder="••••••••" required>
                    <button type="button" class="btn-toggle-pw" onclick="togglePw()">
                        <i class="bi bi-eye" id="pw-icon"></i>
                    </button>
                </div>
            </div>
            <button type="submit" class="btn-login">
                <i class="bi bi-box-arrow-in-right me-2"></i>Đăng nhập
            </button>
        </form>

        <div class="divider">hoặc</div>

        <div class="auth-footer-link">
            Chưa có tài khoản?
            <a href="register.php">Đăng ký ngay</a>
        </div>

        <div style="margin-top:28px;padding:14px;background:var(--ivory);border-radius:10px;border:1px solid var(--blush);">
            <div style="font-size:.72rem;font-weight:700;color:var(--sage);text-transform:uppercase;letter-spacing:.8px;margin-bottom:8px;">
                Tài khoản demo
            </div>
            <div style="font-size:.78rem;color:#555;line-height:1.8;">
                <strong>Admin:</strong> admin@ischool.edu.vn / 123456<br>
                <strong>Sinh viên:</strong> student@ischool.edu.vn / 123456<br>
                <strong>Doanh nghiệp:</strong> company@corp.vn / 123456
            </div>
        </div>
    </div>
</div>

<script>
function togglePw() {
    const pw = document.getElementById('pw');
    const ic = document.getElementById('pw-icon');
    if (pw.type === 'password') {
        pw.type = 'text';
        ic.className = 'bi bi-eye-slash';
    } else {
        pw.type = 'password';
        ic.className = 'bi bi-eye';
    }
}
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
