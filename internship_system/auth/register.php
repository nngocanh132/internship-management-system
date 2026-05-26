<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';
if (isLoggedIn()) redirect(getBaseUrl().'/index.php');
$errors=[]; $success=false;
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $full_name   =sanitize($_POST['full_name']??'');
    $email       =sanitize($_POST['email']??'');
    $password    =$_POST['password']??'';
    $confirm_pw  =$_POST['confirm_password']??'';
    $role        =sanitize($_POST['role']??'');
    $phone       =sanitize($_POST['phone']??'');
    $student_code=sanitize($_POST['student_code']??'');
    $major       =sanitize($_POST['major']??'');
    if (empty($full_name)) $errors[]='Họ tên không được để trống.';
    if (empty($email)||!filter_var($email,FILTER_VALIDATE_EMAIL)) $errors[]='Email không hợp lệ.';
    if (strlen($password)<6) $errors[]='Mật khẩu phải có ít nhất 6 ký tự.';
    if ($password!==$confirm_pw) $errors[]='Mật khẩu xác nhận không khớp.';
    if (!in_array($role,['student','company','lecturer','admin'])) $errors[]='Vui lòng chọn loại tài khoản.';
    if (empty($errors)) {
        $chk=$conn->prepare("SELECT user_id FROM users WHERE email=?");
        $chk->bind_param('s',$email); $chk->execute();
        if ($chk->get_result()->num_rows>0) $errors[]='Email đã được sử dụng.';
    }
    if (empty($errors)&&$role==='student'&&!empty($student_code)) {
        $chk2=$conn->prepare("SELECT user_id FROM users WHERE student_code=?");
        $chk2->bind_param('s',$student_code); $chk2->execute();
        if ($chk2->get_result()->num_rows>0) $errors[]='Mã sinh viên đã tồn tại.';
    }
    if (empty($errors)) {
        $hashed=password_hash($password,PASSWORD_DEFAULT);
        $code=($role==='student'&&!empty($student_code))?$student_code:null;
        $maj=!empty($major)?$major:null;
        $ph=!empty($phone)?$phone:null;
        $stmt=$conn->prepare("INSERT INTO users (full_name,email,password,role,phone,student_code,major,status) VALUES (?,?,?,?,?,?,?,'active')");
        $stmt->bind_param('sssssss',$full_name,$email,$hashed,$role,$ph,$code,$maj);
        if ($stmt->execute()) $success=true;
        else $errors[]='Lỗi: '.$conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Đăng ký · ISchool</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,400;14..32,500;14..32,600;14..32,700;14..32,800&display=swap" rel="stylesheet">
    <style>
        :root{
            --bg:#E8E8DC; --sidebar:#4A6741; --sid-dark:#3a5232;
            --mint:#A8D5BA; --mint-lt:#D4EDE0; --teal:#7EC8C8; --teal-lt:#C8ECEC;
            --accent:#5A8A5A; --text:#2C3A2C; --muted:#7A8C7A; --border:rgba(74,103,65,.15);
        }
        *,*::before,*::after{box-sizing:border-box;}
        body{
            font-family:'Inter',system-ui,sans-serif;
            background:var(--bg); min-height:100vh;
            display:flex; align-items:center; justify-content:center;
            padding:32px 24px; -webkit-font-smoothing:antialiased;
        }
        .reg-card{
            background:#fff; border-radius:24px;
            box-shadow:0 16px 48px rgba(44,58,44,.12);
            width:100%; max-width:560px; overflow:hidden;
        }
        .reg-header{
            background:var(--sidebar); padding:32px 40px 24px; color:#fff;
        }
        .reg-header .brand{ font-size:1.2rem; font-weight:800; margin-bottom:14px; }
        .reg-header .brand span{ color:var(--mint); }
        .reg-header h3{ font-size:1.35rem; font-weight:800; margin:0; }
        .reg-header p{ font-size:.82rem; opacity:.7; margin:4px 0 0; }
        .reg-body{ padding:32px 40px 36px; }
        .form-label{ font-weight:600; font-size:.82rem; color:var(--text); margin-bottom:6px; }
        .form-control,.form-select{
            border-radius:12px; border:1.5px solid var(--border);
            font-size:.875rem; padding:10px 14px;
            background:#fafafa; font-family:inherit;
            transition:border-color .2s,box-shadow .2s;
        }
        .form-control:focus,.form-select:focus{
            border-color:var(--accent);
            box-shadow:0 0 0 3px rgba(90,138,90,.1);
            background:#fff; outline:none;
        }
        .input-group .form-control{ border-radius:12px 0 0 12px; }
        .btn-eye{
            border:1.5px solid var(--border); border-left:none;
            border-radius:0 12px 12px 0; background:#fafafa;
            color:var(--muted); padding:0 14px; cursor:pointer;
        }
        .btn-eye:hover{ background:var(--mint-lt); }
        .role-grid{ display:grid; grid-template-columns:1fr 1fr; gap:10px; }
        .role-opt{ position:relative; }
        .role-opt input{ position:absolute; opacity:0; width:0; height:0; }
        .role-opt label{
            display:flex; flex-direction:column; align-items:center; gap:6px;
            padding:14px 10px; border-radius:14px;
            border:2px solid var(--border);
            cursor:pointer; transition:all .15s;
            background:#fafafa; text-align:center;
        }
        .role-opt label i{ font-size:1.4rem; color:var(--muted); transition:color .15s; }
        .role-opt label span{ font-size:.82rem; font-weight:600; color:var(--text); }
        .role-opt label small{ font-size:.7rem; color:var(--muted); }
        .role-opt input:checked+label{ border-color:var(--accent); background:var(--mint-lt); }
        .role-opt input:checked+label i{ color:var(--accent); }
        .btn-reg{
            background:var(--sidebar); color:#fff; border:none;
            border-radius:12px; padding:12px; font-weight:700;
            font-size:.9rem; width:100%; font-family:inherit;
            cursor:pointer; transition:background .2s,transform .15s;
        }
        .btn-reg:hover{ background:var(--sid-dark); transform:translateY(-1px); }
        .alert-err{
            background:#fef2f2; border-left:4px solid #f87171;
            border-radius:12px; color:#991b1b;
            font-size:.84rem; padding:12px 16px; margin-bottom:16px;
        }
        .alert-ok{
            background:var(--teal-lt); border-left:4px solid var(--teal);
            border-radius:12px; color:#1a6b6b;
            font-size:.88rem; padding:14px 18px;
        }
        .footer-link{ text-align:center; margin-top:18px; font-size:.82rem; color:var(--muted); }
        .footer-link a{ color:var(--accent); font-weight:600; text-decoration:none; }
        .footer-link a:hover{ text-decoration:underline; }
        #student-fields{ display:none; }
    </style>
</head>
<body>
<div class="reg-card">
    <div class="reg-header">
        <div class="brand">ISCHOOL<span>.</span></div>
        <h3>Tạo tài khoản mới</h3>
        <p>Điền thông tin để đăng ký tham gia hệ thống</p>
    </div>
    <div class="reg-body">
        <?php if ($success): ?>
        <div class="alert-ok">
            <i class="bi bi-check-circle-fill me-2"></i>
            <strong>Đăng ký thành công!</strong> Tài khoản đã được tạo.
            <div class="mt-2"><a href="login.php" style="color:var(--accent);font-weight:700"><i class="bi bi-box-arrow-in-right me-1"></i>Đăng nhập ngay →</a></div>
        </div>
        <?php else: ?>
        <?php if (!empty($errors)): ?>
        <div class="alert-err"><i class="bi bi-exclamation-circle me-2"></i>
            <ul class="mb-0 ps-3 mt-1"><?php foreach($errors as $e): ?><li><?= $e ?></li><?php endforeach; ?></ul>
        </div>
        <?php endif; ?>
        <form method="POST">
            <div class="mb-4">
                <label class="form-label">Loại tài khoản <span class="text-danger">*</span></label>
                <div class="role-grid">
                    <div class="role-opt">
                        <input type="radio" name="role" id="r-student" value="student" <?= ($_POST['role']??'')==='student'?'checked':'' ?>>
                        <label for="r-student"><i class="bi bi-mortarboard-fill"></i><span>Sinh viên</span><small>Đăng ký thực tập</small></label>
                    </div>
                    <div class="role-opt">
                        <input type="radio" name="role" id="r-company" value="company" <?= ($_POST['role']??'')==='company'?'checked':'' ?>>
                        <label for="r-company"><i class="bi bi-building-fill"></i><span>Doanh nghiệp</span><small>Đăng vị trí</small></label>
                    </div>
                    <div class="role-opt">
                        <input type="radio" name="role" id="r-lecturer" value="lecturer" <?= ($_POST['role']??'')==='lecturer'?'checked':'' ?>>
                        <label for="r-lecturer"><i class="bi bi-person-workspace"></i><span>Giảng viên</span><small>Hướng dẫn SV</small></label>
                    </div>
                    <div class="role-opt">
                        <input type="radio" name="role" id="r-admin" value="admin" <?= ($_POST['role']??'')==='admin'?'checked':'' ?>>
                        <label for="r-admin"><i class="bi bi-shield-fill-check"></i><span>Quản trị viên</span><small>Quản lý hệ thống</small></label>
                    </div>
                </div>
            </div>
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label">Họ và tên <span class="text-danger">*</span></label>
                    <input type="text" name="full_name" class="form-control" value="<?= htmlspecialchars($_POST['full_name']??'') ?>" placeholder="Nguyễn Văn A" required>
                </div>
                <div class="col-12">
                    <label class="form-label">Email <span class="text-danger">*</span></label>
                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($_POST['email']??'') ?>" placeholder="example@email.com" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Mật khẩu <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="password" name="password" id="pw1" class="form-control" placeholder="Tối thiểu 6 ký tự" minlength="6" required>
                        <button type="button" class="btn-eye" onclick="togglePw('pw1','i1')"><i class="bi bi-eye" id="i1"></i></button>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Xác nhận mật khẩu <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="password" name="confirm_password" id="pw2" class="form-control" placeholder="Nhập lại" required>
                        <button type="button" class="btn-eye" onclick="togglePw('pw2','i2')"><i class="bi bi-eye" id="i2"></i></button>
                    </div>
                </div>
                <div class="col-12">
                    <label class="form-label">Số điện thoại</label>
                    <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($_POST['phone']??'') ?>" placeholder="0901234567">
                </div>
            </div>
            <div id="student-fields" class="mt-3">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Mã sinh viên</label>
                        <input type="text" name="student_code" class="form-control" value="<?= htmlspecialchars($_POST['student_code']??'') ?>" placeholder="SV001">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Chuyên ngành</label>
                        <input type="text" name="major" class="form-control" value="<?= htmlspecialchars($_POST['major']??'') ?>" placeholder="Công nghệ thông tin">
                    </div>
                </div>
            </div>
            <div class="mt-4">
                <button type="submit" class="btn-reg"><i class="bi bi-person-plus-fill me-2"></i>Tạo tài khoản</button>
            </div>
        </form>
        <div class="footer-link">Đã có tài khoản? <a href="login.php">Đăng nhập</a></div>
        <?php endif; ?>
    </div>
</div>
<script>
function togglePw(id,icId){
    const p=document.getElementById(id),i=document.getElementById(icId);
    p.type=p.type==='password'?'text':'password';
    i.className=p.type==='text'?'bi bi-eye-slash':'bi bi-eye';
}
function updateRoleFields(){
    const role=document.querySelector('input[name="role"]:checked')?.value;
    document.getElementById('student-fields').style.display=role==='student'?'block':'none';
}
document.querySelectorAll('input[name="role"]').forEach(r=>r.addEventListener('change',updateRoleFields));
updateRoleFields();
</script>
</body>
</html>
