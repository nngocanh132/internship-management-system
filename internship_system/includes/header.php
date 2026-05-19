<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ISchool Internship Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* ===== BASE ===== */
        *, *::before, *::after { box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: #f0f2f8;
            color: #2d3748;
            margin: 0;
        }

        /* ===== SIDEBAR ===== */
        .sidebar {
            width: 260px;
            min-height: 100vh;
            background: linear-gradient(180deg, #0f172a 0%, #1e293b 100%);
            position: fixed;
            top: 0; left: 0;
            display: flex;
            flex-direction: column;
            z-index: 1000;
            box-shadow: 4px 0 20px rgba(0,0,0,.25);
            overflow-y: auto;
        }

        .sidebar-brand {
            padding: 22px 20px 18px;
            border-bottom: 1px solid rgba(255,255,255,.08);
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .sidebar-brand .brand-icon {
            width: 40px; height: 40px;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.2rem; color: #fff;
            flex-shrink: 0;
        }
        .sidebar-brand .brand-text {
            line-height: 1.2;
        }
        .sidebar-brand .brand-title {
            font-size: 0.95rem;
            font-weight: 700;
            color: #f1f5f9;
            letter-spacing: .3px;
        }
        .sidebar-brand .brand-sub {
            font-size: 0.7rem;
            color: #64748b;
            font-weight: 400;
        }

        .nav-section {
            padding: 18px 16px 4px;
        }
        .nav-section-label {
            font-size: 0.65rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            color: #475569;
            padding: 0 8px;
            margin-bottom: 4px;
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 9px 12px;
            border-radius: 8px;
            color: #94a3b8;
            text-decoration: none;
            font-size: 0.855rem;
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
            background: rgba(255,255,255,.07);
            color: #e2e8f0;
            transform: translateX(2px);
        }
        .sidebar-link.active {
            background: linear-gradient(135deg, rgba(99,102,241,.35), rgba(139,92,246,.25));
            color: #a5b4fc;
            border-left: 3px solid #6366f1;
        }

        .sidebar-footer {
            margin-top: auto;
            padding: 16px;
            border-top: 1px solid rgba(255,255,255,.06);
        }
        .sidebar-footer a {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 9px 14px;
            border-radius: 8px;
            color: #64748b;
            text-decoration: none;
            font-size: 0.82rem;
            font-weight: 500;
            transition: all .18s;
        }
        .sidebar-footer a:hover {
            background: rgba(255,255,255,.06);
            color: #94a3b8;
        }

        /* ===== TOPBAR ===== */
        .topbar {
            height: 64px;
            background: #fff;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            padding: 0 28px;
            position: sticky;
            top: 0;
            z-index: 900;
            box-shadow: 0 1px 8px rgba(0,0,0,.06);
        }
        .topbar-title {
            font-size: 1.05rem;
            font-weight: 600;
            color: #1e293b;
        }
        .topbar-badge {
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color: #fff;
            font-size: 0.7rem;
            padding: 3px 10px;
            border-radius: 20px;
            font-weight: 600;
            margin-left: 10px;
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
            box-shadow: 0 2px 12px rgba(0,0,0,.07);
            transition: box-shadow .2s, transform .2s;
        }
        .card:hover { box-shadow: 0 6px 24px rgba(0,0,0,.11); }

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
        .stat-card:hover { transform: translateY(-3px); box-shadow: 0 12px 32px rgba(0,0,0,.18); }
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
            font-size: 0.82rem;
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
            font-size: 0.75rem;
            text-decoration: none;
            margin-top: 10px;
            transition: color .15s;
        }
        .stat-card .stat-link:hover { color: #fff; }

        .stat-blue    { background: linear-gradient(135deg, #3b82f6, #1d4ed8); }
        .stat-green   { background: linear-gradient(135deg, #10b981, #059669); }
        .stat-purple  { background: linear-gradient(135deg, #8b5cf6, #6d28d9); }
        .stat-orange  { background: linear-gradient(135deg, #f59e0b, #d97706); }
        .stat-red     { background: linear-gradient(135deg, #ef4444, #dc2626); }
        .stat-teal    { background: linear-gradient(135deg, #14b8a6, #0d9488); }

        /* ===== TABLE ===== */
        .table-card .card-header {
            background: #fff;
            border-bottom: 2px solid #f1f5f9;
            padding: 16px 20px;
            border-radius: 14px 14px 0 0 !important;
            font-weight: 600;
            font-size: 0.9rem;
            color: #1e293b;
        }
        .table thead th {
            background: #f8fafc;
            color: #64748b;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .6px;
            border-bottom: 2px solid #e2e8f0;
            padding: 12px 16px;
            white-space: nowrap;
        }
        .table tbody td {
            padding: 12px 16px;
            vertical-align: middle;
            font-size: 0.875rem;
            border-bottom: 1px solid #f1f5f9;
            color: #374151;
        }
        .table tbody tr:hover td { background: #f8fafc; }
        .table tbody tr:last-child td { border-bottom: none; }

        /* ===== BUTTONS ===== */
        .btn { border-radius: 8px; font-weight: 500; font-size: 0.855rem; }
        .btn-primary   { background: linear-gradient(135deg, #6366f1, #4f46e5); border: none; }
        .btn-primary:hover { background: linear-gradient(135deg, #4f46e5, #4338ca); }
        .btn-success   { background: linear-gradient(135deg, #10b981, #059669); border: none; }
        .btn-warning   { background: linear-gradient(135deg, #f59e0b, #d97706); border: none; color: #fff; }
        .btn-warning:hover { color: #fff; }
        .btn-danger    { background: linear-gradient(135deg, #ef4444, #dc2626); border: none; }
        .btn-sm { padding: 5px 12px; font-size: 0.8rem; }

        /* ===== BADGES ===== */
        .badge { border-radius: 6px; font-weight: 600; font-size: 0.72rem; padding: 4px 9px; }

        /* ===== FORMS ===== */
        .form-control, .form-select {
            border-radius: 8px;
            border: 1.5px solid #e2e8f0;
            font-size: 0.875rem;
            padding: 9px 14px;
            transition: border-color .2s, box-shadow .2s;
        }
        .form-control:focus, .form-select:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99,102,241,.15);
        }
        .form-label { font-weight: 600; font-size: 0.82rem; color: #374151; margin-bottom: 6px; }

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
            color: #1e293b;
            margin: 0;
        }
        .page-header .page-subtitle {
            font-size: 0.8rem;
            color: #94a3b8;
            margin-top: 2px;
        }

        /* ===== ALERTS ===== */
        .alert {
            border: none;
            border-radius: 12px;
            font-size: 0.875rem;
            padding: 14px 18px;
        }
        .alert-warning { background: #fffbeb; color: #92400e; border-left: 4px solid #f59e0b; }
        .alert-danger  { background: #fef2f2; color: #991b1b; border-left: 4px solid #ef4444; }
        .alert-success { background: #f0fdf4; color: #166534; border-left: 4px solid #22c55e; }
        .alert-info    { background: #eff6ff; color: #1e40af; border-left: 4px solid #3b82f6; }

        /* ===== PROGRESS ===== */
        .progress { border-radius: 99px; background: #e2e8f0; }
        .progress-bar { border-radius: 99px; }

        /* ===== MISC ===== */
        .section-divider {
            height: 1px;
            background: linear-gradient(90deg, #e2e8f0, transparent);
            margin: 24px 0;
        }
        .avatar-sm {
            width: 34px; height: 34px;
            border-radius: 8px;
            display: inline-flex; align-items: center; justify-content: center;
            font-size: 0.85rem; font-weight: 700;
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
            border: 1.5px solid #e2e8f0;
            transition: all .2s;
        }
        .quick-action-card:hover {
            border-color: #6366f1;
            box-shadow: 0 4px 16px rgba(99,102,241,.15);
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
        .quick-action-card .qa-title { font-weight: 600; font-size: 0.875rem; color: #1e293b; }
        .quick-action-card .qa-sub   { font-size: 0.75rem; color: #94a3b8; }

        /* ===== SCROLLBAR ===== */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 99px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>
</head>
<body>

<?php
// Detect current page for active sidebar link
$current_page = $_SERVER['REQUEST_URI'];
function isActive($path) {
    global $current_page;
    return strpos($current_page, $path) !== false ? 'active' : '';
}

// Build base URL to /internship_system by finding it in the current script path
$script_url = str_replace('\\', '/', $_SERVER['SCRIPT_NAME']); // e.g. /internship-management-system/internship_system/modules/users/list.php
$pos = strpos($script_url, '/internship_system');
$sys = ($pos !== false) ? substr($script_url, 0, $pos) . '/internship_system' : '/internship_system';
// $sys = e.g. /internship-management-system/internship_system
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

    <!-- Module 1 -->
    <div class="nav-section">
        <div class="nav-section-label">Module 1 · Thành viên 1</div>
        <a href="<?= $sys ?>/modules/users/list.php"
           class="sidebar-link <?= isActive('/users/') ?>">
            <i class="bi bi-people-fill"></i> Quản lý Users
        </a>
        <a href="<?= $sys ?>/modules/companies/list.php"
           class="sidebar-link <?= isActive('/companies/') ?>">
            <i class="bi bi-building-fill"></i> Doanh nghiệp
        </a>
        <a href="<?= $sys ?>/modules/positions/list.php"
           class="sidebar-link <?= isActive('/positions/') ?>">
            <i class="bi bi-briefcase-fill"></i> Vị trí Thực tập
        </a>
    </div>

    <!-- Module 2 -->
    <div class="nav-section">
        <div class="nav-section-label">Module 2 · Thành viên 2</div>
        <a href="<?= $sys ?>/modules/registrations/list.php"
           class="sidebar-link <?= isActive('/registrations/') ?>">
            <i class="bi bi-clipboard-check-fill"></i> Đăng ký Thực tập
        </a>
        <a href="<?= $sys ?>/modules/assignments/list.php"
           class="sidebar-link <?= isActive('/assignments/') ?>">
            <i class="bi bi-person-check-fill"></i> Phân công GVHD
        </a>
        <a href="<?= $sys ?>/modules/journals/list.php"
           class="sidebar-link <?= isActive('/journals/') ?>">
            <i class="bi bi-journal-richtext"></i> Nhật ký Hàng tuần
        </a>
    </div>

    <!-- Module 3 -->
    <div class="nav-section">
        <div class="nav-section-label">Module 3 · Thành viên 3</div>
        <a href="<?= $sys ?>/modules/company_eval/list.php"
           class="sidebar-link <?= isActive('/company_eval/') ?>">
            <i class="bi bi-star-fill"></i> Đánh giá DN
        </a>
        <a href="<?= $sys ?>/modules/lecturer_eval/list.php"
           class="sidebar-link <?= isActive('/lecturer_eval/') ?>">
            <i class="bi bi-patch-check-fill"></i> Đánh giá GV
        </a>
        <a href="<?= $sys ?>/modules/grades/list.php"
           class="sidebar-link <?= isActive('/grades/') ?>">
            <i class="bi bi-award-fill"></i> Điểm Tổng hợp
        </a>
    </div>

    <div class="sidebar-footer">
        <a href="<?= $sys ?>/index.php">
            <i class="bi bi-house-fill"></i> Trang chủ Dashboard
        </a>
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
                <div style="width:32px;height:32px;border-radius:8px;background:linear-gradient(135deg,#6366f1,#8b5cf6);display:flex;align-items:center;justify-content:center;color:#fff;font-size:.8rem;font-weight:700;">A</div>
                <span class="small fw-semibold text-secondary">Admin</span>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
