<?php
/**
 * Seed demo accounts for testing 3 roles.
 * Run once: http://localhost/.../internship_system/auth/seed_demo.php
 */
require_once '../config/database.php';

$accounts = [
    ['Admin Nhà Trường', 'admin@ischool.edu.vn',   md5('123456'), 'admin',    null, null],
    ['Giảng Viên Hướng Dẫn', 'lecturer@ischool.edu.vn', md5('123456'), 'lecturer', null, null],
    ['Nguyễn Văn Sinh Viên', 'student@ischool.edu.vn',  md5('123456'), 'student',  'SV001', 'Công nghệ thông tin'],
    ['Công Ty ABC Corp',     'company@corp.vn',          md5('123456'), 'company',  null, null],
];

$inserted = 0;
foreach ($accounts as [$name, $email, $pw, $role, $code, $major]) {
    $chk = $conn->prepare("SELECT user_id FROM users WHERE email=?");
    $chk->bind_param('s', $email);
    $chk->execute();
    if ($chk->get_result()->num_rows > 0) continue; // already exists

    $stmt = $conn->prepare(
        "INSERT INTO users (full_name, email, password, role, student_code, major, status)
         VALUES (?, ?, ?, ?, ?, ?, 'active')"
    );
    $stmt->bind_param('ssssss', $name, $email, $pw, $role, $code, $major);
    $stmt->execute();
    $inserted++;
}

// Seed a demo company
$chkC = $conn->query("SELECT company_id FROM companies WHERE contact_email='company@corp.vn'")->fetch_assoc();
if (!$chkC) {
    $conn->query("INSERT INTO companies (name, location, description, contact_email, phone, status)
                  VALUES ('ABC Corp', 'Hà Nội', 'Công ty công nghệ hàng đầu', 'company@corp.vn', '0901234567', 'active')");
}

echo "<h3 style='font-family:sans-serif;color:#68756D'>✅ Seed hoàn tất — $inserted tài khoản mới được tạo</h3>";
echo "<p style='font-family:sans-serif'>Demo accounts:</p>";
echo "<ul style='font-family:monospace'>";
echo "<li>admin@ischool.edu.vn / 123456 (Admin)</li>";
echo "<li>lecturer@ischool.edu.vn / 123456 (Giảng viên)</li>";
echo "<li>student@ischool.edu.vn / 123456 (Sinh viên)</li>";
echo "<li>company@corp.vn / 123456 (Doanh nghiệp)</li>";
echo "</ul>";
echo "<a href='login.php' style='font-family:sans-serif;color:#68756D'>→ Đến trang đăng nhập</a>";
