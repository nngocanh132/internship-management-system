<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

requireLogin();

$role = userRole();

// ── Statistics (safe queries matching actual schema) ──────────────────────────
$stats = [];
$stats['total_users']    = $conn->query("SELECT COUNT(*) AS c FROM users")->fetch_assoc()['c'];
$stats['students']       = $conn->query("SELECT COUNT(*) AS c FROM users WHERE role='student'")->fetch_assoc()['c'];
$stats['lecturers']      = $conn->query("SELECT COUNT(*) AS c FROM users WHERE role='lecturer'")->fetch_assoc()['c'];
$stats['companies']      = $conn->query("SELECT COUNT(*) AS c FROM companies WHERE status='active'")->fetch_assoc()['c'];
$stats['positions_open'] = $conn->query("SELECT COUNT(*) AS c FROM internship_positions WHERE status='open'")->fetch_assoc()['c'];
$stats['reg_total']      = $conn->query("SELECT COUNT(*) AS c FROM internship_registrations")->fetch_assoc()['c'];
$stats['reg_pending']    = $conn->query("SELECT COUNT(*) AS c FROM internship_registrations WHERE status='pending'")->fetch_assoc()['c'];
$stats['reg_approved']   = $conn->query("SELECT COUNT(*) AS c FROM internship_registrations WHERE status='approved'")->fetch_assoc()['c'];
$stats['reg_rejected']   = $conn->query("SELECT COUNT(*) AS c FROM internship_registrations WHERE status='rejected'")->fetch_assoc()['c'];
$stats['journals']       = $conn->query("SELECT COUNT(*) AS c FROM weekly_journals")->fetch_assoc()['c'];
$stats['assignments']    = $conn->query("SELECT COUNT(*) AS c FROM internship_assignments")->fetch_assoc()['c'];
$stats['grades']         = $conn->query("SELECT COUNT(*) AS c FROM final_grades")->fetch_assoc()['c'];
$stats['avg_grade']      = $conn->query("SELECT ROUND(AVG(final_score),2) AS c FROM final_grades")->fetch_assoc()['c'] ?? 0;

// ── Student-specific stats ────────────────────────────────────────────────────
if ($role === 'student') {
    $uid = (int)$_SESSION['user_id'];
    $stats['my_regs']     = $conn->query("SELECT COUNT(*) AS c FROM internship_registrations WHERE student_id=$uid")->fetch_assoc()['c'];
    $stats['my_approved'] = $conn->query("SELECT COUNT(*) AS c FROM internship_registrations WHERE student_id=$uid AND status='approved'")->fetch_assoc()['c'];
    $stats['my_journals'] = $conn->query(
        "SELECT COUNT(*) AS c FROM weekly_journals wj
         JOIN internship_assignments ia ON wj.assignment_id=ia.assignment_id
         JOIN internship_registrations ir ON ia.registration_id=ir.registration_id
         WHERE ir.student_id=$uid"
    )->fetch_assoc()['c'];
}

// ── Company-specific stats ────────────────────────────────────────────────────
if ($role === 'company') {
    $uid = (int)$_SESSION['user_id'];
    // company user linked via companies table — find company by contact_email
    $cRow = $conn->query("SELECT company_id FROM companies WHERE contact_email='" . $conn->real_escape_string($_SESSION['email']) . "' LIMIT 1")->fetch_assoc();
    $cid  = $cRow ? (int)$cRow['company_id'] : 0;
    $stats['my_positions']  = $conn->query("SELECT COUNT(*) AS c FROM internship_positions WHERE company_id=$cid")->fetch_assoc()['c'];
    $stats['my_applicants'] = $conn->query(
        "SELECT COUNT(*) AS c FROM internship_registrations ir
         JOIN internship_positions ip ON ir.position_id=ip.position_id
         WHERE ip.company_id=$cid"
    )->fetch_assoc()['c'];
}

// ── Recent registrations (admin/lecturer) ────────────────────────────────────
$recent_regs = [];
if ($role === 'admin' || $role === 'lecturer') {
    $recent_regs = $conn->query(
        "SELECT r.registration_id, r.status, r.registered_at,
                u.full_name AS student_name, u.student_code,
                p.title AS position_title, c.name AS company_name
         FROM internship_registrations r
         JOIN users u ON r.student_id = u.user_id
         JOIN internship_positions p ON r.position_id = p.position_id
         JOIN companies c ON p.company_id = c.company_id
         ORDER BY r.registered_at DESC LIMIT 6"
    )->fetch_all(MYSQLI_ASSOC);
}

// ── Top companies ─────────────────────────────────────────────────────────────
$top_companies = [];
if ($role === 'admin' || $role === 'lecturer') {
    $top_companies = $conn->query(
        "SELECT c.name AS company_name, COUNT(p.position_id) AS pos_count,
                SUM(p.quota) AS total_quota
         FROM companies c
         LEFT JOIN internship_positions p ON c.company_id = p.company_id
         WHERE c.status = 'active'
         GROUP BY c.company_id
         ORDER BY pos_count DESC LIMIT 5"
    )->fetch_all(MYSQLI_ASSOC);
}

// ── Student: my registrations ─────────────────────────────────────────────────
$my_regs = [];
if ($role === 'student') {
    $uid = (int)$_SESSION['user_id'];
    $my_regs = $conn->query(
        "SELECT r.registration_id, r.status, r.registered_at,
                p.title AS position_title, c.name AS company_name
         FROM internship_registrations r
         JOIN internship_positions p ON r.position_id = p.position_id
         JOIN companies c ON p.company_id = c.company_id
         WHERE r.student_id = $uid
         ORDER BY r.registered_at DESC LIMIT 5"
    )->fetch_all(MYSQLI_ASSOC);
}

// ── Company: recent applicants ────────────────────────────────────────────────
$company_applicants = [];
if ($role === 'company' && isset($cid) && $cid > 0) {
    $company_applicants = $conn->query(
        "SELECT r.registration_id, r.status, r.registered_at,
                u.full_name AS student_name, u.student_code,
                p.title AS position_title
         FROM internship_registrations r
         JOIN users u ON r.student_id = u.user_id
         JOIN internship_positions p ON r.position_id = p.position_id
         WHERE p.company_id = $cid
         ORDER BY r.registered_at DESC LIMIT 6"
    )->fetch_all(MYSQLI_ASSOC);
}
?>
<?php include 'includes/header.php'; ?>

<!-- ── Page Header ── -->
<div class="page-header">
    <div>
        <h4><i class="bi bi-grid-fill me-2" style="color:var(--evergreen)"></i>Dashboard</h4>
        <div class="page-subtitle">
            <?php if ($role === 'student'): ?>
                Xin chào, <?= htmlspecialchars($full_name ?? '') ?> · Theo dõi tiến trình thực tập của bạn
            <?php elseif ($role === 'company'): ?>
                Quản lý vị trí thực tập và sinh viên của doanh nghiệp
            <?php else: ?>
                Tổng quan hệ thống quản lý thực tập · <?= date('l, d/m/Y') ?>
            <?php endif; ?>
        </div>
    </div>
    <?php if ($role === 'admin' || $role === 'lecturer'): ?>
    <div class="d-flex gap-2">
        <a href="modules/registrations/add.php" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i>Đăng ký mới
        </a>
        <a href="modules/grades/list.php" class="btn btn-secondary btn-sm">
            <i class="bi bi-calculator me-1"></i>Tính điểm
        </a>
    </div>
    <?php elseif ($role === 'student'): ?>
    <a href="modules/positions/list.php" class="btn btn-primary btn-sm">
        <i class="bi bi-search me-1"></i>Tìm vị trí thực tập
    </a>
    <?php elseif ($role === 'company'): ?>
    <a href="modules/positions/add.php" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg me-1"></i>Đăng vị trí mới
    </a>
    <?php endif; ?>
</div>

<?php showFlash(); ?>

<!-- ════════════════════════════════════════════════════════
     ADMIN / LECTURER DASHBOARD
     ════════════════════════════════════════════════════════ -->
<?php if ($role === 'admin' || $role === 'lecturer'): ?>

<!-- Stat Cards Row 1 -->
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="stat-card stat-ev">
            <i class="bi bi-people-fill stat-icon"></i>
            <div class="stat-number"><?= $stats['students'] ?></div>
            <div class="stat-label">Sinh viên</div>
            <a href="modules/users/list.php?role=student" class="stat-link">Xem danh sách <i class="bi bi-arrow-right"></i></a>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="stat-card stat-terra">
            <i class="bi bi-building-fill stat-icon"></i>
            <div class="stat-number"><?= $stats['companies'] ?></div>
            <div class="stat-label">Doanh nghiệp đối tác</div>
            <a href="modules/companies/list.php" class="stat-link">Xem danh sách <i class="bi bi-arrow-right"></i></a>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="stat-card stat-sage">
            <i class="bi bi-briefcase-fill stat-icon"></i>
            <div class="stat-number"><?= $stats['positions_open'] ?></div>
            <div class="stat-label">Vị trí đang mở</div>
            <a href="modules/positions/list.php?status=open" class="stat-link">Xem danh sách <i class="bi bi-arrow-right"></i></a>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="stat-card stat-blush">
            <i class="bi bi-hourglass-split stat-icon"></i>
            <div class="stat-number"><?= $stats['reg_pending'] ?></div>
            <div class="stat-label">Đăng ký chờ duyệt</div>
            <a href="modules/registrations/list.php?status=pending" class="stat-link">Xem & duyệt <i class="bi bi-arrow-right"></i></a>
        </div>
    </div>
</div>

<!-- Stat Cards Row 2 -->
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="stat-card stat-green">
            <i class="bi bi-clipboard-check-fill stat-icon"></i>
            <div class="stat-number"><?= $stats['reg_approved'] ?></div>
            <div class="stat-label">Đang thực tập</div>
            <a href="modules/registrations/list.php?status=approved" class="stat-link">Xem danh sách <i class="bi bi-arrow-right"></i></a>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="stat-card stat-blue">
            <i class="bi bi-journal-richtext stat-icon"></i>
            <div class="stat-number"><?= $stats['journals'] ?></div>
            <div class="stat-label">Nhật ký đã nộp</div>
            <a href="modules/journals/list.php" class="stat-link">Xem nhật ký <i class="bi bi-arrow-right"></i></a>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="stat-card stat-ev">
            <i class="bi bi-award-fill stat-icon"></i>
            <div class="stat-number"><?= $stats['grades'] ?></div>
            <div class="stat-label">Điểm đã chốt</div>
            <a href="modules/grades/list.php" class="stat-link">Xem điểm <i class="bi bi-arrow-right"></i></a>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="stat-card stat-terra">
            <i class="bi bi-graph-up stat-icon"></i>
            <div class="stat-number"><?= $stats['avg_grade'] ?: '—' ?></div>
            <div class="stat-label">Điểm TB tổng hợp</div>
            <a href="modules/grades/list.php" class="stat-link">Chi tiết <i class="bi bi-arrow-right"></i></a>
        </div>
    </div>
</div>

<!-- Main Content Grid -->
<div class="row g-4">
    <!-- Recent Registrations -->
    <div class="col-xl-7">
        <div class="card table-card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-clock-history me-2" style="color:var(--terracotta)"></i>Đăng ký gần đây</span>
                <a href="modules/registrations/list.php" class="btn btn-secondary btn-sm">Xem tất cả <i class="bi bi-arrow-right ms-1"></i></a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($recent_regs)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-inbox fs-1 d-block mb-2 opacity-25"></i>Chưa có đăng ký nào
                </div>
                <?php else: ?>
                <table class="table mb-0">
                    <thead><tr><th>Sinh viên</th><th>Vị trí / DN</th><th>Ngày</th><th>Trạng thái</th></tr></thead>
                    <tbody>
                    <?php foreach ($recent_regs as $r):
                        $sc = match($r['status']) {
                            'pending'   => ['background:#fffbeb;color:#92400e', 'Chờ duyệt'],
                            'approved'  => ['background:#f0fdf4;color:#166534', 'Đã duyệt'],
                            'rejected'  => ['background:#fef2f2;color:#991b1b', 'Từ chối'],
                            'cancelled' => ['background:var(--ivory);color:var(--sage)', 'Đã hủy'],
                            default     => ['background:var(--ivory);color:var(--sage)', $r['status']]
                        };
                        $colors = ['#68756D','#B57B66','#A1A79E','#8b5cf6','#0ea5e9','#10b981'];
                        $color  = $colors[crc32($r['student_name']) % count($colors)];
                        $ini    = strtoupper(mb_substr($r['student_name'], 0, 1));
                    ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="avatar-sm" style="background:<?= $color ?>20;color:<?= $color ?>"><?= $ini ?></div>
                                <div>
                                    <div class="fw-semibold" style="font-size:.85rem"><?= htmlspecialchars($r['student_name']) ?></div>
                                    <div class="text-muted" style="font-size:.75rem"><?= htmlspecialchars($r['student_code'] ?? '') ?></div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div style="font-size:.85rem;font-weight:500"><?= htmlspecialchars(mb_substr($r['position_title'],0,28)) ?></div>
                            <div class="text-muted" style="font-size:.75rem"><?= htmlspecialchars($r['company_name']) ?></div>
                        </td>
                        <td style="font-size:.78rem;color:var(--sage);white-space:nowrap"><?= date('d/m/Y', strtotime($r['registered_at'])) ?></td>
                        <td><span class="badge" style="<?= $sc[0] ?>"><?= $sc[1] ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Right: Stats + Top Companies -->
    <div class="col-xl-5 d-flex flex-column gap-4">
        <!-- Registration breakdown -->
        <div class="card">
            <div class="card-header"><i class="bi bi-pie-chart-fill me-2" style="color:var(--evergreen)"></i>Tình trạng đăng ký</div>
            <div class="card-body">
                <?php
                $total = max($stats['reg_total'], 1);
                $items = [
                    ['Đã duyệt',  $stats['reg_approved'], 'var(--evergreen)'],
                    ['Chờ duyệt', $stats['reg_pending'],  '#f59e0b'],
                    ['Từ chối',   $stats['reg_rejected'],  'var(--terracotta)'],
                ];
                foreach ($items as [$label, $count, $color]): ?>
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span style="font-size:.82rem;font-weight:500"><?= $label ?></span>
                        <span style="font-size:.82rem;font-weight:700;color:<?= $color ?>"><?= $count ?></span>
                    </div>
                    <div class="progress" style="height:7px;">
                        <div class="progress-bar" style="width:<?= round($count/$total*100) ?>%;background:<?= $color ?>"></div>
                    </div>
                </div>
                <?php endforeach; ?>
                <div class="d-flex justify-content-between mt-3 pt-2" style="border-top:1px solid var(--ivory)">
                    <span style="font-size:.8rem;color:var(--sage)">Tổng đăng ký</span>
                    <strong><?= $stats['reg_total'] ?></strong>
                </div>
            </div>
        </div>

        <!-- Top companies -->
        <div class="card">
            <div class="card-header"><i class="bi bi-building-fill me-2" style="color:var(--terracotta)"></i>Doanh nghiệp nổi bật</div>
            <div class="card-body p-0">
                <?php if (empty($top_companies)): ?>
                <div class="text-center py-4 text-muted small">Chưa có dữ liệu</div>
                <?php else: ?>
                <ul class="list-unstyled mb-0">
                <?php foreach ($top_companies as $i => $c):
                    $cols = ['var(--evergreen)','var(--terracotta)','var(--sage)','#8b5cf6','#0ea5e9'];
                    $col  = $cols[$i % count($cols)];
                ?>
                <li style="padding:12px 20px;border-bottom:1px solid var(--ivory);display:flex;align-items:center;gap:12px;">
                    <div style="width:32px;height:32px;border-radius:8px;background:<?= $col ?>20;color:<?= $col ?>;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.8rem;flex-shrink:0;"><?= $i+1 ?></div>
                    <div style="flex:1;min-width:0">
                        <div style="font-size:.84rem;font-weight:600;color:var(--ev-dark);white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= htmlspecialchars($c['company_name']) ?></div>
                        <div style="font-size:.73rem;color:var(--sage)"><?= $c['pos_count'] ?> vị trí · quota: <?= $c['total_quota'] ?></div>
                    </div>
                </li>
                <?php endforeach; ?>
                </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php endif; // end admin/lecturer ?>

<!-- ════════════════════════════════════════════════════════
     STUDENT DASHBOARD
     ════════════════════════════════════════════════════════ -->
<?php if ($role === 'student'): ?>
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="stat-card stat-ev">
            <i class="bi bi-clipboard-check-fill stat-icon"></i>
            <div class="stat-number"><?= $stats['my_regs'] ?></div>
            <div class="stat-label">Đăng ký của tôi</div>
            <a href="modules/registrations/list.php" class="stat-link">Xem chi tiết <i class="bi bi-arrow-right"></i></a>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card stat-terra">
            <i class="bi bi-briefcase-fill stat-icon"></i>
            <div class="stat-number"><?= $stats['my_approved'] ?></div>
            <div class="stat-label">Đang thực tập</div>
            <a href="modules/registrations/list.php?status=approved" class="stat-link">Xem <i class="bi bi-arrow-right"></i></a>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card stat-sage">
            <i class="bi bi-journal-richtext stat-icon"></i>
            <div class="stat-number"><?= $stats['my_journals'] ?></div>
            <div class="stat-label">Nhật ký đã nộp</div>
            <a href="modules/journals/list.php" class="stat-link">Xem nhật ký <i class="bi bi-arrow-right"></i></a>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-xl-7">
        <div class="card table-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-clipboard-list me-2" style="color:var(--terracotta)"></i>Đăng ký của tôi</span>
                <a href="modules/registrations/add.php" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>Đăng ký mới</a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($my_regs)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-inbox fs-1 d-block mb-2 opacity-25"></i>
                    Bạn chưa đăng ký thực tập nào.
                    <div class="mt-2"><a href="modules/positions/list.php" style="color:var(--evergreen);font-weight:600;">Tìm vị trí ngay →</a></div>
                </div>
                <?php else: ?>
                <table class="table mb-0">
                    <thead><tr><th>Vị trí</th><th>Doanh nghiệp</th><th>Ngày đăng ký</th><th>Trạng thái</th></tr></thead>
                    <tbody>
                    <?php foreach ($my_regs as $r):
                        $sc = match($r['status']) {
                            'pending'   => ['background:#fffbeb;color:#92400e', 'Chờ duyệt'],
                            'approved'  => ['background:#f0fdf4;color:#166534', 'Đã duyệt'],
                            'rejected'  => ['background:#fef2f2;color:#991b1b', 'Từ chối'],
                            'cancelled' => ['background:var(--ivory);color:var(--sage)', 'Đã hủy'],
                            default     => ['background:var(--ivory);color:var(--sage)', $r['status']]
                        };
                    ?>
                    <tr>
                        <td style="font-size:.875rem;font-weight:500"><?= htmlspecialchars($r['position_title']) ?></td>
                        <td style="font-size:.85rem;color:var(--sage)"><?= htmlspecialchars($r['company_name']) ?></td>
                        <td style="font-size:.78rem;color:var(--sage);white-space:nowrap"><?= date('d/m/Y', strtotime($r['registered_at'])) ?></td>
                        <td><span class="badge" style="<?= $sc[0] ?>"><?= $sc[1] ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-xl-5">
        <div class="card">
            <div class="card-header"><i class="bi bi-lightbulb-fill me-2" style="color:var(--terracotta)"></i>Hướng dẫn nhanh</div>
            <div class="card-body">
                <div style="font-size:.84rem;line-height:1.9;color:#374151;">
                    <div class="mb-2"><span style="display:inline-flex;width:24px;height:24px;background:var(--ev-light);border-radius:6px;align-items:center;justify-content:center;font-weight:700;color:var(--evergreen);font-size:.75rem;margin-right:8px;">1</span>Tìm vị trí thực tập phù hợp</div>
                    <div class="mb-2"><span style="display:inline-flex;width:24px;height:24px;background:var(--ev-light);border-radius:6px;align-items:center;justify-content:center;font-weight:700;color:var(--evergreen);font-size:.75rem;margin-right:8px;">2</span>Gửi đăng ký và chờ duyệt</div>
                    <div class="mb-2"><span style="display:inline-flex;width:24px;height:24px;background:var(--ev-light);border-radius:6px;align-items:center;justify-content:center;font-weight:700;color:var(--evergreen);font-size:.75rem;margin-right:8px;">3</span>Nộp nhật ký hàng tuần</div>
                    <div><span style="display:inline-flex;width:24px;height:24px;background:var(--ev-light);border-radius:6px;align-items:center;justify-content:center;font-weight:700;color:var(--evergreen);font-size:.75rem;margin-right:8px;">4</span>Xem điểm tổng hợp cuối kỳ</div>
                </div>
                <div class="mt-3">
                    <a href="modules/positions/list.php" class="btn btn-primary btn-sm w-100">
                        <i class="bi bi-search me-1"></i>Tìm vị trí thực tập
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; // end student ?>

<!-- ════════════════════════════════════════════════════════
     COMPANY DASHBOARD
     ════════════════════════════════════════════════════════ -->
<?php if ($role === 'company'): ?>
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="stat-card stat-ev">
            <i class="bi bi-briefcase-fill stat-icon"></i>
            <div class="stat-number"><?= $stats['my_positions'] ?></div>
            <div class="stat-label">Vị trí đã đăng</div>
            <a href="modules/positions/list.php" class="stat-link">Quản lý <i class="bi bi-arrow-right"></i></a>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card stat-terra">
            <i class="bi bi-people-fill stat-icon"></i>
            <div class="stat-number"><?= $stats['my_applicants'] ?></div>
            <div class="stat-label">Sinh viên đăng ký</div>
            <a href="modules/registrations/list.php" class="stat-link">Xem <i class="bi bi-arrow-right"></i></a>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card stat-sage">
            <i class="bi bi-star-fill stat-icon"></i>
            <div class="stat-number"><?= $conn->query("SELECT COUNT(*) AS c FROM company_evaluations")->fetch_assoc()['c'] ?></div>
            <div class="stat-label">Đánh giá đã gửi</div>
            <a href="modules/company_eval/list.php" class="stat-link">Xem <i class="bi bi-arrow-right"></i></a>
        </div>
    </div>
</div>

<div class="card table-card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-people-fill me-2" style="color:var(--terracotta)"></i>Sinh viên đăng ký gần đây</span>
        <a href="modules/positions/add.php" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>Đăng vị trí mới</a>
    </div>
    <div class="card-body p-0">
        <?php if (empty($company_applicants)): ?>
        <div class="text-center py-5 text-muted">
            <i class="bi bi-inbox fs-1 d-block mb-2 opacity-25"></i>Chưa có sinh viên đăng ký
        </div>
        <?php else: ?>
        <table class="table mb-0">
            <thead><tr><th>Sinh viên</th><th>Vị trí</th><th>Ngày đăng ký</th><th>Trạng thái</th></tr></thead>
            <tbody>
            <?php foreach ($company_applicants as $r):
                $sc = match($r['status']) {
                    'pending'   => ['background:#fffbeb;color:#92400e', 'Chờ duyệt'],
                    'approved'  => ['background:#f0fdf4;color:#166534', 'Đã duyệt'],
                    'rejected'  => ['background:#fef2f2;color:#991b1b', 'Từ chối'],
                    default     => ['background:var(--ivory);color:var(--sage)', $r['status']]
                };
                $color = 'var(--evergreen)';
                $ini   = strtoupper(mb_substr($r['student_name'], 0, 1));
            ?>
            <tr>
                <td>
                    <div class="d-flex align-items-center gap-2">
                        <div class="avatar-sm" style="background:var(--ev-light);color:var(--evergreen)"><?= $ini ?></div>
                        <div>
                            <div class="fw-semibold" style="font-size:.85rem"><?= htmlspecialchars($r['student_name']) ?></div>
                            <div class="text-muted" style="font-size:.75rem"><?= htmlspecialchars($r['student_code'] ?? '') ?></div>
                        </div>
                    </div>
                </td>
                <td style="font-size:.875rem"><?= htmlspecialchars($r['position_title']) ?></td>
                <td style="font-size:.78rem;color:var(--sage);white-space:nowrap"><?= date('d/m/Y', strtotime($r['registered_at'])) ?></td>
                <td><span class="badge" style="<?= $sc[0] ?>"><?= $sc[1] ?></span></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>
<?php endif; // end company ?>

<?php include 'includes/footer.php'; ?>
