<?php
define('DB_HOST','localhost');
define('DB_USER','root');
define('DB_PASS','');
define('DB_NAME','internship_system');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if($conn->connect_error){
    http_response_code(500);
    die('<div style="font-family:sans-serif;padding:40px;color:red">
        <h3>Lỗi kết nối database</h3><p>'.$conn->connect_error.'</p>
        <p>Vui lòng chạy <a href="/internship-management-system/setup.php">setup.php</a> trước.</p>
    </div>');
}
$conn->set_charset('utf8mb4');
