<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';
if (isLoggedIn()) redirect(getBaseUrl().'/index.php');
$errors=[];
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $email    = sanitize($_POST['email']??'');
    $password = $_POST['password']??'';
    if (empty($email))    $errors[]='Vui lòng nhập email.';
    if (empty($password)) $errors[]='Vui lòng nhập mật khẩu.';
    if (empty($errors)) {
        $stmt=$conn->prepare("SELECT * FROM users WHERE email=? AND status='active' LIMIT 1");
        $stmt->bind_param('s',$email); $stmt->execute();
        $user=$stmt->get_result()->fetch_assoc();
        if ($user && (password_verify($password,$user['password']) || md5($password)===$user['password'])) {
            // Tự động nâng cấp hash md5 cũ lên password_hash
            if (md5($password)===$user['password']) {
                $newHash=password_hash($password,PASSWORD_DEFAULT);
                $upd=$conn->prepare("UPDATE users SET password=? WHERE user_id=?");
                $upd->bind_param('si',$newHash,$user['user_id']);
                $upd->execute();
            }
            $_SESSION['user_id']  =$user['user_id'];
            $_SESSION['full_name']=$user['full_name'];
            $_SESSION['role']     =$user['role'];
            $_SESSION['email']    =$user['email'];
            redirect(getBaseUrl().'/index.php');
        } else {
            $errors[]='Email hoặc mật khẩu không đúng, hoặc tài khoản đã bị vô hiệu hóa.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Đăng nhập · ISchool</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700;14..32,800&display=swap" rel="stylesheet">
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
            padding:24px; -webkit-font-smoothing:antialiased;
        }
        .auth-wrap{
            display:flex; width:100%; max-width:900px; min-height:540px;
            border-radius:24px; overflow:hidden;
            box-shadow:0 16px 48px rgba(44,58,44,.15);
        }
        /* Left */
        .auth-left{
            width:280px; flex-shrink:0;
            background:var(--sidebar);
            padding:40px 32px;
            display:flex; flex-direction:column; justify-content:space-between;
            color:#fff; position:relative; overflow:hidden;
        }
        .auth-left::before{
            content:''; position:absolute;
            width:260px; height:260px; border-radius:50%;
            background:rgba(168,213,186,.1);
            top:-60px; right:-80px;
        }
        .auth-left::after{
            content:''; position:absolute;
            width:160px; height:160px; border-radius:50%;
            background:rgba(126,200,200,.08);
            bottom:-40px; left:-40px;
        }
        .auth-brand{ font-size:1.5rem; font-weight:800; letter-spacing:-.3px; position:relative; z-index:1; }
        .auth-brand span{ color:var(--mint); }
        .auth-tagline{ font-size:.88rem; opacity:.7; line-height:1.6; position:relative; z-index:1; }
        .auth-features{ list-style:none; padding:0; margin:0; position:relative; z-index:1; }
        .auth-features li{
            display:flex; align-items:center; gap:10px;
            font-size:.82rem; opacity:.8; margin-bottom:12px;
        }
        .auth-features li i{
            width:28px; height:28px; background:rgba(255,255,255,.12);
            border-radius:8px; display:flex; align-items:center; justify-content:center;
            font-size:.8rem; flex-shrink:0;
        }
        /* Right */
        .auth-right{
            flex:1; background:#fff;
            padding:48px 44px;
            display:flex; flex-direction:column; justify-content:center;
        }
        .auth-right h2{ font-size:1.75rem; font-weight:800; color:var(--text); margin-bottom:4px; letter-spacing:-.4px; }
        .auth-right .auth-sub{ font-size:.84rem; color:var(--muted); margin-bottom:32px; }
        .form-label{ font-weight:600; font-size:.82rem; color:var(--text); margin-bottom:6px; }
        .form-control{
            border-radius:12px; border:1.5px solid var(--border);
            font-size:.875rem; padding:11px 14px;
            background:#fafafa; font-family:inherit;
            transition:border-color .2s,box-shadow .2s;
        }
        .form-control:focus{
            border-color:var(--accent);
            box-shadow:0 0 0 3px rgba(90,138,90,.1);
            background:#fff; outline:none;
        }
        .input-group .form-control{ border-radius:12px 0 0 12px; }
        .btn-eye{
            border:1.5px solid var(--border); border-left:none;
            border-radius:0 12px 12px 0; background:#fafafa;
            color:var(--muted); padding:0 14px; cursor:pointer;
            transition:background .15s;
        }
        .btn-eye:hover{ background:var(--mint-lt); }
        .btn-login{
            background:var(--sidebar); color:#fff; border:none;
            border-radius:12px; padding:12px; font-weight:700;
            font-size:.9rem; width:100%; font-family:inherit;
            cursor:pointer; transition:background .2s,transform .15s;
        }
        .btn-login:hover{ background:var(--sid-dark); transform:translateY(-1px); }
        .divider{
            display:flex; align-items:center; gap:12px;
            margin:22px 0; color:var(--muted); font-size:.76rem;
        }
        .divider::before,.divider::after{ content:''; flex:1; height:1px; background:var(--border); }
        .alert-err{
            background:#fef2f2; border-left:4px solid #f87171;
            border-radius:12px; color:#991b1b;
            font-size:.84rem; padding:12px 16px; margin-bottom:20px;
        }
        .footer-link{ text-align:center; margin-top:20px; font-size:.82rem; color:var(--muted); }
        .footer-link a{ color:var(--accent); font-weight:600; text-decoration:none; }
        .footer-link a:hover{ text-decoration:underline; }
        .demo-box{
            margin-top:24px; padding:14px 16px;
            background:var(--teal-lt); border-radius:12px;
            border:1px solid rgba(126,200,200,.4);
        }
        .demo-box .demo-lbl{
            font-size:.68rem; font-weight:700; color:#1a6b6b;
            text-transform:uppercase; letter-spacing:.8px; margin-bottom:8px;
        }
        .demo-box .demo-row{ font-size:.79rem; color:var(--text); line-height:1.9; }
        @media(max-width:680px){
            .auth-left{display:none;}
            .auth-right{padding:36px 28px;}
            .auth-wrap{max-width:440px;}
        }
    </style>
</head>
<body>
<div class="auth-wrap">
    <div class="auth-left">
        <div class="auth-brand">ISCHOOL<span>.</span></div>
        <div>
            <div class="auth-tagline" style="margin-bottom:28px">Nền tảng quản lý thực tập toàn diện cho sinh viên, giảng viên và doanh nghiệp.</div>
            <ul class="auth-features">
                <li><i class="bi bi-person-check-fill"></i> Theo dõi tiến độ theo tuần</li>
                <li><i class="bi bi-building-fill"></i> Kết nối doanh nghiệp đối tác</li>
                <li><i class="bi bi-star-fill"></i> Đánh giá đa chiều</li>
                <li><i class="bi bi-award-fill"></i> Tổng hợp điểm tự động</li>
            </ul>
        </div>
        <div style="font-size:.75rem;opacity:.45;position:relative;z-index:1">© 2025 ISchool · INS3064</div>
    </div>
    <div class="auth-right">
        <h2>Chào mừng trở lại</h2>
        <p class="auth-sub">Đăng nhập để tiếp tục vào hệ thống</p>

        <?php if (!empty($errors)): ?>
        <div class="alert-err"><i class="bi bi-exclamation-circle me-2"></i><?= implode('<br>',$errors) ?></div>
        <?php endif; ?>

        <form method="POST" autocomplete="off">
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control"
                       placeholder="example@email.com"
                       value="<?= htmlspecialchars($_POST['email']??'') ?>" required autofocus>
            </div>
            <div class="mb-4">
                <label class="form-label">Mật khẩu</label>
                <div class="input-group">
                    <input type="password" name="password" id="pw" class="form-control" placeholder="••••••••" required>
                    <button type="button" class="btn-eye" onclick="togglePw('pw','pwi')"><i class="bi bi-eye" id="pwi"></i></button>
                </div>
            </div>
            <button type="submit" class="btn-login"><i class="bi bi-box-arrow-in-right me-2"></i>Đăng nhập</button>
        </form>

        <div class="divider">hoặc</div>
        <div class="footer-link">Chưa có tài khoản? <a href="register.php">Đăng ký ngay</a></div>

        <div class="demo-box">
            <div class="demo-lbl">Tài khoản demo · mật khẩu: 123456</div>
            <div class="demo-row">
                <strong>Admin:</strong> admin@ischool.edu.vn<br>
                <strong>Giảng viên:</strong> lecturer@ischool.edu.vn<br>
                <strong>Sinh viên:</strong> student@ischool.edu.vn<br>
                <strong>Doanh nghiệp:</strong> company@corp.vn
            </div>
        </div>
    </div>
</div>
<script>
function togglePw(id,icId){
    const p=document.getElementById(id),i=document.getElementById(icId);
    p.type=p.type==='password'?'text':'password';
    i.className=p.type==='text'?'bi bi-eye-slash':'bi bi-eye';
}
</script>
</body>
</html>
