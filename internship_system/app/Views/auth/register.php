<?php // View: auth/register — nhận $errors, $success từ AuthController ?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Đăng ký — ISchool Internship</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@700;800&display=swap" rel="stylesheet">
<style>
:root{--ds:#5D7B6F;--ds2:#3D5A50;--ds3:#2A3F38;--sg:#A4C3A2;--sm:#B0D4B8;--wc:#EAE7D6}
*{font-family:'Inter',sans-serif;box-sizing:border-box}
body{margin:0;min-height:100vh;background:linear-gradient(135deg,#eef5f2,#e6efe8);display:flex;flex-direction:column;align-items:center;padding:0}
.guest-nav{width:100%;background:rgba(255,255,255,.92);backdrop-filter:blur(10px);border-bottom:1px solid rgba(164,195,162,.25);padding:0 24px;height:52px;display:flex;align-items:center;justify-content:space-between;z-index:100;box-shadow:0 2px 12px rgba(93,123,111,.08)}
.guest-nav .nav-brand{display:flex;align-items:center;gap:8px;text-decoration:none;font-family:'Plus Jakarta Sans',sans-serif;font-size:.95rem;font-weight:800;color:var(--ds3)}
.guest-nav .nav-links{display:flex;align-items:center;gap:4px}
.guest-nav .nav-links a{text-decoration:none;color:#4a6058;font-size:.82rem;font-weight:600;padding:6px 13px;border-radius:8px;transition:all .18s;display:flex;align-items:center;gap:5px}
.guest-nav .nav-links a:hover{background:rgba(93,123,111,.1);color:var(--ds2)}
.guest-nav .nav-actions{display:flex;align-items:center;gap:8px}
.guest-nav .nav-actions a{text-decoration:none;font-size:.82rem;font-weight:700;padding:7px 16px;border-radius:8px;transition:all .2s}
.btn-outline-nav{color:var(--ds);border:1.5px solid var(--ds);background:transparent}
.btn-outline-nav:hover{background:rgba(93,123,111,.08)}
.btn-solid-nav{color:#fff;background:linear-gradient(135deg,var(--ds),var(--ds2));border:none}
.btn-solid-nav:hover{transform:translateY(-1px);box-shadow:0 4px 12px rgba(93,123,111,.3)}
.page-content{width:100%;display:flex;align-items:center;justify-content:center;padding:24px 16px;flex:1}
.wrap{width:100%;max-width:520px}
.rh{background:linear-gradient(135deg,var(--ds3),var(--ds2),#4a8a70);border-radius:18px 18px 0 0;padding:28px 32px 24px;color:#fff;position:relative;overflow:hidden}
.rh::before{content:'';position:absolute;top:-50px;right:-50px;width:180px;height:180px;background:radial-gradient(circle,rgba(176,212,184,.2),transparent 70%);border-radius:50%}
.rh .brand{font-family:'Plus Jakarta Sans',sans-serif;font-size:.9rem;font-weight:800;color:var(--sm);letter-spacing:2px;text-transform:uppercase;margin-bottom:8px;display:flex;align-items:center;gap:7px;position:relative;z-index:1}
.rh h3{font-family:'Plus Jakarta Sans',sans-serif;font-size:1.4rem;font-weight:800;margin:0 0 3px;position:relative;z-index:1}
.rh p{font-size:.82rem;opacity:.72;margin:0;position:relative;z-index:1}
.rb{background:#fff;border-radius:0 0 18px 18px;padding:28px 32px 32px;box-shadow:0 14px 40px rgba(93,123,111,.14)}
.form-label{font-weight:600;font-size:.78rem;color:var(--ds2);margin-bottom:4px}
.form-control,.form-select{border-radius:9px;border:1.5px solid rgba(164,195,162,.35);font-size:.87rem;padding:10px 13px;background:rgba(164,195,162,.04);transition:all .2s}
.form-control:focus,.form-select:focus{border-color:var(--ds);box-shadow:0 0 0 3px rgba(93,123,111,.1);background:#fff;outline:none}
.input-group .form-control{border-radius:9px 0 0 9px}
.eye-btn{border:1.5px solid rgba(164,195,162,.35);border-left:none;border-radius:0 9px 9px 0;background:rgba(164,195,162,.04);color:var(--tl);padding:0 12px;cursor:pointer}
.role-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px}
.ro{position:relative}
.ro input{position:absolute;opacity:0;width:0;height:0}
.ro label{display:flex;flex-direction:column;align-items:center;gap:6px;padding:16px 10px 12px;border-radius:12px;border:2px solid rgba(164,195,162,.3);cursor:pointer;transition:all .18s;background:rgba(164,195,162,.04);text-align:center}
.ro label .ri{width:42px;height:42px;border-radius:11px;background:rgba(164,195,162,.12);display:flex;align-items:center;justify-content:center;font-size:1.25rem;transition:all .18s}
.ro label .ri i{color:#7a9590;transition:color .18s}
.ro label .rn{font-size:.82rem;font-weight:700;color:var(--ds3)}
.ro label .rs{font-size:.67rem;color:#7a9590;line-height:1.3}
.ro input:checked+label{border-color:var(--ds);background:rgba(93,123,111,.06)}
.ro input:checked+label .ri{background:rgba(93,123,111,.15)}
.ro input:checked+label .ri i{color:var(--ds)}
.ro label:hover{border-color:var(--sg);transform:translateY(-2px);box-shadow:0 4px 10px rgba(93,123,111,.1)}
#sf{background:rgba(176,212,184,.07);border:1.5px solid rgba(164,195,162,.28);border-radius:11px;padding:14px;margin-top:4px}
.swrap{height:3px;background:rgba(164,195,162,.2);border-radius:2px;margin-top:5px}
.sbar{height:3px;border-radius:2px;transition:all .3s;background:transparent}
.btn-reg{background:linear-gradient(135deg,var(--ds),var(--ds2));border:none;border-radius:10px;padding:12px;font-weight:700;font-size:.92rem;color:#fff;width:100%;cursor:pointer;transition:all .25s}
.btn-reg:hover{transform:translateY(-2px);box-shadow:0 7px 20px rgba(93,123,111,.35)}
.alert-e{background:rgba(192,96,80,.07);border-left:4px solid #c06050;border-radius:10px;color:#7a2020;font-size:.82rem;padding:10px 14px;margin-bottom:16px}
.alert-ok{background:rgba(74,158,106,.1);border-left:4px solid #4a9e6a;border-radius:12px;color:#1a4a30;padding:20px;text-align:center}
.divl{display:flex;align-items:center;gap:10px;color:#a4c3a2;font-size:.75rem;margin:16px 0}
.divl::before,.divl::after{content:'';flex:1;height:1px;background:rgba(164,195,162,.28)}
@media(max-width:600px){.guest-nav .nav-links{display:none}}
</style>
</head>
<body>
<nav class="guest-nav">
  <a href="/internship-management-system/public/index.php" class="nav-brand"><span>🎓</span>ISchool Internship</a>
  <div class="nav-links">
    <a href="/internship-management-system/public/index.php"><i class="bi bi-house-fill"></i>Trang chủ</a>
    <a href="/internship-management-system/public/internships.php"><i class="bi bi-briefcase-fill"></i>Vị trí thực tập</a>
    <a href="/internship-management-system/public/about.php"><i class="bi bi-info-circle-fill"></i>Giới thiệu</a>
  </div>
  <div class="nav-actions">
    <a href="login.php" class="btn-outline-nav">Đăng nhập</a>
    <a href="register.php" class="btn-solid-nav">Đăng ký</a>
  </div>
</nav>
<div class="page-content">
<div class="wrap">
  <div class="rh">
    <div class="brand"><i class="bi bi-mortarboard-fill"></i>ISchool Internship</div>
    <h3>Tạo tài khoản mới</h3>
    <p>Tham gia hệ thống quản lý thực tập</p>
  </div>
  <div class="rb">
    <?php if($success): ?>
    <div class="alert-ok">
      <div style="font-size:2.5rem;margin-bottom:8px">🎉</div>
      <div style="font-family:'Plus Jakarta Sans',sans-serif;font-size:1.15rem;font-weight:800;margin-bottom:6px">Đăng ký thành công!</div>
      <p style="font-size:.86rem;margin-bottom:14px;opacity:.8">Đăng nhập để hoàn thiện hồ sơ và bắt đầu sử dụng.</p>
      <a href="login.php" style="display:inline-flex;align-items:center;gap:7px;background:linear-gradient(135deg,#5D7B6F,#3D5A50);color:#fff;padding:10px 22px;border-radius:9px;text-decoration:none;font-weight:700;font-size:.88rem">
        <i class="bi bi-box-arrow-in-right"></i>Đăng nhập ngay
      </a>
    </div>
    <?php else: ?>
    <?php if(!empty($errors)): ?>
    <div class="alert-e"><i class="bi bi-exclamation-triangle-fill me-2"></i>
      <ul class="mb-0 ps-3 mt-1"><?php foreach($errors as $e): ?><li><?=htmlspecialchars($e)?></li><?php endforeach; ?></ul>
    </div>
    <?php endif; ?>
    <form method="POST" id="rf">
      <div class="mb-4">
        <label class="form-label">Loại tài khoản <span class="text-danger">*</span></label>
        <div class="role-grid">
          <div class="ro">
            <input type="radio" name="role" id="rs" value="student" <?=($_POST['role']??'')==='student'?'checked':''?>>
            <label for="rs"><div class="ri"><i class="bi bi-mortarboard-fill"></i></div><span class="rn">Sinh viên</span><span class="rs">Tìm &amp; ứng tuyển thực tập</span></label>
          </div>
          <div class="ro">
            <input type="radio" name="role" id="rc" value="company" <?=($_POST['role']??'')==='company'?'checked':''?>>
            <label for="rc"><div class="ri"><i class="bi bi-building-fill"></i></div><span class="rn">Doanh nghiệp</span><span class="rs">Đăng vị trí thực tập</span></label>
          </div>
        </div>
      </div>
      <div class="row g-3">
        <div class="col-12">
          <label class="form-label" id="fn-label">Họ và tên <span class="text-danger">*</span></label>
          <input type="text" name="full_name" class="form-control" required value="<?=htmlspecialchars($_POST['full_name']??'')?>" placeholder="Nguyễn Văn A">
        </div>
        <div class="col-12">
          <label class="form-label">Email <span class="text-danger">*</span></label>
          <input type="email" name="email" class="form-control" required value="<?=htmlspecialchars($_POST['email']??'')?>" placeholder="email@example.com">
        </div>
        <div class="col-md-6">
          <label class="form-label">Mật khẩu <span class="text-danger">*</span></label>
          <div class="input-group">
            <input type="password" name="password" id="p1" class="form-control" minlength="6" placeholder="Tối thiểu 6 ký tự" oninput="strength(this.value)" required>
            <button type="button" class="eye-btn" onclick="toggle('p1','e1')"><i class="bi bi-eye" id="e1"></i></button>
          </div>
          <div class="swrap"><div class="sbar" id="sb"></div></div>
          <div id="st" style="font-size:.68rem;color:#7a9590;margin-top:2px"></div>
        </div>
        <div class="col-md-6">
          <label class="form-label">Xác nhận mật khẩu <span class="text-danger">*</span></label>
          <div class="input-group">
            <input type="password" name="confirm_password" id="p2" class="form-control" placeholder="Nhập lại" required>
            <button type="button" class="eye-btn" onclick="toggle('p2','e2')"><i class="bi bi-eye" id="e2"></i></button>
          </div>
        </div>
      </div>
      <div id="sf" style="display:none" class="mt-3">
        <div style="font-size:.76rem;font-weight:700;color:var(--ds);margin-bottom:8px;display:flex;align-items:center;gap:6px"><i class="bi bi-mortarboard-fill"></i>Thông tin học vấn (điền ngay hoặc bổ sung sau)</div>
        <div class="row g-2">
          <div class="col-6"><label class="form-label">Mã sinh viên</label><input type="text" name="student_code" class="form-control" placeholder="SV2024001" value="<?=htmlspecialchars($_POST['student_code']??'')?>"></div>
          <div class="col-6"><label class="form-label">Chuyên ngành</label><input type="text" name="major" class="form-control" placeholder="CNTT" value="<?=htmlspecialchars($_POST['major']??'')?>"></div>
        </div>
      </div>
      <div class="mt-4"><button type="submit" class="btn-reg"><i class="bi bi-person-plus-fill me-2"></i>Tạo tài khoản</button></div>
    </form>
    <div class="divl">hoặc</div>
    <div class="text-center" style="font-size:.82rem;color:#7a9590">
      Đã có tài khoản? <a href="login.php" style="color:var(--ds);font-weight:700;text-decoration:none">Đăng nhập ngay</a>
    </div>
    <?php endif; ?>
  </div>
</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
function toggle(id,ico){const i=document.getElementById(id),e=document.getElementById(ico);i.type=i.type==='password'?'text':'password';e.className=i.type==='text'?'bi bi-eye-slash':'bi bi-eye';}
function strength(v){const b=document.getElementById('sb'),t=document.getElementById('st');let s=0;if(v.length>=6)s++;if(v.length>=10)s++;if(/[A-Z]/.test(v))s++;if(/\d/.test(v))s++;if(/[^A-Za-z0-9]/.test(v))s++;const l=[{w:'0%',c:'transparent',t:''},{w:'25%',c:'#c06050',t:'Yếu'},{w:'50%',c:'#c49a6c',t:'Trung bình'},{w:'75%',c:'#4a9e6a',t:'Khá'},{w:'100%',c:'#2d7a50',t:'Mạnh'}];b.style.width=l[Math.min(s,4)].w;b.style.background=l[Math.min(s,4)].c;t.textContent=l[Math.min(s,4)].t;t.style.color=l[Math.min(s,4)].c;}
function updateRole(){const r=document.querySelector('input[name=role]:checked')?.value;document.getElementById('sf').style.display=r==='student'?'block':'none';document.getElementById('fn-label').firstChild.textContent=r==='company'?'Tên công ty':'Họ và tên';}
document.querySelectorAll('input[name=role]').forEach(r=>r.addEventListener('change',updateRole));updateRole();
document.getElementById('rf').addEventListener('submit',function(e){const p1=document.getElementById('p1').value,p2=document.getElementById('p2').value;if(p1!==p2){e.preventDefault();alert('Mật khẩu xác nhận không khớp!');}if(!document.querySelector('input[name=role]:checked')){e.preventDefault();alert('Vui lòng chọn loại tài khoản!');}});
</script>
</body></html>
