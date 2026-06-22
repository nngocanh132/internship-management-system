<?php
define('BASE_PATH', '/internship-management-system/internship_system');
define('UPLOAD_PATH', __DIR__ . '/../uploads');
define('UPLOAD_URL',  '/internship-management-system/internship_system/uploads');

// Hàm helper tạo URL ngắn (không qua /)
function modUrl($module, $file) {
    return BASE_PATH . '/' . $module . '/' . $file;
}

// BASE_PATH_FS: đường dẫn tuyệt đối tới thư mục internship_system/
// Được dùng trong Views để include header/footer
if (!defined('BASE_PATH_FS')) {
    define('BASE_PATH_FS', __DIR__ . '/../');
}

// ── Helpers ──────────────────────────────
function sanitize($v){ return htmlspecialchars(strip_tags(trim($v ?? ''))); }
function redirect($url){ header("Location: $url"); exit(); }

// ── Safe query helpers — không crash khi bảng chưa tồn tại ──
function safeQuery($conn, $sql){
    $r = $conn->query($sql);
    return ($r && $r !== true) ? $r->fetch_all(MYSQLI_ASSOC) : [];
}
function safeCount($conn, $sql){
    $r = $conn->query($sql);
    return ($r && $r !== true) ? (int)($r->fetch_assoc()['c'] ?? 0) : 0;
}
function safeRow($conn, $sql){
    $r = $conn->query($sql);
    return ($r && $r !== true) ? $r->fetch_assoc() : null;
}

function setFlash($type, $msg){
    $_SESSION['flash'] = ['type'=>$type,'msg'=>$msg];
}
function showFlash(){
    if(!isset($_SESSION['flash'])) return;
    $f = $_SESSION['flash']; unset($_SESSION['flash']);
    $map = ['success'=>['success','check-circle-fill'],
            'error'  =>['danger','exclamation-triangle-fill'],
            'warning'=>['warning','exclamation-triangle-fill'],
            'info'   =>['info','info-circle-fill']];
    [$cls,$icon] = $map[$f['type']] ?? ['secondary','info-circle-fill'];
    echo "<div class='alert alert-{$cls} alert-dismissible fade show'>
        <i class='bi bi-{$icon} me-2'></i>{$f['msg']}
        <button type='button' class='btn-close' data-bs-dismiss='alert'></button></div>";
}

// ── Auth ─────────────────────────────────
function isLoggedIn(){ return isset($_SESSION['user_id']); }
function getRole()   { return $_SESSION['role'] ?? null; }
function isAdmin()   { return getRole()==='admin'; }
function isStudent() { return getRole()==='student'; }
function isCompany() { return getRole()==='company'; }
function isLecturer(){ return getRole()==='lecturer'; }

function requireLogin(){
    if(!isLoggedIn()) redirect(BASE_PATH.'/auth/login.php');
}
function requireRole($roles){
    requireLogin();
    if(!in_array(getRole(),(array)$roles))
        redirect(BASE_PATH.'/auth/unauthorized.php');
}
function getDashboardUrl(){
    return match(getRole()){
        'admin'    => BASE_PATH.'/dashboard/admin.php',
        'student'  => BASE_PATH.'/dashboard/student.php',
        'company'  => BASE_PATH.'/dashboard/company.php',
        'lecturer' => BASE_PATH.'/dashboard/lecturer.php',
        default    => BASE_PATH.'/auth/login.php'
    };
}
function getBaseUrl(){ return BASE_PATH; }
function getRoleLabel($r){
    return match($r){'admin'=>'Quản trị viên','student'=>'Sinh viên',
        'company'=>'Doanh nghiệp','lecturer'=>'Giảng viên',default=>$r};
}

// ── Profile check — soft redirect, không block nếu DB chưa sẵn sàng ──
function requireProfileComplete($conn){
    $role = getRole();
    $uid  = $_SESSION['user_id'] ?? 0;
    $cur  = $_SERVER['REQUEST_URI'];

    // Admin và Lecturer không cần check
    if($role === 'admin' || $role === 'lecturer') return;

    // Trang miễn kiểm tra
    $exempt = ['/student_profiles/','/company_profiles/','/auth/','/setup','/login','/register','/logout'];
    foreach($exempt as $ex)
        if(strpos($cur,$ex) !== false) return;

    // Chỉ redirect nếu cố vào trang nhạy cảm (apply, create job)
    $strict = ['/applications/apply','/internships/create','/internships/my_jobs'];
    $is_strict = false;
    foreach($strict as $s) if(strpos($cur,$s) !== false){ $is_strict=true; break; }

    if(!$is_strict) return; // Dashboard + các trang khác: không chặn

    // Kiểm tra DB — nếu lỗi thì bỏ qua
    $r = $conn->prepare("SELECT is_profile_completed FROM users WHERE user_id=?");
    if(!$r) return;
    $r->bind_param('i',$uid); $r->execute();
    $row = $r->get_result()->fetch_assoc();
    $completed = $row['is_profile_completed'] ?? 0;

    if(!$completed){
        if($role === 'student'){
            setFlash('warning','⚠️ Vui lòng hoàn thiện hồ sơ sinh viên trước khi ứng tuyển!');
            redirect(BASE_PATH.'/student_profiles/edit.php');
        } elseif($role === 'company'){
            setFlash('warning','⚠️ Vui lòng hoàn thiện hồ sơ doanh nghiệp trước khi đăng vị trí!');
            redirect(BASE_PATH.'/company_profiles/edit.php');
        }
    }
}

// ── File upload ───────────────────────────
function uploadFile($file, $dir, $exts=['jpg','jpeg','png','pdf','doc','docx'], $maxMb=10){
    if(empty($file['tmp_name'])) return ['ok'=>false,'err'=>'Không có file.'];
    $ext = strtolower(pathinfo($file['name'],PATHINFO_EXTENSION));
    if(!in_array($ext,$exts))
        return ['ok'=>false,'err'=>'Định dạng không hợp lệ ('.implode(',',$exts).')'];
    if($file['size'] > $maxMb*1024*1024)
        return ['ok'=>false,'err'=>"File quá lớn (tối đa {$maxMb}MB)"];
    $dest = UPLOAD_PATH.'/'.$dir;
    if(!is_dir($dest)) mkdir($dest,0777,true);
    $name = uniqid().'_'.time().'.'.$ext;
    if(!move_uploaded_file($file['tmp_name'],$dest.'/'.$name))
        return ['ok'=>false,'err'=>'Lỗi lưu file.'];
    return ['ok'=>true,'path'=>$dir.'/'.$name];
}

// ── Messaging helpers ─────────────────────
function getOrCreateConversation($conn, $student_id, $company_id, $application_id=null){
    $sql = "SELECT conversation_id FROM conversations WHERE student_id=? AND company_id=?";
    $p   = [$student_id,$company_id]; $t='ii';
    if($application_id){ $sql.=" AND application_id=?"; $p[]=$application_id; $t.='i'; }
    $s=$conn->prepare($sql); $s->bind_param($t,...$p); $s->execute();
    $row=$s->get_result()->fetch_assoc();
    if($row) return $row['conversation_id'];
    $ins=$conn->prepare("INSERT INTO conversations (student_id,company_id,application_id) VALUES (?,?,?)");
    $ins->bind_param('iii',$student_id,$company_id,$application_id);
    $ins->execute();
    return $conn->insert_id;
}

function getUnreadCount($conn,$uid){
    $r=$conn->prepare("SELECT COUNT(*) c FROM messages m JOIN conversations c2 ON m.conversation_id=c2.conversation_id WHERE m.sender_id!=? AND m.is_read=0 AND (c2.student_id=(SELECT student_id FROM student_profiles WHERE user_id=? LIMIT 1) OR c2.company_id=(SELECT company_id FROM company_profiles WHERE user_id=? LIMIT 1))");
    $r->bind_param('iii',$uid,$uid,$uid); $r->execute();
    return $r->get_result()->fetch_assoc()['c'] ?? 0;
}

// ── Status labels ─────────────────────────
function appStatusLabel($s){
    return match($s){
        'pending_admin'       => ['⏳ Chờ trường duyệt','rgba(196,154,108,.15)','#a07040'],
        'approved_admin'      => ['✅ Trường đã duyệt','rgba(74,138,150,.15)','#3a8a96'],
        'rejected_admin'      => ['❌ Trường từ chối','rgba(192,96,80,.15)','#9a3030'],
        'approved_company'    => ['🏢 Công ty chấp nhận','rgba(74,158,106,.15)','#2d6a40'],
        'rejected_company'    => ['❌ Công ty từ chối','rgba(192,96,80,.15)','#9a3030'],
        'interview_passed'    => ['🎉 Đậu phỏng vấn','rgba(74,158,106,.2)','#1a4a2a'],
        'interview_failed'    => ['😞 Rớt phỏng vấn','rgba(192,96,80,.15)','#9a3030'],
        'internship_active'   => ['🚀 Đang thực tập','rgba(93,123,111,.15)','var(--deep-sage)'],
        'internship_completed'=> ['🏆 Hoàn thành','rgba(74,158,106,.2)','#1a4a2a'],
        default               => [$s,'rgba(160,160,160,.12)','#5a5a5a']
    };
}
