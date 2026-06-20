<?php
$conn = new mysqli('localhost','root','','internship_system');
$conn->set_charset('utf8mb4');

$tests = [
    ['admin@ischool.edu.vn','admin123'],
    ['student1@ischool.edu.vn','pass123'],
    ['hr@fpt.com','pass123'],
    ['lecturer1@ischool.edu.vn','pass123'],
];

echo '<style>body{font-family:monospace;padding:20px;background:#eef5f2}
table{border-collapse:collapse;width:100%}
td,th{border:1px solid #ccc;padding:8px 12px}
.ok{color:green;font-weight:bold}.fail{color:red;font-weight:bold}</style>';
echo '<h3>Login Test</h3><table>';
echo '<tr><th>Email</th><th>Password</th><th>DB hash</th><th>MD5 match</th><th>Role</th><th>Profile</th></tr>';

foreach($tests as [$email,$pw]){
    $r = $conn->query("SELECT * FROM users WHERE email='$email' LIMIT 1");
    if(!$r||!($u=$r->fetch_assoc())){
        echo "<tr><td>$email</td><td>$pw</td><td colspan=4 class='fail'>USER NOT FOUND</td></tr>";
        continue;
    }
    $md5_match = (md5($pw)===$u['password']) ? "<span class='ok'>✅ YES</span>" : "<span class='fail'>❌ NO</span>";
    $hash_preview = substr($u['password'],0,8).'...';
    $profile_ok = $u['is_profile_completed'] ? "<span class='ok'>✅</span>" : "<span class='fail'>⚠️ 0</span>";
    echo "<tr><td>$email</td><td>$pw</td><td>$hash_preview</td><td>$md5_match</td><td>{$u['role']}</td><td>$profile_ok</td></tr>";
}
echo '</table>';
echo '<br><a href="/internship-management-system/internship_system/auth/login.php" style="background:#5D7B6F;color:#fff;padding:10px 20px;border-radius:8px;text-decoration:none">→ Thử đăng nhập</a>';
$conn->close();
?>
