<?php
/**
 * Seed / Reset demo accounts.
 * Run: http://localhost/.../internship_system/auth/seed_demo.php
 * Thêm ?reset=1 vào URL để reset mật khẩu các tài khoản đã tồn tại.
 */
require_once '../config/database.php';

$do_reset = isset($_GET['reset']);

$accounts = [
    ['Admin Nhà Trường',     'admin@ischool.edu.vn',    '123456', 'admin',    null,    null],
    ['Giảng Viên Hướng Dẫn', 'lecturer@ischool.edu.vn', '123456', 'lecturer', null,    null],
    ['Nguyễn Văn Sinh Viên', 'student@ischool.edu.vn',  '123456', 'student',  'SV001', 'Công nghệ thông tin'],
    ['Công Ty ABC Corp',     'company@corp.vn',          '123456', 'company',  null,    null],
];

$inserted = 0; $reset = 0;
$log = [];

foreach ($accounts as [$name, $email, $plain_pw, $role, $code, $major]) {
    $hashed = password_hash($plain_pw, PASSWORD_DEFAULT);

    $chk = $conn->prepare("SELECT user_id FROM users WHERE email=?");
    $chk->bind_param('s', $email);
    $chk->execute();
    $existing = $chk->get_result()->fetch_assoc();

    if ($existing) {
        if ($do_reset) {
            $upd = $conn->prepare("UPDATE users SET password=?, status='active' WHERE email=?");
            $upd->bind_param('ss', $hashed, $email);
            $upd->execute();
            $reset++;
            $log[] = "🔄 Đã reset mật khẩu: $email ($role)";
        } else {
            $log[] = "⏭ Đã tồn tại (dùng ?reset=1 để reset): $email";
        }
        continue;
    }

    $stmt = $conn->prepare(
        "INSERT INTO users (full_name, email, password, role, student_code, major, status)
         VALUES (?, ?, ?, ?, ?, ?, 'active')"
    );
    $stmt->bind_param('ssssss', $name, $email, $hashed, $role, $code, $major);

    if ($stmt->execute()) {
        $inserted++;
        $log[] = "✅ Đã tạo: $email ($role)";
    } else {
        $log[] = "❌ Lỗi tạo $email: " . $stmt->error;
    }
}

// Seed demo company
$chkC = $conn->query("SELECT company_id FROM companies WHERE contact_email='company@corp.vn'")->fetch_assoc();
if (!$chkC) {
    $res = $conn->query("INSERT INTO companies (name, location, description, contact_email, phone, status)
                  VALUES ('ABC Corp', 'Hà Nội', 'Công ty công nghệ hàng đầu', 'company@corp.vn', '0901234567', 'active')");
    $log[] = $res ? "✅ Đã tạo công ty demo: ABC Corp" : "❌ Lỗi tạo công ty: " . $conn->error;
} else {
    $log[] = "⏭ Công ty ABC Corp đã tồn tại";
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Seed Demo</title>
<style>
    body{font-family:'Segoe UI',sans-serif;max-width:640px;margin:40px auto;color:#2c3e35;background:#f0faf5;padding:0 16px}
    h2{color:#3f5c52;margin-bottom:4px}
    .sub{color:#6b8c7e;font-size:.88rem;margin-bottom:20px}
    .log{background:#fff;border:1px solid #c8e6c9;border-radius:10px;padding:16px;font-size:.88rem;line-height:2.1}
    .accounts{background:#fff;border:1px solid #b2dfdb;border-radius:10px;padding:16px;margin-top:16px}
    .accounts li{font-family:monospace;font-size:.86rem;line-height:2}
    .btn{display:inline-block;margin-top:16px;padding:10px 20px;border-radius:8px;text-decoration:none;font-weight:700;font-size:.88rem}
    .btn-reset{background:#e8f5e9;color:#2e7d32;border:1.5px solid #a5d6a7}
    .btn-login{background:#3f5c52;color:#fff;border:none;margin-left:8px}
    .warn{background:#fff8e1;border:1px solid #ffe082;border-radius:8px;padding:10px 14px;font-size:.83rem;color:#7c5c00;margin-top:12px}
</style>
</head>
<body>
<h2>🌱 Seed Demo</h2>
<div class="sub">Tạo mới: <strong><?= $inserted ?></strong> · Reset mật khẩu: <strong><?= $reset ?></strong></div>

<div class="log">
    <?php foreach ($log as $l) echo "<div>$l</div>"; ?>
</div>

<?php if (!$do_reset && $inserted === 0): ?>
<div class="warn">
    ⚠️ Tất cả tài khoản đã tồn tại. Nếu không đăng nhập được, hãy nhấn <strong>Reset mật khẩu</strong> bên dưới để cập nhật lại mật khẩu về <code>123456</code>.
</div>
<?php endif; ?>

<div class="accounts">
    <strong>Tài khoản demo (mật khẩu: <code>123456</code>):</strong>
    <ul>
        <li>admin@ischool.edu.vn — Admin</li>
        <li>lecturer@ischool.edu.vn — Giảng viên</li>
        <li>student@ischool.edu.vn — Sinh viên</li>
        <li>company@corp.vn — Doanh nghiệp</li>
    </ul>
</div>

<div>
    <a href="seed_demo.php?reset=1" class="btn btn-reset">🔄 Reset mật khẩu về 123456</a>
    <a href="login.php" class="btn btn-login">→ Đến trang đăng nhập</a>
</div>
</body>
</html>
