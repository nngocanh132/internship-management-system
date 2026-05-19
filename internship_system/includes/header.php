<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ISchool Internship Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        /* ===== PALETTE ===== */
        :root {
            --ivory:      #F4F3F1;
            --blush:      #DECEBF;
            --sage:       #A1A79E;
            --terracotta: #B57B66;
            --evergreen:  #68756D;
            --ev-dark:    #4a5c52;
            --ev-light:   rgba(104,117,109,.12);
        }

        /* ===== BASE ===== */
        *, *::before, *::after { box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: var(--ivory);
            color: #2d3748;
            margin: 0;
        }

        /* ===== SIDEBAR ===== */
        .sidebar {
            width: 260px;
            min-height: 100vh;
            background: linear-gradient(180deg, var(--ev-dark) 0%, var(--evergreen) 100%);
            position: fixed;
            top: 0; left: 0;
            display: flex;
            flex-direction: column;
            z-index: 1000;
            box-shadow: 4px 0 24px rgba(74,92,82,.25);
            overflow-y: auto;
        }

        .sidebar-brand {
            padding: 22px 20px 18px;
            border-bottom: 1px solid rgba(255,255,255,.1);
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .sidebar-brand .brand-icon {
            width: 42px; height: 42px;
            background: rgba(255,255,255,.15);
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.2rem; color: #fff;
            flex-shrink: 0;
        }
        .sidebar-brand .brand-title {
            font-size: .95rem;
            font-weight: 700;
            color: #fff;
            letter-spacing: .3px;
        }
        .sidebar-brand .brand-sub {
            font-size: .7rem;
            color: rgba(255,255,255,.55);
            font-weight: 400;
        }

        /* Role badge in sidebar */
        .sidebar-role {
            margin: 12px 16px 0;
            padding: 8px 14px;
            background: rgba(255,255,255,.1);
            border-radius: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .sidebar-role .role-avatar {
            width: 32px; height: 32px;
            border-radius: 8px;
            background: rgba(255,255,255,.2);
            display: flex; align-items: center; justify-content: center;
            font-size: .85rem; font-weight: 700; color: #fff;
            flex-shrink: 0;
        }
        .sidebar-role .role-name {
            font-size: .8rem;
            font-weight: 600;
            color: #fff;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .sidebar-role .role-badge {
            font-size: .65rem;
            color: rgba(255,255,255,.6);
        }

        .nav-section {
            padding: 16px 16px 4px;
        }
        .nav-section-label {
            font-size: .65rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            color: rgba(255,255,255,.4);
            padding: 0 8px;
            margin-bottom: 4px;
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 9px 12px;
            border-radius: 8px;
            color: rgba(255,255,255,.7);
            text-decoration: none;
            font-size: .855rem;
            font-weight: 500;
            transition: all .18s ease;
            margin-bottom: 2px;
        }
        .sidebar-link i {
            font-size: 1rem;
            width: 20px;
            text-align: center;
            flex-shrink: 0;
        }
        .sidebar-link:hover {
            background: rgba(255,255,255,.1);
            color: #fff;
            transform: translateX(2px);
        }
        .sidebar-link.active {
            background: rgba(255,255,255,.18);
            color: #fff;
            border-left: 3px solid var(--blush);
            font-weight: 600;
        }

        .sidebar-footer {
            margin-top: auto;
            padding: 16px;
            border-top: 1px solid rgba(255,255,255,.08);
        }
        .sidebar-footer a {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 9px 14px;
            border-radius: 8px;
            color: rgba(255,255,255,.55);
            text-decoration: none;
            font-size: .82rem;
            font-weight: 500;
            transition: all .18s;
        }
        .sidebar-footer a:hover {
            background: rgba(255,255,255,.08);
            color: rgba(255,255,255,.85);
        }
        .sidebar-footer .logout-link {
            color: rgba(181,123,102,.85);
        }
        .sidebar-footer .logout-link:hover {
            background: rgba(181,123,102,.12);
            color: var(--terracotta);
        }

        /* ===== TOPBAR ===== */
        .topbar {
            height: 64px;
            background: #fff;
            border-bottom: 1px solid var(--blush);
            display: flex;
            align-items: center;
            padding: 0 28px;
            position: sticky;
            top: 0;
            z-index: 900;
            box-shadow: 0 1px 8px rgba(104,117,109,.08);
        }
        .topbar-title {
            font-size: 1.05rem;
            font-weight: 700;
            color: var(--ev-dark);
        }

        /* ===== MAIN LAYOUT ===== */
        .main-wrapper {
            margin-left: 260px;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .main-content {
            padding: 28px 32px;
            flex: 1;
        }

        /* ===== CARDS ===== */
        .card {
            border: none;
            border-radius: 14px;
            box-shadow: 0 2px 12px rgba(104,117,109,.08);
            background: #fff;
            transition: box-shadow .2s, transform .2s;
        }
        .card:hover { box-shadow: 0 6px 24px rgba(104,117,109,.13); }

        /* Stat cards */
        .stat-card {
            border-radius: 16px;
            padding: 22px 24px;
            color: #fff;
            position: relative;
            overflow: hidden;
            cursor: default;
            transition: transform .2s, box-shadow .2s;
        }
        .stat-card:hover { transform: translateY(-3px); box-shadow: 0 12px 32px rgba(0,0,0,.15); }
        .stat-card .stat-icon {
            position: absolute;
            right: 18px; top: 50%;
            transform: translateY(-50%);
            font-size: 3.5rem;
            opacity: .15;
        }
        .stat-card .stat-number {
            font-size: 2.4rem;
            font-weight: 800;
            line-height: 1;
            margin-bottom: 4px;
        }
        .stat-card .stat-label {
            font-size: .82rem;
            font-weight: 500;
            opacity: .88;
            text-transform: uppercase;
            letter-spacing: .5px;
        }
        .stat-card .stat-link {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            color: rgba(255,255,255,.75);
            font-size: .75rem;
            text-decoration: none;
            margin-top: 10px;
            transition: color .15s;
        }
        .stat-card .stat-link:hover { color: #fff; }

        .stat-ev      { background: linear-gradient(135deg, var(--evergreen), var(--ev-dark)); }
        .stat-terra   { background: linear-gradient(135deg, var(--terracotta), #9a5e4a); }
        .stat-sage    { background: linear-gradient(135deg, var(--sage), #7a8078); }
        .stat-blush   { background: linear-gradient(135deg, #c9a98e, #b08060); }
        .stat-green   { background: linear-gradient(135deg, #10b981, #059669); }
        .stat-blue    { background: linear-gradient(135deg, #3b82f6, #1d4ed8); }
        .stat-purple  { background: linear-gradient(135deg, #8b5cf6, #6d28d9); }
        .stat-orange  { background: linear-gradient(135deg, #f59e0b, #d97706); }

        /* ===== TABLE ===== */
        .table-card .card-header {
            background: #fff;
            border-bottom: 2px solid var(--ivory);
            padding: 16px 20px;
            border-radius: 14px 14px 0 0 !important;
            font-weight: 600;
            font-size: .9rem;
            color: var(--ev-dark);
        }
        .table thead th {
            background: var(--ivory);
            color: var(--sage);
            font-size: .75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .6px;
            border-bottom: 2px solid var(--blush);
            padding: 12px 16px;
            white-space: nowrap;
        }
        .table tbody td {
            padding: 12px 16px;
            vertical-align: middle;
            font-size: .875rem;
            border-bottom: 1px solid var(--ivory);
            color: #374151;
        }
        .table tbody tr:hover td { background: #faf9f7; }
        .table tbody tr:last-child td { border-bottom: none; }

        /* ===== BUTTONS ===== */
        .btn { border-radius: 8px; font-weight: 500; font-size: .855rem; }
        .btn-primary   { background: linear-gradient(135deg, var(--evergreen), var(--ev-dark)); border: none; color: #fff; }
        .btn-primary:hover { background: linear-gradient(135deg, var(--ev-dark), #3a4c42); color: #fff; }
        .btn-success   { background: linear-gradient(135deg, #10b981, #059669); border: none; }
        .btn-warning   { background: linear-gradient(135deg, var(--terracotta), #9a5e4a); border: none; color: #fff; }
        .btn-warning:hover { color: #fff; }
        .btn-danger    { background: linear-gradient(135deg, #ef4444, #dc2626); border: none; }
        .btn-secondary { background: var(--blush); border: none; color: var(--ev-dark); }
        .btn-secondary:hover { background: #cdbdad; color: var(--ev-dark); }
        .btn-sm { padding: 5px 12px; font-size: .8rem; }

        /* ===== BADGES ===== */
        .badge { border-radius: 6px; font-weight: 600; font-size: .72rem; padding: 4px 9px; }

        /* ===== FORMS ===== */
        .form-control, .form-select {
            border-radius: 8px;
            border: 1.5px solid var(--blush);
            font-size: .875rem;
            padding: 9px 14px;
            background: var(--ivory);
            transition: border-color .2s, box-shadow .2s;
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--evergreen);
            box-shadow: 0 0 0 3px rgba(104,117,109,.12);
            background: #fff;
        }
        .form-label { font-weight: 600; font-size: .82rem; color: #374151; margin-bottom: 6px; }

        /* ===== PAGE HEADER ===== */
        .page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
        }
        .page-header h4 {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--ev-dark);
            margin: 0;
        }
        .page-header .page-subtitle {
            font-size: .8rem;
            color: var(--sage);
            margin-top: 2px;
        }

        /* ===== ALERTS ===== */
        .alert {
            border: none;
            border-radius: 12px;
            font-size: .875rem;
            padding: 14px 18px;
        }
        .alert-warning { background: #fffbeb; color: #92400e; border-left: 4px solid #f59e0b; }
        .alert-danger  { background: #fef2f2; color: #991b1b; border-left: 4px solid var(--terracotta); }
        .alert-success { background: #f0fdf4; color: #166534; border-left: 4px solid #22c55e; }
        .alert-info    { background: #f0f4f2; color: var(--ev-dark); border-left: 4px solid var(--evergreen); }

        /* ===== PROGRESS ===== */
        .progress { border-radius: 99px; background: var(--blush); }
        .progress-bar { border-radius: 99px; background: var(--evergreen); }

        /* ===== MISC ===== */
        .section-divider {
            height: 1px;
            background: linear-gradient(90deg, var(--blush), transparent);
            margin: 24px 0;
        }
        .avatar-sm {
            width: 34px; height: 34px;
            border-radius: 8px;
            display: inline-flex; align-items: center; justify-content: center;
            font-size: .85rem; font-weight: 700;
            flex-shrink: 0;
        }
        .quick-action-card {
            border-radius: 12px;
            padding: 16px 18px;
            display: flex;
            align-items: center;
            gap: 14px;
            text-decoration: none;
            color: inherit;
            background: #fff;
            border: 1.5px solid var(--blush);
            transition: all .2s;
        }
        .quick-action-card:hover {
            border-color: var(--evergreen);
            box-shadow: 0 4px 16px rgba(104,117,109,.15);
            transform: translateY(-2px);
            color: inherit;
        }
        .quick-action-card .qa-icon {
            width: 42px; height: 42px;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.2rem;
            flex-shrink: 0;
        }
        .quick-action-card .qa-title { font-weight: 600; font-size: .875rem; color: var(--ev-dark); }
        .quick-action-card .qa-sub   { font-size: .75rem; color: var(--sage); }

        /* ===== SCROLLBAR ===== */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: var(--blush); border-radius: 99px; }
        ::-webkit-scrollbar-thumb:hover { background: var(--sage); }
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

$role_labels = [
    'admin'    => 'Quản trị viên',
    'lecturer' => 'Giảng viên',
    'company'  => 'Doanh nghiệp',
    'student'  => 'Sinh viên',
];
$role_label = $role_labels[$role] ?? 'Người dùng';
?>

<!-- ===== SIDEBAR ===== -->
<div class="sidebar">
    <div class="sidebar-brand">
        <div class="brand-icon"><i class="bi bi-mortarboard-fill"></i></div>
        <div class="brand-text">
            <div class="brand-title">ISchool Internship</div>
            <div class="brand-sub">Management System</div>
        </div>
    </div>

    <!-- User info -->
    <div class="sidebar-role">
        <div class="role-avatar"><?= $initials ?></div>
        <div style="min-width:0">
            <div class="role-name"><?= htmlspecialchars($full_name) ?></div>
            <div class="role-badge"><?= $role_label ?></div>
        </div>
    </div>

    <?php if ($role === 'admin' || $role === 'lecturer'): ?>
    <!-- ── ADMIN / LECTURER MENU ── -->
    <div class="nav-section">
        <div class="nav-section-label">Tổng quan</div>
        <a href="<?= $sys ?>/index.php" class="sidebar-link <?= isActive('/index.php') && !isActive('/modules/') ? 'active' : '' ?>">
            <i class="bi bi-grid-fill"></i> Dashboard
        </a>
    </div>

    <div class="nav-section">
        <div class="nav-section-label">Quản lý người dùng</div>
        <a href="<?= $sys ?>/modules/users/list.php" class="sidebar-link <?= isActive('/users/') ?>">
            <i class="bi bi-people-fill"></i> Người dùng
        </a>
        <a href="<?= $sys ?>/modules/companies/list.php" class="sidebar-link <?= isActive('/companies/') ?>">
            <i class="bi bi-building-fill"></i> Doanh nghiệp
        </a>
    </div>

    <div class="nav-section">
        <div class="nav-section-label">Thực tập</div>
        <a href="<?= $sys ?>/modules/positions/list.php" class="sidebar-link <?= isActive('/positions/') ?>">
            <i class="bi bi-briefcase-fill"></i> Vị trí thực tập
        </a>
        <a href="<?= $sys ?>/modules/registrations/list.php" class="sidebar-link <?= isActive('/registrations/') ?>">
            <i class="bi bi-clipboard-check-fill"></i> Đăng ký thực tập
        </a>
        <a href="<?= $sys ?>/modules/assignments/list.php" class="sidebar-link <?= isActive('/assignments/') ?>">
            <i class="bi bi-person-check-fill"></i> Phân công GVHD
        </a>
    </div>

    <div class="nav-section">
        <div class="nav-section-label">Theo dõi & Đánh giá</div>
        <a href="<?= $sys ?>/modules/journals/list.php" class="sidebar-link <?= isActive('/journals/') ?>">
            <i class="bi bi-journal-richtext"></i> Nhật ký hàng tuần
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
    <!-- ── COMPANY MENU ── -->
    <div class="nav-section">
        <div class="nav-section-label">Tổng quan</div>
        <a href="<?= $sys ?>/index.php" class="sidebar-link <?= isActive('/index.php') && !isActive('/modules/') ? 'active' : '' ?>">
            <i class="bi bi-grid-fill"></i> Dashboard
        </a>
    </div>
    <div class="nav-section">
        <div class="nav-section-label">Quản lý</div>
        <a href="<?= $sys ?>/modules/positions/list.php" class="sidebar-link <?= isActive('/positions/') ?>">
            <i class="bi bi-briefcase-fill"></i> Vị trí thực tập
        </a>
        <a href="<?= $sys ?>/modules/registrations/list.php" class="sidebar-link <?= isActive('/registrations/') ?>">
            <i class="bi bi-clipboard-check-fill"></i> Sinh viên đăng ký
        </a>
        <a href="<?= $sys ?>/modules/journals/list.php" class="sidebar-link <?= isActive('/journals/') ?>">
            <i class="bi bi-journal-richtext"></i> Nhật ký sinh viên
        </a>
        <a href="<?= $sys ?>/modules/company_eval/list.php" class="sidebar-link <?= isActive('/company_eval/') ?>">
            <i class="bi bi-star-fill"></i> Đánh giá sinh viên
        </a>
    </div>

    <?php elseif ($role === 'student'): ?>
    <!-- ── STUDENT MENU ── -->
    <div class="nav-section">
        <div class="nav-section-label">Tổng quan</div>
        <a href="<?= $sys ?>/index.php" class="sidebar-link <?= isActive('/index.php') && !isActive('/modules/') ? 'active' : '' ?>">
            <i class="bi bi-grid-fill"></i> Dashboard
        </a>
    </div>
    <div class="nav-section">
        <div class="nav-section-label">Thực tập của tôi</div>
        <a href="<?= $sys ?>/modules/positions/list.php" class="sidebar-link <?= isActive('/positions/') ?>">
            <i class="bi bi-briefcase-fill"></i> Tìm vị trí thực tập
        </a>
        <a href="<?= $sys ?>/modules/registrations/list.php" class="sidebar-link <?= isActive('/registrations/') ?>">
            <i class="bi bi-clipboard-check-fill"></i> Đăng ký của tôi
        </a>
        <a href="<?= $sys ?>/modules/journals/list.php" class="sidebar-link <?= isActive('/journals/') ?>">
            <i class="bi bi-journal-richtext"></i> Nhật ký của tôi
        </a>
        <a href="<?= $sys ?>/modules/grades/list.php" class="sidebar-link <?= isActive('/grades/') ?>">
            <i class="bi bi-award-fill"></i> Điểm của tôi
        </a>
    </div>

    <?php else: ?>
    <!-- Guest / fallback -->
    <div class="nav-section">
        <a href="<?= $sys ?>/auth/login.php" class="sidebar-link">
            <i class="bi bi-box-arrow-in-right"></i> Đăng nhập
        </a>
    </div>
    <?php endif; ?>

    <div class="sidebar-footer">
        <a href="<?= $sys ?>/index.php">
            <i class="bi bi-house-fill"></i> Trang chủ
        </a>
        <?php if (isLoggedIn()): ?>
        <a href="<?= $sys ?>/auth/logout.php" class="logout-link">
            <i class="bi bi-box-arrow-right"></i> Đăng xuất
        </a>
        <?php endif; ?>
    </div>
</div>

<!-- ===== MAIN WRAPPER ===== -->
<div class="main-wrapper">
    <!-- Topbar -->
    <div class="topbar">
        <div>
            <div class="topbar-title">
                <?php
                $titles = [
                    'users'        => 'Quản lý Người dùng',
                    'companies'    => 'Quản lý Doanh nghiệp',
                    'positions'    => 'Vị trí Thực tập',
                    'registrations'=> 'Đăng ký Thực tập',
                    'assignments'  => 'Phân công GVHD',
                    'journals'     => 'Nhật ký Hàng tuần',
                    'company_eval' => 'Đánh giá Doanh nghiệp',
                    'lecturer_eval'=> 'Đánh giá Giảng viên',
                    'grades'       => 'Điểm Tổng hợp',
                    'index'        => 'Dashboard',
                ];
                $page_key = 'index';
                foreach ($titles as $k => $v) {
                    if (strpos($current_page, $k) !== false) { $page_key = $k; break; }
                }
                echo $titles[$page_key];
                ?>
            </div>
        </div>
        <div class="ms-auto d-flex align-items-center gap-3">
            <span class="text-muted small"><i class="bi bi-calendar3 me-1"></i><?= date('d/m/Y') ?></span>
            <div class="d-flex align-items-center gap-2">
                <div style="width:32px;height:32px;border-radius:8px;background:var(--ev-light);display:flex;align-items:center;justify-content:center;color:var(--evergreen);font-size:.8rem;font-weight:700;border:1.5px solid var(--blush);">
                    <?= $initials ?>
                </div>
                <div>
                    <div class="small fw-semibold" style="color:var(--ev-dark);line-height:1.2"><?= htmlspecialchars($full_name) ?></div>
                    <div style="font-size:.68rem;color:var(--sage)"><?= $role_label ?></div>
                </div>
            </div>
            <?php if (isLoggedIn()): ?>
            <a href="<?= $sys ?>/auth/logout.php"
               style="color:var(--sage);font-size:.8rem;text-decoration:none;padding:6px 10px;border-radius:8px;border:1px solid var(--blush);transition:all .18s;"
               onmouseover="this.style.background='var(--blush)'"
               onmouseout="this.style.background='transparent'">
                <i class="bi bi-box-arrow-right"></i>
            </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
