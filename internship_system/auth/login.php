<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';
if(isLoggedIn()) redirect(getDashboardUrl());

$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
    $email = trim($_POST['email'] ?? '');  // KHÔNG dùng sanitize — email cần giữ nguyên
    $pw    = $_POST['password'] ?? '';
    if(empty($email)||empty($pw)){ $error='Vui lòng nhập đầy đủ thông tin.'; }
    else{
        $s = $conn->prepare("SELECT * FROM users WHERE email=?");
        if (!$s) { $error = 'DB error: '.$conn->error; }
        else {
            $s->bind_param('s',$email); $s->execute();
            $u = $s->get_result()->fetch_assoc();
            $ok = false;
            if ($u) {
                if (strlen($u['password']) === 32) {
                    $ok = ($u['password'] === md5($pw));
                } else {
                    $ok = password_verify($pw, $u['password']);
                    if ($ok) {
                        // Migrate sang md5
                        $mig = $conn->prepare("UPDATE users SET password=? WHERE user_id=?");
                        if ($mig) { $h=md5($pw); $mig->bind_param('si',$h,$u['user_id']); $mig->execute(); }
                    }
                }
            }
            if ($ok) {
                $_SESSION['user_id']   = $u['user_id'];
                $_SESSION['role']      = $u['role'];
                $_SESSION['email']     = $u['email'];
                $display = $u['email'];
                if ($u['role'] === 'student') {
                    $n = $conn->query("SELECT full_name FROM student_profiles WHERE user_id={$u['user_id']} LIMIT 1");
                    if ($n && $row = $n->fetch_assoc()) $display = $row['full_name'] ?: $u['email'];
                } elseif ($u['role'] === 'company') {
                    $n = $conn->query("SELECT company_name FROM company_profiles WHERE user_id={$u['user_id']} LIMIT 1");
                    if ($n && $row = $n->fetch_assoc()) $display = $row['company_name'] ?: $u['email'];
                } elseif ($u['role'] === 'lecturer') {
                    $n = $conn->query("SELECT full_name FROM lecturer_profiles WHERE user_id={$u['user_id']} LIMIT 1");
                    if ($n && $row = $n->fetch_assoc()) $display = $row['full_name'] ?: $u['email'];
                } elseif ($u['role'] === 'admin') {
                    $display = 'Admin';
                }
                $_SESSION['full_name'] = $display;
                redirect(getDashboardUrl());
            } else {
                $error = 'Email hoặc mật khẩu không đúng.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Đăng nhập — ISchool Internship</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@700;800&display=swap" rel="stylesheet">
<style>
:root{--ds:#5D7B6F;--ds2:#3D5A50;--ds3:#2A3F38;--sg:#A4C3A2;--sm:#B0D4B8;--wc:#EAE7D6}
*{font-family:'Inter',sans-serif;box-sizing:border-box}
body{margin:0;min-height:100vh;display:flex;flex-direction:column;background:#eef5f2}
/* Guest Navbar */
.guest-nav{width:100%;background:rgba(255,255,255,.92);backdrop-filter:blur(10px);
  border-bottom:1px solid rgba(164,195,162,.25);padding:0 24px;height:52px;
  display:flex;align-items:center;justify-content:space-between;z-index:100;
  box-shadow:0 2px 12px rgba(93,123,111,.08)}
.guest-nav .nav-brand{display:flex;align-items:center;gap:8px;text-decoration:none;font-family:'Plus Jakarta Sans',sans-serif;font-size:.95rem;font-weight:800;color:var(--ds3)}
.guest-nav .nav-brand span{font-size:1.2rem}
.guest-nav .nav-links{display:flex;align-items:center;gap:4px}
.guest-nav .nav-links a{text-decoration:none;color:#4a6058;font-size:.82rem;font-weight:600;
  padding:6px 13px;border-radius:8px;transition:all .18s;display:flex;align-items:center;gap:5px}
.guest-nav .nav-links a:hover{background:rgba(93,123,111,.1);color:var(--ds2)}
.guest-nav .nav-links a.active{background:rgba(93,123,111,.12);color:var(--ds)}
.guest-nav .nav-actions{display:flex;align-items:center;gap:8px}
.guest-nav .nav-actions a{text-decoration:none;font-size:.82rem;font-weight:700;padding:7px 16px;border-radius:8px;transition:all .2s}
.guest-nav .btn-outline-nav{color:var(--ds);border:1.5px solid var(--ds);background:transparent}
.guest-nav .btn-outline-nav:hover{background:rgba(93,123,111,.08)}
.guest-nav .btn-solid-nav{color:#fff;background:linear-gradient(135deg,var(--ds),var(--ds2));border:none}
.guest-nav .btn-solid-nav:hover{transform:translateY(-1px);box-shadow:0 4px 12px rgba(93,123,111,.3)}
.main-layout{display:flex;flex:1}
.left{flex:1;background:linear-gradient(155deg,var(--ds3),var(--ds2),#4a8a70);
  display:flex;flex-direction:column;align-items:center;justify-content:center;
  padding:60px 40px;position:relative;overflow:hidden}
.blob{position:absolute;border-radius:50%;background:radial-gradient(circle,rgba(176,212,184,.2),transparent 70%)}
.b1{width:320px;height:320px;top:-80px;right:-60px;animation:bm 8s ease-in-out infinite}
.b2{width:240px;height:240px;bottom:-60px;left:-40px;animation:bm 10s ease-in-out infinite reverse}
@keyframes bm{0%,100%{transform:translate(0,0)}50%{transform:translate(15px,-12px)}}
.logo-emoji{font-size:3.5rem;animation:float 5s ease-in-out infinite;position:relative;z-index:1;filter:drop-shadow(0 8px 16px rgba(0,0,0,.2))}
@keyframes float{0%,100%{transform:translateY(0)}50%{transform:translateY(-10px)}}
.left h2{position:relative;z-index:1;font-family:'Plus Jakarta Sans',sans-serif;color:#fff;font-weight:800;font-size:1.8rem;text-align:center;margin:8px 0}
.left p{position:relative;z-index:1;color:rgba(255,255,255,.7);text-align:center;font-size:.9rem;line-height:1.7;max-width:320px}
.pills{position:relative;z-index:1;display:flex;flex-wrap:wrap;gap:8px;justify-content:center;margin-top:24px}
.pill{background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.18);color:rgba(255,255,255,.88);
  padding:7px 14px;border-radius:50px;font-size:.75rem;font-weight:600;display:flex;align-items:center;gap:5px}
.right{width:440px;display:flex;align-items:center;justify-content:center;padding:48px 48px;background:#fff}
.form-wrap{width:100%}
.greeting{font-family:'Plus Jakarta Sans',sans-serif;font-size:1.65rem;font-weight:800;color:var(--ds3);margin-bottom:4px}
.sub{color:#7a9590;font-size:.86rem;margin-bottom:28px}
.form-control{border-radius:9px;border:1.5px solid rgba(164,195,162,.35);padding:10px 13px;font-size:.88rem;background:rgba(164,195,162,.05);transition:all .2s}
.form-control:focus{border-color:var(--ds);box-shadow:0 0 0 3px rgba(93,123,111,.12);background:#fff;outline:none}
.form-label{font-weight:600;font-size:.78rem;color:#4a6058;margin-bottom:4px}
.igt{border-radius:9px 0 0 9px;border:1.5px solid rgba(164,195,162,.35);background:var(--wc);color:var(--ds);border-right:none}
.input-group .form-control{border-radius:0 9px 9px 0;border-left-color:transparent}
.btn-login{background:linear-gradient(135deg,var(--ds),var(--ds2));border:none;border-radius:9px;
  padding:11px;font-weight:700;font-size:.92rem;color:#fff;width:100%;transition:all .28s}
.btn-login:hover{transform:translateY(-2px);box-shadow:0 7px 20px rgba(93,123,111,.38);color:#fff}
.divider{display:flex;align-items:center;gap:10px;margin:18px 0;color:#a4c3a2;font-size:.75rem}
.divider::before,.divider::after{content:'';flex:1;height:1px;background:rgba(164,195,162,.28)}
.demo-box{background:rgba(234,231,214,.4);border:1.5px solid rgba(164,195,162,.28);border-radius:11px;padding:14px}
.demo-box h6{font-size:.7rem;font-weight:700;color:var(--ds);text-transform:uppercase;letter-spacing:.7px;margin-bottom:8px}
.demo-row{display:flex;justify-content:space-between;align-items:center;padding:5px 0;
  border-bottom:1px solid rgba(164,195,162,.18);font-size:.77rem;cursor:pointer;border-radius:4px;transition:background .14s}
.demo-row:last-child{border-bottom:none}
.demo-row:hover{background:rgba(164,195,162,.12);padding-left:4px}
.demo-em{color:var(--ds3);font-weight:500}
@media(max-width:768px){.left{display:none}.right{width:100%;padding:28px 20px}.guest-nav .nav-links{display:none}}
</style>
</head>
<body>

<!-- Guest Navbar -->
<nav class="guest-nav">
  <a href="/internship-management-system/public/index.php" class="nav-brand"><span>🎓</span>ISchool Internship</a>
  <div class="nav-links">
    <a href="/internship-management-system/public/index.php"><i class="bi bi-house-fill"></i>Trang chủ</a>
    <a href="/internship-management-system/public/internships.php"><i class="bi bi-briefcase-fill"></i>Vị trí thực tập</a>
    <a href="/internship-management-system/public/about.php"><i class="bi bi-info-circle-fill"></i>Giới thiệu</a>
  </div>
  <div class="nav-actions">
    <a href="register.php" class="btn-outline-nav">Đăng ký</a>
    <a href="login.php" class="btn-solid-nav active">Đăng nhập</a>
  </div>
</nav>

<div class="main-layout">
<div class="left">
  <div class="blob b1"></div><div class="blob b2"></div>
  <div class="logo-emoji">🎓</div>
  <h2>ISchool Internship</h2>
  <p>Nền tảng quản lý thực tập toàn diện — kết nối sinh viên, doanh nghiệp và nhà trường.</p>
  <div class="pills">
    <div class="pill"><i class="bi bi-mortarboard-fill"></i>Sinh viên</div>
    <div class="pill"><i class="bi bi-building-fill"></i>Doanh nghiệp</div>
    <div class="pill"><i class="bi bi-shield-fill-check"></i>Quản trị</div>
    <div class="pill"><i class="bi bi-person-workspace"></i>Giảng viên</div>
  </div>
</div>
<div class="right">
  <div class="form-wrap">
    <div class="greeting">Chào mừng 👋</div>
    <p class="sub">Đăng nhập để quản lý kỳ thực tập</p>

    <?php if($error): ?>
    <div class="alert alert-danger" style="border-radius:9px;font-size:.84rem;padding:10px 14px;margin-bottom:14px">
      <i class="bi bi-exclamation-triangle-fill me-2"></i><?=htmlspecialchars($error)?>
    </div>
    <?php endif; ?>

    <form method="POST">
      <div class="mb-3">
        <label class="form-label">Email</label>
        <div class="input-group">
          <span class="input-group-text igt"><i class="bi bi-envelope-fill"></i></span>
          <input type="email" name="email" class="form-control" placeholder="email@example.com"
                 value="<?=htmlspecialchars($_POST['email']??'')?>" required>
        </div>
      </div>
      <div class="mb-4">
        <label class="form-label">Mật khẩu</label>
        <div class="input-group">
          <span class="input-group-text igt"><i class="bi bi-lock-fill"></i></span>
          <input type="password" name="password" id="pw" class="form-control" placeholder="••••••••" required>
          <button type="button" class="input-group-text igt" style="border-left:none;border-radius:0 9px 9px 0;cursor:pointer"
                  onclick="const p=document.getElementById('pw');p.type=p.type==='password'?'text':'password'">
            <i class="bi bi-eye"></i>
          </button>
        </div>
      </div>
      <button type="submit" class="btn-login">
        <i class="bi bi-box-arrow-in-right me-2"></i>Đăng nhập
      </button>
    </form>

    <div class="divider">hoặc</div>
    <div class="text-center mb-3" style="font-size:.83rem;color:#7a9590">
      Chưa có tài khoản?
      <a href="register.php" style="color:var(--ds);font-weight:700;text-decoration:none">Đăng ký ngay</a>
    </div>
<?php /*
    <div class="demo-box">
      <h6><i class="bi bi-info-circle me-1"></i>Tài khoản demo — click để điền</h6>
      <?php $demos=[['admin@ischool.edu.vn','admin123','Admin','#9a3030','rgba(192,96,80,.12)'],
                    ['student1@ischool.edu.vn','pass123','Sinh viên','#2d6a58','rgba(93,123,111,.12)'],
                    ['hr@fpt.com','pass123','Doanh nghiệp','#a07040','rgba(196,154,108,.12)'],
                    ['lecturer1@ischool.edu.vn','pass123','Giảng viên','#3d8a58','rgba(74,158,106,.12)']];
      foreach($demos as [$e,$p,$r,$c,$bg]): ?>
      <div class="demo-row" onclick="fill('<?=$e?>','<?=$p?>')">
        <span class="demo-em"><i class="bi bi-cursor-fill me-1" style="font-size:.6rem;opacity:.5"></i><?=$e?> / <?=$p?></span>
        <span style="padding:2px 7px;border-radius:4px;font-size:.67rem;font-weight:700;background:<?=$bg?>;color:<?=$c?>"><?=$r?></span>
      </div>
      <?php endforeach; ?>
    </div>
*/ ?>
  </div>
</div><!-- /.main-layout -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
function fill(e,p){
  document.querySelector('[name=email]').value=e;
  document.querySelector('[name=password]').value=p;
}
</script>
</body></html>
