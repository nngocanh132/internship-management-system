<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ISchool Internship</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,300;0,14..32,400;0,14..32,500;0,14..32,600;0,14..32,700;0,14..32,800;1,14..32,400&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg:        #E8E8DC;
            --sidebar:   #4A6741;
            --sid-dark:  #3a5232;
            --sid-text:  rgba(255,255,255,.75);
            --sid-active:#C8DFC4;
            --card-bg:   #FFFFFF;
            --mint:      #A8D5BA;
            --mint-lt:   #D4EDE0;
            --teal:      #7EC8C8;
            --teal-lt:   #C8ECEC;
            --accent:    #5A8A5A;
            --text:      #2C3A2C;
            --muted:     #7A8C7A;
            --border:    rgba(74,103,65,.15);
        }
        *,*::before,*::after{box-sizing:border-box;}
        body{
            font-family:'Inter',system-ui,sans-serif;
            background:var(--bg);
            color:var(--text);
            margin:0;
            -webkit-font-smoothing:antialiased;
        }

        /* ── SIDEBAR ── */
        .sidebar{
            width:220px; min-height:100vh;
            background:var(--sidebar);
            position:fixed; top:0; left:0;
            display:flex; flex-direction:column;
            z-index:1000;
        }
        .sidebar-brand{
            padding:28px 24px 20px;
            font-size:1.25rem; font-weight:800;
            color:#fff; letter-spacing:-.3px;
        }
        .sidebar-brand span{ color:var(--mint); }
        .nav-section{ padding:6px 12px; }
        .nav-section-label{
            font-size:.62rem; font-weight:600;
            text-transform:uppercase; letter-spacing:1px;
            color:rgba(255,255,255,.35);
            padding:8px 12px 4px;
        }
        .sidebar-link{
            display:flex; align-items:center; gap:10px;
            padding:10px 14px; border-radius:10px;
            color:var(--sid-text); text-decoration:none;
            font-size:.875rem; font-weight:500;
            transition:all .15s; margin-bottom:2px;
        }
        .sidebar-link i{ font-size:.95rem; width:18px; text-align:center; flex-shrink:0; }
        .sidebar-link:hover{ background:rgba(255,255,255,.12); color:#fff; }
        .sidebar-link.active{
            background:rgba(200,223,196,.2);
            color:var(--sid-active);
            font-weight:600;
        }
        .sidebar-user{
            margin:auto 16px 16px;
            padding:12px 14px;
            background:rgba(255,255,255,.1);
            border-radius:12px;
        }
        .sidebar-user .u-name{ font-size:.82rem; font-weight:700; color:#fff; }
        .sidebar-user .u-role{ font-size:.7rem; color:rgba(255,255,255,.55); margin-top:1px; }
        .sidebar-footer{
            padding:0 12px 16px;
        }
        .sidebar-footer a{
            display:flex; align-items:center; gap:8px;
            padding:9px 14px; border-radius:10px;
            color:rgba(255,255,255,.5); text-decoration:none;
            font-size:.82rem; font-weight:500; transition:all .15s;
        }
        .sidebar-footer a:hover{ background:rgba(255,255,255,.08); color:rgba(255,255,255,.85); }
        .sidebar-footer .logout-link{ color:rgba(168,213,186,.7); }
        .sidebar-footer .logout-link:hover{ background:rgba(168,213,186,.12); color:var(--mint); }

        /* ── TOPBAR ── */
        .topbar{
            height:64px; background:var(--bg);
            display:flex; align-items:center;
            padding:0 32px;
            position:sticky; top:0; z-index:900;
            border-bottom:1px solid var(--border);
        }
        .topbar-title{ font-size:1.5rem; font-weight:800; color:var(--text); letter-spacing:-.4px; }
        .topbar-sub{ font-size:.82rem; color:var(--muted); margin-top:1px; }

        /* ── LAYOUT ── */
        .main-wrapper{ margin-left:220px; min-height:100vh; display:flex; flex-direction:column; }
        .main-content{ padding:28px 32px; flex:1; }

        /* ── CARDS ── */
        .card{
            border:none; border-radius:20px;
            background:var(--card-bg);
            box-shadow:0 2px 8px rgba(44,58,44,.06);
            transition:box-shadow .2s,transform .2s;
        }
        .card:hover{ box-shadow:0 6px 20px rgba(44,58,44,.1); }

        /* Stat cards */
        .stat-card{
            background:var(--card-bg); border-radius:20px;
            padding:24px 26px; position:relative;
            transition:transform .2s,box-shadow .2s;
            cursor:default;
        }
        .stat-card:hover{ transform:translateY(-2px); box-shadow:0 8px 24px rgba(44,58,44,.1); }
        .stat-card .stat-label{ font-size:.8rem; font-weight:600; color:var(--muted); margin-bottom:8px; }
        .stat-card .stat-number{ font-size:2.4rem; font-weight:800; color:var(--text); line-height:1; margin-bottom:10px; }
        .stat-card .stat-badge{
            display:inline-block; padding:4px 12px;
            border-radius:20px; font-size:.75rem; font-weight:600;
        }
        .stat-card .stat-link{
            display:inline-flex; align-items:center; gap:4px;
            font-size:.75rem; font-weight:600; text-decoration:none;
            color:var(--accent); margin-top:10px;
        }
        .stat-card .stat-link:hover{ color:var(--sidebar); }
        /* badge colors */
        .badge-mint  { background:var(--mint-lt);  color:#2d6a4f; }
        .badge-teal  { background:var(--teal-lt);  color:#1a6b6b; }
        .badge-sage  { background:#e8f0e8; color:var(--accent); }
        .badge-peach { background:#fde8d8; color:#b05a2a; }
        .badge-lav   { background:#ede9fe; color:#5b21b6; }
        /* keep old stat-* for index.php */
        .stat-ev,.stat-terra,.stat-sage,.stat-blush,.stat-green,.stat-blue,.stat-purple,.stat-orange,.stat-mint,.stat-ice{
            background:var(--card-bg);
        }
        .stat-ev .stat-number,.stat-terra .stat-number,.stat-sage .stat-number,
        .stat-blush .stat-number,.stat-green .stat-number,.stat-blue .stat-number,
        .stat-purple .stat-number,.stat-orange .stat-number,.stat-mint .stat-number,
        .stat-ice .stat-number{ color:var(--text); }
        .stat-ev .stat-link,.stat-terra .stat-link,.stat-sage .stat-link,
        .stat-blush .stat-link,.stat-green .stat-link,.stat-blue .stat-link,
        .stat-purple .stat-link,.stat-orange .stat-link,.stat-mint .stat-link,
        .stat-ice .stat-link{ color:var(--accent); }
        .stat-card .stat-icon{ display:none; }

        /* ── TABLE ── */
        .table-card .card-header{
            background:var(--card-bg);
            border-bottom:1px solid var(--border);
            padding:18px 24px;
            border-radius:20px 20px 0 0 !important;
            font-weight:700; font-size:.95rem; color:var(--text);
        }
        .table thead th{
            background:#f4f6f2;
            color:var(--muted);
            font-size:.72rem; font-weight:700;
            text-transform:uppercase; letter-spacing:.7px;
            border-bottom:1px solid var(--border);
            padding:12px 18px; white-space:nowrap;
        }
        .table tbody td{
            padding:13px 18px; vertical-align:middle;
            font-size:.875rem;
            border-bottom:1px solid rgba(44,58,44,.05);
            color:var(--text);
        }
        .table tbody tr:hover td{ background:rgba(168,213,186,.08); }
        .table tbody tr:last-child td{ border-bottom:none; }

        /* ── BUTTONS ── */
        .btn{ border-radius:10px; font-weight:600; font-size:.855rem; }
        .btn-primary{ background:var(--sidebar); border:none; color:#fff; }
        .btn-primary:hover{ background:var(--sid-dark); color:#fff; }
        .btn-success{ background:#3a8a5a; border:none; color:#fff; }
        .btn-warning{ background:#d4a843; border:none; color:#fff; }
        .btn-warning:hover{ color:#fff; }
        .btn-danger{ background:#d45a5a; border:none; color:#fff; }
        .btn-secondary{ background:var(--mint-lt); border:none; color:var(--accent); }
        .btn-secondary:hover{ background:var(--mint); color:var(--sid-dark); }
        .btn-sm{ padding:5px 13px; font-size:.8rem; border-radius:8px; }

        /* ── BADGES ── */
        .badge{ border-radius:8px; font-weight:600; font-size:.72rem; padding:4px 10px; }

        /* ── FORMS ── */
        .form-control,.form-select{
            border-radius:10px;
            border:1.5px solid var(--border);
            font-size:.875rem; padding:9px 14px;
            background:#fafafa;
            font-family:'Inter',system-ui,sans-serif;
            transition:border-color .2s,box-shadow .2s;
        }
        .form-control:focus,.form-select:focus{
            border-color:var(--accent);
            box-shadow:0 0 0 3px rgba(90,138,90,.12);
            background:#fff; outline:none;
        }
        .form-label{ font-weight:600; font-size:.82rem; color:var(--text); margin-bottom:6px; }

        /* ── PAGE HEADER ── */
        .page-header{
            display:flex; align-items:flex-start;
            justify-content:space-between; margin-bottom:24px;
        }
        .page-header h4{ font-size:1.5rem; font-weight:800; color:var(--text); margin:0; letter-spacing:-.3px; }
        .page-header .page-subtitle{ font-size:.82rem; color:var(--muted); margin-top:3px; }

        /* ── ALERTS ── */
        .alert{ border:none; border-radius:14px; font-size:.875rem; padding:13px 18px; }
        .alert-warning{ background:#fef9e7; color:#7c5c00; border-left:4px solid #f0c040; }
        .alert-danger { background:#fef2f2; color:#991b1b; border-left:4px solid #f87171; }
        .alert-success{ background:#f0fdf4; color:#166534; border-left:4px solid #4ade80; }
        .alert-info   { background:var(--teal-lt); color:#1a6b6b; border-left:4px solid var(--teal); }

        /* ── PROGRESS ── */
        .progress{ border-radius:99px; background:var(--mint-lt); height:8px; }
        .progress-bar{ border-radius:99px; background:var(--sidebar); }

        /* ── MISC ── */
        .avatar-sm{
            width:36px; height:36px; border-radius:10px;
            display:inline-flex; align-items:center; justify-content:center;
            font-size:.82rem; font-weight:700; flex-shrink:0;
        }
        .section-divider{ height:1px; background:var(--border); margin:20px 0; }
        .quick-action-card{
            border-radius:16px; padding:16px 18px;
            display:flex; align-items:center; gap:14px;
            text-decoration:none; color:inherit;
            background:var(--card-bg);
            border:1.5px solid var(--border);
            transition:all .2s;
        }
        .quick-action-card:hover{
            border-color:var(--accent);
            box-shadow:0 4px 16px rgba(44,58,44,.1);
            transform:translateY(-2px); color:inherit;
        }
        .quick-action-card .qa-icon{
            width:42px; height:42px; border-radius:12px;
            display:flex; align-items:center; justify-content:center;
            font-size:1.2rem; flex-shrink:0;
        }
        .quick-action-card .qa-title{ font-weight:700; font-size:.875rem; color:var(--text); }
        .quick-action-card .qa-sub  { font-size:.75rem; color:var(--muted); }

        /* ── SCROLLBAR ── */
        ::-webkit-scrollbar{ width:5px; }
        ::-webkit-scrollbar-track{ background:transparent; }
        ::-webkit-scrollbar-thumb{ background:var(--mint); border-radius:99px; }
        ::-webkit-scrollbar-thumb:hover{ background:var(--accent); }
    </style>
</head>
<body>

<?php
$current_page = $_SERVER['REQUEST_URI'];
function isActive($path) {
    global $current_page;
    return strpos($current_page, $path) !== false ? 'active' : '';
}
$script_url = str_replace('\\', '/', $_SERVER['SCRIPT_NAME']);
$pos = strpos($script_url, '/internship_system');
$sys = ($pos !== false) ? substr($script_url, 0, $pos) . '/internship_system' : '/internship_system';
$role      = $_SESSION['role']      ?? 'guest';
$full_name = $_SESSION['full_name'] ?? 'Khách';
$initials  = strtoupper(mb_substr($full_name, 0, 1));
$role_labels = ['admin'=>'Quản trị viên','lecturer'=>'Giảng viên','company'=>'Doanh nghiệp','student'=>'Sinh viên'];
$role_label  = $role_labels[$role] ?? 'Người dùng';
?>

<div class="sidebar">
    <div class="sidebar-brand">ISCHOOL<span>.</span></div>

    <?php if ($role === 'admin' || $role === 'lecturer'): ?>
    <div class="nav-section">
        <div class="nav-section-label">Tổng quan</div>
        <a href="<?= $sys ?>/index.php" class="sidebar-link <?= isActive('/index.php') && !isActive('/modules/') ? 'active' : '' ?>">
            <i class="bi bi-grid-fill"></i> Dashboard
        </a>
    </div>
    <div class="nav-section">
        <div class="nav-section-label">Người dùng</div>
        <a href="<?= $sys ?>/modules/users/list.php" class="sidebar-link <?= isActive('/users/') ?>">
            <i class="bi bi-people-fill"></i> Người dùng
        </a>
        <a href="<?= $sys ?>/modules/departments/list.php" class="sidebar-link <?= isActive('/departments/') ?>">
            <i class="bi bi-diagram-3-fill"></i> Khoa / Bộ môn
        </a>
        <a href="<?= $sys ?>/modules/companies/list.php" class="sidebar-link <?= isActive('/companies/') ?>">
            <i class="bi bi-building-fill"></i> Doanh nghiệp
        </a>
    </div>
    <div class="nav-section">
        <div class="nav-section-label">Thực tập</div>
        <a href="<?= $sys ?>/modules/positions/list.php" class="sidebar-link <?= isActive('/positions/') && !isActive('/position_skills/') ? 'active' : '' ?>">
            <i class="bi bi-briefcase-fill"></i> Vị trí thực tập
        </a>
        <a href="<?= $sys ?>/modules/skills/list.php" class="sidebar-link <?= isActive('/skills/') && !isActive('/position_skills/') ? 'active' : '' ?>">
            <i class="bi bi-lightning-charge-fill"></i> Kỹ năng
        </a>
        <a href="<?= $sys ?>/modules/position_skills/list.php" class="sidebar-link <?= isActive('/position_skills/') ?>">
            <i class="bi bi-tags-fill"></i> Kỹ năng – Vị trí
        </a>
        <a href="<?= $sys ?>/modules/registrations/list.php" class="sidebar-link <?= isActive('/registrations/') ?>">
            <i class="bi bi-clipboard-check-fill"></i> Đăng ký
        </a>
        <a href="<?= $sys ?>/modules/assignments/list.php" class="sidebar-link <?= isActive('/assignments/') ?>">
            <i class="bi bi-person-check-fill"></i> Phân công GVHD
        </a>
    </div>
    <div class="nav-section">
        <div class="nav-section-label">Đánh giá</div>
        <a href="<?= $sys ?>/modules/journals/list.php" class="sidebar-link <?= isActive('/journals/') ?>">
            <i class="bi bi-journal-richtext"></i> Nhật ký
        </a>
        <a href="<?= $sys ?>/modules/company_eval/list.php" class="sidebar-link <?= isActive('/company_eval/') ?>">
            <i class="bi bi-star-fill"></i> Đánh giá DN
        </a>
        <a href="<?= $sys ?>/modules/lecturer_eval/list.php" class="sidebar-link <?= isActive('/lecturer_eval/') ?>">
            <i class="bi bi-patch-check-fill"></i> Đánh giá GV
        </a>
        <a href="<?= $sys ?>/modules/grades/list.php" class="sidebar-link <?= isActive('/grades/') ?>">
            <i class="bi bi-award-fill"></i> Điểm tổng hợp
        </a>
    </div>

    <?php elseif ($role === 'company'): ?>
    <div class="nav-section">
        <a href="<?= $sys ?>/index.php" class="sidebar-link <?= isActive('/index.php') && !isActive('/modules/') ? 'active' : '' ?>"><i class="bi bi-grid-fill"></i> Dashboard</a>
        <a href="<?= $sys ?>/modules/positions/list.php" class="sidebar-link <?= isActive('/positions/') ?>"><i class="bi bi-briefcase-fill"></i> Vị trí thực tập</a>
        <a href="<?= $sys ?>/modules/registrations/list.php" class="sidebar-link <?= isActive('/registrations/') ?>"><i class="bi bi-clipboard-check-fill"></i> Sinh viên đăng ký</a>
        <a href="<?= $sys ?>/modules/journals/list.php" class="sidebar-link <?= isActive('/journals/') ?>"><i class="bi bi-journal-richtext"></i> Nhật ký</a>
        <a href="<?= $sys ?>/modules/company_eval/list.php" class="sidebar-link <?= isActive('/company_eval/') ?>"><i class="bi bi-star-fill"></i> Đánh giá</a>
    </div>

    <?php elseif ($role === 'student'): ?>
    <div class="nav-section">
        <a href="<?= $sys ?>/index.php" class="sidebar-link <?= isActive('/index.php') && !isActive('/modules/') ? 'active' : '' ?>"><i class="bi bi-grid-fill"></i> Dashboard</a>
        <a href="<?= $sys ?>/modules/positions/list.php" class="sidebar-link <?= isActive('/positions/') ?>"><i class="bi bi-briefcase-fill"></i> Tìm vị trí</a>
        <a href="<?= $sys ?>/modules/registrations/list.php" class="sidebar-link <?= isActive('/registrations/') ?>"><i class="bi bi-clipboard-check-fill"></i> Đăng ký của tôi</a>
        <a href="<?= $sys ?>/modules/journals/list.php" class="sidebar-link <?= isActive('/journals/') ?>"><i class="bi bi-journal-richtext"></i> Nhật ký</a>
        <a href="<?= $sys ?>/modules/grades/list.php" class="sidebar-link <?= isActive('/grades/') ?>"><i class="bi bi-award-fill"></i> Điểm của tôi</a>
    </div>

    <?php else: ?>
    <div class="nav-section">
        <a href="<?= $sys ?>/auth/login.php" class="sidebar-link"><i class="bi bi-box-arrow-in-right"></i> Đăng nhập</a>
    </div>
    <?php endif; ?>

    <div class="sidebar-user">
        <div class="u-name"><?= htmlspecialchars($full_name) ?></div>
        <div class="u-role"><?= $role_label ?></div>
    </div>

    <div class="sidebar-footer">
        <?php if (isLoggedIn()): ?>
        <a href="<?= $sys ?>/auth/logout.php" class="logout-link">
            <i class="bi bi-box-arrow-right"></i> Đăng xuất
        </a>
        <?php endif; ?>
    </div>
</div>

<div class="main-wrapper">
    <div class="topbar">
        <?php
        $titles = [
            'users'=>'Người dùng','departments'=>'Khoa / Bộ môn','companies'=>'Doanh nghiệp',
            'position_skills'=>'Kỹ năng – Vị trí','positions'=>'Vị trí Thực tập',
            'skills'=>'Kỹ năng','registrations'=>'Đăng ký Thực tập',
            'assignments'=>'Phân công GVHD','journals'=>'Nhật ký',
            'company_eval'=>'Đánh giá DN','lecturer_eval'=>'Đánh giá GV',
            'grades'=>'Điểm Tổng hợp','index'=>'Dashboard',
        ];
        $page_key='index';
        foreach ($titles as $k=>$v) { if (strpos($current_page,$k)!==false){$page_key=$k;break;} }
        ?>
        <div>
            <div class="topbar-title"><?= $titles[$page_key] ?></div>
            <div class="topbar-sub"><?= date('l, d/m/Y') ?></div>
        </div>
        <div class="ms-auto d-flex align-items-center gap-3">
            <div style="width:36px;height:36px;border-radius:50%;background:var(--mint);display:flex;align-items:center;justify-content:center;font-weight:800;font-size:.85rem;color:var(--sid-dark);">
                <?= $initials ?>
            </div>
        </div>
    </div>
    <div class="main-content">
