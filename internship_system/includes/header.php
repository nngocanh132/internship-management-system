<?php
$cur   = $_SERVER['REQUEST_URI'];
$base  = BASE_PATH;
$role  = getRole();
$uid   = $_SESSION['user_id'] ?? 0;

if(!function_exists('_isActive')){
    function _isActive($p){ global $cur; return (strpos($cur,$p)!==false)?'active':''; }
}

$page_title = 'Dashboard';
$title_map  = ['profile'=>'Hồ sơ','internship'=>'Vị trí Thực tập','application'=>'Đơn ứng tuyển',
               'message'=>'Tin nhắn','interview'=>'Phỏng vấn','registration'=>'Quản lý Thực tập',
               'evaluation'=>'Đánh giá','report'=>'Báo cáo','assignment'=>'Phân công GVHD',
               'user'=>'Người dùng','company'=>'Doanh nghiệp','lecturer'=>'Giảng viên',
               'admin'=>'Dashboard','student'=>'Dashboard','dashboard'=>'Dashboard'];
foreach($title_map as $k=>$v)
    if(str_contains(strtolower($cur),$k)){ $page_title=$v; break; }

// Unread messages (chỉ student/company)
$unread = 0;
if(($role==='student'||$role==='company') && isset($conn))
    $unread = getUnreadCount($conn,$uid);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?=htmlspecialchars($page_title)?> — ISchool Internship</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@700;800&display=swap" rel="stylesheet">
<style>
:root{
  --ds:#5D7B6F; --ds2:#3D5A50; --ds3:#2A3F38;
  --sg:#A4C3A2; --sm:#B0D4B8; --wc:#EAE7D6; --ib:#D7F9FA;
  --td:#1A2E28; --tm:#4A6058; --tl:#7A9590;
  --r8:8px; --r12:12px; --r16:16px; --r20:20px;
  --sw:260px;
}
*,*::before,*::after{box-sizing:border-box}
body{font-family:'Inter',sans-serif;background:#eef5f2;color:var(--td);margin:0}
::-webkit-scrollbar{width:4px}::-webkit-scrollbar-thumb{background:var(--sg);border-radius:4px}

/* ── SIDEBAR ── */
.sidebar{
  width:var(--sw);height:100vh;
  background:linear-gradient(180deg,var(--ds3) 0%,var(--ds2) 50%,#3a6258 100%);
  position:fixed;top:0;left:0;z-index:999;
  display:flex;flex-direction:column;
  box-shadow:4px 0 20px rgba(0,0,0,.2);
}
.sb-top{overflow-y:auto;flex:1;padding-bottom:8px}
.sb-brand{padding:20px 16px 16px;border-bottom:1px solid rgba(255,255,255,.1);
  display:flex;align-items:center;gap:11px;flex-shrink:0}
.sb-logo{width:40px;height:40px;background:linear-gradient(135deg,var(--sm),var(--sg));
  border-radius:10px;display:flex;align-items:center;justify-content:center;
  font-size:1.2rem;color:#fff;flex-shrink:0}
.sb-title{font-family:'Plus Jakarta Sans',sans-serif;font-size:.88rem;font-weight:800;color:#fff}
.sb-sub{font-size:.62rem;color:var(--sm);opacity:.75}
.nav-sec{padding:12px 10px 2px}
.nav-label{font-size:.58rem;font-weight:700;text-transform:uppercase;letter-spacing:1.5px;
  color:var(--sm);opacity:.5;padding:0 8px;margin-bottom:3px}
.nav-a{display:flex;align-items:center;gap:9px;padding:8px 11px;border-radius:9px;
  color:rgba(255,255,255,.62);text-decoration:none;font-size:.825rem;font-weight:500;
  transition:all .16s;margin-bottom:1px;position:relative}
.nav-a i{font-size:.9rem;width:16px;text-align:center;flex-shrink:0}
.nav-a:hover{background:rgba(255,255,255,.1);color:#fff;transform:translateX(3px)}
.nav-a.active{background:rgba(176,212,184,.2);color:#fff;
  border-left:3px solid var(--sm);padding-left:8px}
.msg-badge{background:var(--sm);color:var(--ds3);font-size:.6rem;padding:1px 6px;
  border-radius:10px;font-weight:700;margin-left:auto;flex-shrink:0}

/* ── SIDEBAR FOOTER (always visible) ── */
.sb-footer{
  padding:12px;border-top:1px solid rgba(255,255,255,.1);
  background:rgba(42,63,56,.98);flex-shrink:0;
}
.sb-user{display:flex;align-items:center;gap:9px;padding:9px;
  border-radius:9px;background:rgba(255,255,255,.07);margin-bottom:8px}
.sb-avatar{width:32px;height:32px;border-radius:8px;
  background:linear-gradient(135deg,var(--sm),var(--ib));
  display:flex;align-items:center;justify-content:center;
  color:var(--ds3);font-size:.82rem;font-weight:800;flex-shrink:0}
.sb-uname{font-size:.78rem;font-weight:600;color:#fff;
  white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.sb-urole{font-size:.64rem;color:var(--sm);opacity:.8}
.logout-a{display:flex;align-items:center;justify-content:center;gap:8px;
  padding:9px 14px;border-radius:9px;
  background:rgba(192,96,80,.25);border:1px solid rgba(192,96,80,.4);
  color:#ffb3a7;text-decoration:none;font-size:.82rem;font-weight:700;
  transition:all .2s;width:100%}
.logout-a:hover{background:rgba(192,96,80,.45);color:#fff;border-color:rgba(192,96,80,.7)}

/* ── TOPBAR ── */
.topbar{height:60px;background:rgba(255,255,255,.96);backdrop-filter:blur(10px);
  border-bottom:1px solid rgba(164,195,162,.2);display:flex;align-items:center;
  padding:0 26px;position:sticky;top:0;z-index:900;
  box-shadow:0 2px 10px rgba(93,123,111,.07)}
.tb-title{font-family:'Plus Jakarta Sans',sans-serif;font-size:1rem;font-weight:800;color:var(--td)}
.tb-sub{font-size:.7rem;color:var(--tl)}
.tb-avatar{width:34px;height:34px;border-radius:9px;
  background:linear-gradient(135deg,var(--ds),var(--sg));
  display:flex;align-items:center;justify-content:center;
  color:#fff;font-size:.85rem;font-weight:800;flex-shrink:0}

/* ── LAYOUT ── */
.main-wrap{margin-left:var(--sw);min-height:100vh;display:flex;flex-direction:column}
.main-content{padding:26px 28px;flex:1}

/* ── CARDS ── */
.card{border:1px solid rgba(164,195,162,.18);border-radius:var(--r16);
  background:#fff;box-shadow:0 2px 8px rgba(93,123,111,.08);transition:box-shadow .2s}
.card:hover{box-shadow:0 4px 16px rgba(93,123,111,.13)}
.card-body{padding:20px}

/* ── STAT CARDS ── */
.stat-card{border-radius:var(--r16);padding:20px 22px;position:relative;overflow:hidden;
  transition:transform .2s,box-shadow .2s;border:none}
.stat-card:hover{transform:translateY(-3px);box-shadow:0 8px 28px rgba(0,0,0,.15)}
.stat-card .s-bg{position:absolute;right:-12px;bottom:-12px;font-size:4.5rem;opacity:.1}
.stat-card .s-icon{width:42px;height:42px;border-radius:11px;display:flex;align-items:center;
  justify-content:center;font-size:1.2rem;background:rgba(255,255,255,.2);color:#fff;margin-bottom:10px}
.stat-card .s-num{font-family:'Plus Jakarta Sans',sans-serif;font-size:2.2rem;font-weight:800;color:#fff;line-height:1;margin-bottom:3px}
.stat-card .s-lbl{font-size:.73rem;font-weight:600;color:rgba(255,255,255,.82);text-transform:uppercase;letter-spacing:.5px}
.stat-card .s-link{display:inline-flex;align-items:center;gap:4px;color:rgba(255,255,255,.7);
  font-size:.72rem;text-decoration:none;margin-top:8px;transition:all .2s}
.stat-card .s-link:hover{color:#fff;gap:7px}
.sc-green{background:linear-gradient(135deg,var(--ds),var(--ds2))}
.sc-mint {background:linear-gradient(135deg,#4a9e6a,#2d7a50)}
.sc-sage {background:linear-gradient(135deg,var(--sg),#7aab78)}
.sc-warm {background:linear-gradient(135deg,#c49a6c,#a07040)}
.sc-teal {background:linear-gradient(135deg,#4ab8c4,#2a95a2)}
.sc-red  {background:linear-gradient(135deg,#c06050,#9a3030)}

/* ── TABLE ── */
.tc .card-header{background:linear-gradient(90deg,rgba(234,231,214,.4),rgba(255,255,255,.8));
  border-bottom:2px solid rgba(164,195,162,.2);padding:12px 18px;
  border-radius:var(--r16) var(--r16) 0 0!important;
  font-family:'Plus Jakarta Sans',sans-serif;font-weight:700;font-size:.85rem;color:var(--td)}
.table thead th{background:rgba(164,195,162,.1);color:var(--tl);font-size:.67rem;font-weight:700;
  text-transform:uppercase;letter-spacing:.8px;border-bottom:2px solid rgba(164,195,162,.18);
  padding:10px 14px;white-space:nowrap}
.table tbody td{padding:10px 14px;vertical-align:middle;font-size:.85rem;
  border-bottom:1px solid rgba(164,195,162,.1);color:var(--td)}
.table tbody tr:hover td{background:rgba(176,212,184,.06)}
.table tbody tr:last-child td{border-bottom:none}

/* ── BUTTONS ── */
.btn{border-radius:var(--r8);font-weight:600;font-size:.83rem;transition:all .18s;border:none}
.btn-primary{background:linear-gradient(135deg,var(--ds),var(--ds2));color:#fff}
.btn-primary:hover{background:linear-gradient(135deg,var(--ds2),var(--ds3));color:#fff;transform:translateY(-1px)}
.btn-success{background:linear-gradient(135deg,#4a9e6a,#2d7a50);color:#fff}
.btn-warning{background:linear-gradient(135deg,#c49a6c,#a07040);color:#fff}
.btn-warning:hover{background:linear-gradient(135deg,#a07040,#7a5020);color:#fff}
.btn-danger{background:linear-gradient(135deg,#c06050,#9a3030);color:#fff}
.btn-danger:hover{background:linear-gradient(135deg,#9a3030,#7a2020);color:#fff}
.btn-secondary{background:rgba(160,160,160,.15);color:var(--tm);border:1px solid rgba(164,195,162,.3)!important}
.btn-secondary:hover{background:var(--wc);color:var(--td)}
.btn-outline-secondary{border:1.5px solid rgba(164,195,162,.35)!important;color:var(--tm);background:rgba(255,255,255,.8)}
.btn-outline-secondary:hover{background:var(--wc);color:var(--td);border-color:var(--sg)!important}
.btn-sm{padding:4px 10px;font-size:.76rem}

/* ── BADGES ── */
.badge{border-radius:5px;font-weight:600;font-size:.69rem;padding:3px 8px}
.bg-primary{background:var(--ds)!important}
.bg-success{background:#3d8a58!important}
.bg-warning{background:#a07040!important}
.bg-danger{background:#9a3030!important}
.bg-secondary{background:var(--tl)!important}

/* ── FORMS ── */
.form-control,.form-select{border-radius:var(--r8);border:1.5px solid rgba(164,195,162,.32);
  font-size:.86rem;padding:9px 13px;background:rgba(255,255,255,.9);transition:all .2s}
.form-control:focus,.form-select:focus{border-color:var(--ds);
  box-shadow:0 0 0 3px rgba(93,123,111,.12);background:#fff;outline:none}
.form-label{font-weight:600;font-size:.78rem;color:var(--tm);margin-bottom:4px}
.input-group-text{border-radius:var(--r8) 0 0 var(--r8);
  border:1.5px solid rgba(164,195,162,.32);background:var(--wc);color:var(--tl);border-right:none}
.input-group .form-control{border-radius:0 var(--r8) var(--r8) 0;border-left-color:transparent}

/* ── PAGE HEADER ── */
.ph{display:flex;align-items:center;justify-content:space-between;
  margin-bottom:22px;padding-bottom:14px;border-bottom:2px solid rgba(164,195,162,.18)}
.ph h4{font-family:'Plus Jakarta Sans',sans-serif;font-size:1.25rem;font-weight:800;color:var(--td);margin:0}
.ph-sub{font-size:.74rem;color:var(--tl);margin-top:2px}

/* ── ALERTS ── */
.alert{border:none;border-radius:var(--r12);font-size:.85rem;padding:12px 16px}
.alert-success{background:rgba(74,158,106,.12);color:#1a4a30;border-left:4px solid #4a9e6a}
.alert-danger{background:rgba(192,96,80,.08);color:#7a2020;border-left:4px solid #c06050}
.alert-warning{background:rgba(234,231,214,.7);color:#5a4a20;border-left:4px solid #c49a6c}
.alert-info{background:rgba(74,138,150,.1);color:#1a3a4a;border-left:4px solid #4ab8c4}

/* ── QUICK ACTION ── */
.qa{border-radius:var(--r12);padding:12px 14px;display:flex;align-items:center;gap:12px;
  text-decoration:none;color:inherit;background:#fff;
  border:1.5px solid rgba(164,195,162,.22);transition:all .2s}
.qa:hover{border-color:var(--ds);box-shadow:0 4px 14px rgba(93,123,111,.14);
  transform:translateY(-2px);color:inherit}
.qa-icon{width:38px;height:38px;border-radius:9px;display:flex;align-items:center;
  justify-content:center;font-size:1.1rem;flex-shrink:0}
.qa-title{font-weight:700;font-size:.84rem;color:var(--td)}
.qa-sub{font-size:.71rem;color:var(--tl)}

/* ── AVATAR ── */
.av{width:32px;height:32px;border-radius:7px;
  background:linear-gradient(135deg,var(--ds),var(--sg));
  display:flex;align-items:center;justify-content:center;
  color:#fff;font-size:.78rem;font-weight:800;flex-shrink:0}

/* ── ANIMATIONS ── */
@keyframes fadeUp{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:none}}
.fu{animation:fadeUp .32s ease both}
.fu1{animation:fadeUp .32s .06s ease both}
.fu2{animation:fadeUp .32s .12s ease both}
.fu3{animation:fadeUp .32s .18s ease both}
.fu4{animation:fadeUp .32s .24s ease both}

/* ── MISC ── */
.fw7{font-weight:700!important}.fw8{font-weight:800!important}
hr{border-color:rgba(164,195,162,.22)!important}
@media(max-width:768px){.sidebar{transform:translateX(-100%)}.main-wrap{margin-left:0}.main-content{padding:14px}}
</style>
</head>
<body>

<div class="sidebar">
  <!-- Scrollable top -->
  <div class="sb-top">
    <div class="sb-brand">
      <div class="sb-logo"><i class="bi bi-mortarboard-fill"></i></div>
      <div>
        <div class="sb-title">ISchool Internship</div>
        <div class="sb-sub">Management System</div>
      </div>
    </div>

    <?php if($role==='admin'): ?>
    <div class="nav-sec">
      <div class="nav-label">Tổng quan</div>
      <a href="<?=$base?>/dashboard/admin.php"   class="nav-a <?=_isActive('/dashboard/admin')?>"><i class="bi bi-speedometer2"></i> Dashboard</a>
    </div>
    <div class="nav-sec">
      <div class="nav-label">Quản lý hệ thống</div>
      <a href="<?=$base?>/modules/users/list.php"                class="nav-a <?=_isActive('/users/')?>"><i class="bi bi-people-fill"></i> Người dùng</a>
      <a href="<?=$base?>/modules/student_profiles/list.php"     class="nav-a <?=_isActive('/student_profiles/')?>"><i class="bi bi-mortarboard-fill"></i> Hồ sơ Sinh viên</a>
      <a href="<?=$base?>/modules/company_profiles/list.php"     class="nav-a <?=_isActive('/company_profiles/')?>"><i class="bi bi-building-fill"></i> Hồ sơ DN</a>
      <a href="<?=$base?>/modules/lecturer_profiles/list.php"    class="nav-a <?=_isActive('/lecturer_profiles/')?>"><i class="bi bi-person-workspace"></i> Giảng viên</a>
    </div>
    <div class="nav-sec">
      <div class="nav-label">Thực tập</div>
      <a href="<?=$base?>/modules/internships/list.php"          class="nav-a <?=_isActive('/internships/')?>"><i class="bi bi-briefcase-fill"></i> Vị trí thực tập</a>
      <a href="<?=$base?>/modules/applications/list.php"         class="nav-a <?=_isActive('/applications/')?>"><i class="bi bi-clipboard-check-fill"></i> Xét duyệt đơn</a>
      <a href="<?=$base?>/modules/registrations/list.php"        class="nav-a <?=_isActive('/registrations/list')?>"><i class="bi bi-journal-richtext"></i> Đang thực tập</a>
      <a href="<?=$base?>/modules/registrations/assign.php" class="nav-a <?=_isActive('/registrations/assign')?>"><i class="bi bi-person-check-fill"></i> Phân công GVHD</a>
    </div>
    <div class="nav-sec">
      <div class="nav-label">Kết quả</div>
      <a href="<?=$base?>/modules/evaluations/list.php"          class="nav-a <?=_isActive('/evaluations/')?>"><i class="bi bi-star-fill"></i> Đánh giá</a>
      <a href="<?=$base?>/modules/reports/list.php"              class="nav-a <?=_isActive('/reports/')?>"><i class="bi bi-file-earmark-text-fill"></i> Báo cáo TT</a>
    </div>

    <?php elseif($role==='student'): ?>
    <div class="nav-sec">
      <div class="nav-label">Sinh viên</div>
      <a href="<?=$base?>/dashboard/student.php" class="nav-a <?=_isActive('/dashboard/student')?>"><i class="bi bi-speedometer2"></i> Dashboard</a>
      <a href="<?=$base?>/modules/student_profiles/edit.php" class="nav-a <?=_isActive('/student_profiles/edit')?>"><i class="bi bi-person-circle"></i> Hồ sơ của tôi</a>
      <a href="<?=$base?>/modules/internships/browse.php" class="nav-a <?=_isActive('/internships/browse')?>"><i class="bi bi-search"></i> Tìm việc thực tập</a>
      <a href="<?=$base?>/modules/applications/my_applications.php" class="nav-a <?=_isActive('/applications/my')?>"><i class="bi bi-clipboard-check-fill"></i> Đơn ứng tuyển</a>
      <a href="<?=$base?>/modules/registrations/my_internship.php" class="nav-a <?=_isActive('/registrations/my')?>"><i class="bi bi-briefcase-fill"></i> Thực tập của tôi</a>
      <a href="<?=$base?>/modules/reports/submit.php" class="nav-a <?=_isActive('/reports/submit')?>"><i class="bi bi-file-earmark-arrow-up-fill"></i> Nộp báo cáo</a>
    </div>
    <div class="nav-sec">
      <div class="nav-label">Liên lạc</div>
      <a href="<?=$base?>/modules/messages/inbox.php" class="nav-a <?=_isActive('/messages/inbox')?>">
        <i class="bi bi-chat-dots-fill"></i> Tin nhắn với DN
        <?php if($unread>0): ?><span class="msg-badge"><?=$unread?></span><?php endif; ?>
      </a>
      <a href="<?=$base?>/modules/messages/lecturer_chat.php" class="nav-a <?=_isActive('/messages/lecturer_chat')?>">
        <i class="bi bi-person-workspace"></i> Nhắn tin GVHD
      </a>
    </div>

    <?php elseif($role==='company'): ?>
    <div class="nav-sec">
      <div class="nav-label">Doanh nghiệp</div>
      <a href="<?=$base?>/dashboard/company.php" class="nav-a <?=_isActive('/dashboard/company')?>"><i class="bi bi-speedometer2"></i> Dashboard</a>
      <a href="<?=$base?>/modules/company_profiles/edit.php" class="nav-a <?=_isActive('/company_profiles/edit')?>"><i class="bi bi-building-fill"></i> Hồ sơ DN</a>
      <a href="<?=$base?>/modules/internships/my_jobs.php" class="nav-a <?=_isActive('/internships/my_jobs')?>"><i class="bi bi-briefcase-fill"></i> Vị trí Thực tập</a>
      <a href="<?=$base?>/modules/applications/company_candidates.php" class="nav-a <?=_isActive('/applications/company_candidates')?>"><i class="bi bi-people-fill"></i> Danh sách Ứng viên</a>
      <a href="<?=$base?>/modules/evaluations/add.php" class="nav-a <?=_isActive('/evaluations/')?>"><i class="bi bi-star-fill"></i> Đánh giá SV</a>
    </div>
    <div class="nav-sec">
      <div class="nav-label">Liên lạc</div>
      <a href="<?=$base?>/modules/messages/inbox.php" class="nav-a <?=_isActive('/messages/')?>">
        <i class="bi bi-chat-dots-fill"></i> Tin nhắn
        <?php if($unread>0): ?><span class="msg-badge"><?=$unread?></span><?php endif; ?>
      </a>
    </div>

    <?php elseif($role==='lecturer'): ?>
    <div class="nav-sec">
      <div class="nav-label">Giảng viên</div>
      <a href="<?=$base?>/dashboard/lecturer.php" class="nav-a <?=_isActive('/dashboard/lecturer')?>"><i class="bi bi-speedometer2"></i> Dashboard</a>
      <a href="<?=$base?>/modules/registrations/my_students.php" class="nav-a <?=_isActive('/registrations/my_students')?>"><i class="bi bi-people-fill"></i> SV được phân công</a>
      <a href="<?=$base?>/modules/reports/review.php" class="nav-a <?=_isActive('/reports/review')?>"><i class="bi bi-file-earmark-check-fill"></i> Duyệt báo cáo</a>
    </div>
    <div class="nav-sec">
      <div class="nav-label">Liên lạc</div>
      <a href="<?=$base?>/modules/messages/lecturer_chat.php" class="nav-a <?=_isActive('/messages/lecturer_chat')?>">
        <i class="bi bi-chat-dots-fill"></i> Nhắn tin với SV
        <?php if(($role==='lecturer')&&isset($conn)){$_uc=getUnreadCount($conn,$uid);if($_uc>0):?><span class="msg-badge"><?=$_uc?></span><?php endif;}?>
      </a>
    </div>
    <?php endif; ?>
  </div><!-- /sb-top -->

  <!-- Always-visible footer -->
  <div class="sb-footer">
    <div class="sb-user">
      <div class="sb-avatar"><?=strtoupper(mb_substr($_SESSION['full_name']??'U',0,1))?></div>
      <div style="flex:1;min-width:0">
        <div class="sb-uname"><?=htmlspecialchars($_SESSION['full_name']??'Tài khoản')?></div>
        <div class="sb-urole"><?=getRoleLabel($role)?></div>
      </div>
    </div>
    <a href="<?=$base?>/auth/logout.php" class="logout-a">
      <i class="bi bi-box-arrow-right"></i> Đăng xuất
    </a>
  </div>
</div>

<div class="main-wrap">
  <div class="topbar">
    <div>
      <div class="tb-title"><?=htmlspecialchars($page_title)?></div>
      <div class="tb-sub">ISchool Internship System</div>
    </div>
    <div class="ms-auto d-flex align-items-center gap-3">
      <div style="background:rgba(164,195,162,.15);padding:5px 11px;border-radius:7px;font-size:.75rem;color:var(--ds);font-weight:600">
        <i class="bi bi-calendar3 me-1"></i><?=date('d/m/Y')?>
      </div>
      <div class="d-flex align-items-center gap-2">
        <div class="tb-avatar"><?=strtoupper(mb_substr($_SESSION['full_name']??'U',0,1))?></div>
        <div>
          <div style="font-size:.82rem;font-weight:700;color:var(--td);line-height:1.2"><?=htmlspecialchars($_SESSION['full_name']??'')?></div>
          <div style="font-size:.68rem;color:var(--tl)"><?=getRoleLabel($role)?></div>
        </div>
      </div>
    </div>
  </div>
  <div class="main-content">
