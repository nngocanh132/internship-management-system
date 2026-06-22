<?php
$conn = new mysqli('localhost','root','','internship_system');
$conn->set_charset('utf8mb4');
if($conn->connect_error) die('Connect error: '.$conn->connect_error);

$sqls = [
"ALTER TABLE users ADD COLUMN IF NOT EXISTS avatar VARCHAR(255) DEFAULT NULL",
"ALTER TABLE users ADD COLUMN IF NOT EXISTS cv_file VARCHAR(255) DEFAULT NULL",
"ALTER TABLE users ADD COLUMN IF NOT EXISTS gpa DECIMAL(3,2) DEFAULT NULL",
"ALTER TABLE users ADD COLUMN IF NOT EXISTS dob DATE DEFAULT NULL",
"ALTER TABLE users ADD COLUMN IF NOT EXISTS address TEXT DEFAULT NULL",
"ALTER TABLE users ADD COLUMN IF NOT EXISTS linkedin_url VARCHAR(255) DEFAULT NULL",
"ALTER TABLE users ADD COLUMN IF NOT EXISTS about_me TEXT DEFAULT NULL",
"ALTER TABLE users ADD COLUMN IF NOT EXISTS profile_completed TINYINT(1) DEFAULT 0",
"ALTER TABLE companies ADD COLUMN IF NOT EXISTS website VARCHAR(255) DEFAULT NULL",
"ALTER TABLE companies ADD COLUMN IF NOT EXISTS address TEXT DEFAULT NULL",
"ALTER TABLE companies ADD COLUMN IF NOT EXISTS industry VARCHAR(100) DEFAULT NULL",
"ALTER TABLE companies ADD COLUMN IF NOT EXISTS company_size VARCHAR(50) DEFAULT NULL",
"ALTER TABLE companies ADD COLUMN IF NOT EXISTS founded_year INT DEFAULT NULL",
"ALTER TABLE companies ADD COLUMN IF NOT EXISTS logo VARCHAR(255) DEFAULT NULL",
"ALTER TABLE companies ADD COLUMN IF NOT EXISTS business_license VARCHAR(255) DEFAULT NULL",
"ALTER TABLE companies ADD COLUMN IF NOT EXISTS tax_code VARCHAR(50) DEFAULT NULL",
"ALTER TABLE companies ADD COLUMN IF NOT EXISTS branches TEXT DEFAULT NULL",
"ALTER TABLE companies ADD COLUMN IF NOT EXISTS profile_completed TINYINT(1) DEFAULT 0",
"ALTER TABLE companies ADD COLUMN IF NOT EXISTS user_id INT DEFAULT NULL",
"ALTER TABLE internship_positions ADD COLUMN IF NOT EXISTS salary_range VARCHAR(100) DEFAULT NULL",
"ALTER TABLE internship_positions ADD COLUMN IF NOT EXISTS work_type ENUM('office','remote','hybrid') DEFAULT 'office'",
"ALTER TABLE internship_positions ADD COLUMN IF NOT EXISTS benefits TEXT DEFAULT NULL",
"ALTER TABLE internship_positions ADD COLUMN IF NOT EXISTS deadline DATE DEFAULT NULL",
"ALTER TABLE internship_registrations ADD COLUMN IF NOT EXISTS school_status ENUM('pending','approved','rejected') DEFAULT 'pending'",
"ALTER TABLE internship_registrations ADD COLUMN IF NOT EXISTS company_status ENUM('pending','approved','rejected') DEFAULT 'pending'",
"ALTER TABLE internship_registrations ADD COLUMN IF NOT EXISTS cv_submitted VARCHAR(255) DEFAULT NULL",
"ALTER TABLE internship_registrations ADD COLUMN IF NOT EXISTS cover_letter TEXT DEFAULT NULL",
"ALTER TABLE internship_registrations ADD COLUMN IF NOT EXISTS interview_date DATETIME DEFAULT NULL",
"ALTER TABLE internship_registrations ADD COLUMN IF NOT EXISTS interview_note TEXT DEFAULT NULL",
"ALTER TABLE internship_registrations ADD COLUMN IF NOT EXISTS intern_start DATE DEFAULT NULL",
"ALTER TABLE internship_registrations ADD COLUMN IF NOT EXISTS intern_end DATE DEFAULT NULL",
"ALTER TABLE internship_registrations ADD COLUMN IF NOT EXISTS school_note TEXT DEFAULT NULL",
"ALTER TABLE internship_registrations ADD COLUMN IF NOT EXISTS company_note TEXT DEFAULT NULL",
"CREATE TABLE IF NOT EXISTS messages (
    message_id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    registration_id INT DEFAULT NULL,
    subject VARCHAR(255) DEFAULT NULL,
    content TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    sent_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_msg_sender   FOREIGN KEY (sender_id)   REFERENCES users(user_id),
    CONSTRAINT fk_msg_receiver FOREIGN KEY (receiver_id) REFERENCES users(user_id)
)",
"CREATE TABLE IF NOT EXISTS internship_reports (
    report_id INT AUTO_INCREMENT PRIMARY KEY,
    assignment_id INT NOT NULL UNIQUE,
    student_id INT NOT NULL,
    report_file VARCHAR(255) DEFAULT NULL,
    report_content TEXT DEFAULT NULL,
    submitted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    status ENUM('submitted','reviewed','approved') DEFAULT 'submitted',
    admin_comment TEXT DEFAULT NULL,
    reviewed_at DATETIME DEFAULT NULL,
    CONSTRAINT fk_report_assignment FOREIGN KEY (assignment_id) REFERENCES internship_assignments(assignment_id),
    CONSTRAINT fk_report_student FOREIGN KEY (student_id) REFERENCES users(user_id)
)",
"UPDATE companies c JOIN users u ON c.contact_email=u.email AND u.role='company' SET c.user_id=u.user_id WHERE c.user_id IS NULL",
"UPDATE companies SET profile_completed=1 WHERE name IN ('FPT Software','Viettel IT','VNPT Technology','MoMo')",
"UPDATE internship_registrations SET school_status='approved',status='pending' WHERE status='pending' AND school_status='pending'",
];

echo '<!DOCTYPE html><html><head><meta charset="UTF-8">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>body{font-family:Inter,sans-serif;background:#f0f7f4;padding:32px}
.ok{color:#2d6a40}.er{color:#9a3030}
.card{border-radius:16px;box-shadow:0 4px 20px rgba(93,123,111,.12);max-width:800px}</style>
</head><body><div class="card p-4">';
echo '<h3 style="color:#5D7B6F;font-weight:800">🔧 Migration V2 — Nâng cấp Database</h3><hr>';

$ok=0; $fail=0;
foreach($sqls as $sql){
    $label = preg_replace('/\s+/',' ',substr(trim($sql),0,90)).'...';
    if($conn->query($sql)){
        echo "<div class='ok small'>✅ ".htmlspecialchars($label)."</div>";
        $ok++;
    } else {
        $err = $conn->error;
        // Bỏ qua lỗi "Duplicate column" — cột đã tồn tại
        if(strpos($err,'Duplicate column')!==false || strpos($err,'already exists')!==false){
            echo "<div class='text-muted small'>⏭ ".htmlspecialchars($label)." (đã tồn tại)</div>";
            $ok++;
        } else {
            echo "<div class='er small'>❌ ".htmlspecialchars($label)." → ".htmlspecialchars($err)."</div>";
            $fail++;
        }
    }
}

echo "<hr><div style='font-weight:700;color:#5D7B6F;font-size:1.1rem'>Kết quả: ✅ $ok thành công | ❌ $fail lỗi thực sự</div>";
echo "<div class='mt-3 d-flex gap-2'>
  <a href='/internship-management-system/internship_system/auth/login.php' class='btn btn-success'>🚀 Vào hệ thống</a>
  <a href='/internship-management-system/internship_system/dashboard/admin.php' class='btn btn-primary'>📊 Admin Dashboard</a>
</div></div></body></html>";
$conn->close();
?>
