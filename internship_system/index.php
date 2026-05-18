<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// ── Statistics ──────────────────────────────────────────────
$stats = [];
$stats['total_users']    = $conn->query("SELECT COUNT(*) AS c FROM users")->fetch_assoc()['c'];
$stats['students']       = $conn->query("SELECT COUNT(*) AS c FROM users WHERE role='student'")->fetch_assoc()['c'];
$stats['lecturers']      = $conn->query("SELECT COUNT(*) AS c FROM users WHERE role='lecturer'")->fetch_assoc()['c'];
$stats['companies']      = $conn->query("SELECT COUNT(*) AS c FROM companies WHERE status='active'")->fetch_assoc()['c'];
$stats['positions_open'] = $conn->query("SELECT COUNT(*) AS c FROM internship_positions WHERE status='open'")->fetch_assoc()['c'];
$stats['positions_full'] = $conn->query("SELECT COUNT(*) AS c FROM internship_positions WHERE status='full'")->fetch_assoc()['c'];
$stats['reg_total']      = $conn->query("SELECT COUNT(*) AS c FROM internship_registrations")->fetch_assoc()['c'];
$stats['reg_pending']    = $conn->query("SELECT COUNT(*) AS c FROM internship_registrations WHERE status='pending'")->fetch_assoc()['c'];
$stats['reg_approved']   = $conn->query("SELECT COUNT(*) AS c FROM internship_registrations WHERE status='approved'")->fetch_assoc()['c'];
$stats['reg_rejected']   = $conn->query("SELECT COUNT(*) AS c FROM internship_registrations WHERE status='rejected'")->fetch_assoc()['c'];
$stats['journals']       = $conn->query("SELECT COUNT(*) AS c FROM weekly_journals")->fetch_assoc()['c'];
$stats['assignments']    = $conn->query("SELECT COUNT(*) AS c FROM internship_assignments")->fetch_assoc()['c'];
$stats['grades']         = $conn->query("SELECT COUNT(*) AS c FROM final_grades WHERE status='finalized'")->fetch_assoc()['c'];
$stats['avg_grade']      = $conn->query("SELECT ROUND(AVG(final_score),2) AS c FROM final_grades WHERE status='finalized'")->fetch_assoc()['c'] ?? 0;

// At-risk students
$at_risk = $conn->query(
    "SELECT u.full_name, u.student_code, r.registration_id,
            p.title AS position_title, c.company_name,
            MAX(j.submitted_at) AS last_submission
     FROM internship_registrations r
     JOIN users u ON r.student_id = u.user_id
     JOIN internship_positions p ON r.position_id = p.position_id
     JOIN companies c ON p.company_id = c.company_id
     LEFT JOIN weekly_journals j ON r.registration_id = j.registration_id
     WHERE r.status = 'approved'
     GROUP BY r.registration_id
     HAVING last_submission IS NULL OR last_submission < DATE_SUB(NOW(), INTERVAL 7 DAY)
     LIMIT 5"
)->fetch_all(MYSQLI_ASSOC);

// Recent registrations
$recent_regs = $conn->query(
    "SELECT r.registration_id, r.status, r.registered_at,
            u.full_name AS student_name, u.student_code,
            p.title AS position_title, c.company_name
     FROM internship_registrations r
     JOIN users u ON r.student_id = u.user_id
     JOIN internship_positions p ON r.position_id = p.position_id
     JOIN companies c ON p.company_id = c.company_id
     ORDER BY r.registered_at DESC LIMIT 6"
)->fetch_all(MYSQLI_ASSOC);

// Top companies by positions
$top_companies = $conn->query(
    "SELECT c.company_name, COUNT(p.position_id) AS pos_count,
            SUM(p.filled) AS total_filled, SUM(p.quota) AS total_quota
     FROM companies c
     LEFT JOIN internship_positions p ON c.company_id = p.company_id
     WHERE c.status = 'active'
     GROUP BY c.company_id
     ORDER BY pos_count DESC LIMIT 5"
)->fetch_all(MYSQLI_ASSOC);

// Pending grades (have both evals, no grade yet)
$pending_grades = $conn->query(
    "SELECT COUNT(*) AS c FROM internship_registrations r
     WHERE r.status = 'approved'
       AND r.registration_id IN (SELECT registration_id FROM company_evaluations)
       AND r.registration_id IN (SELECT registration_id FROM lecturer_evaluations)
       AND r.registration_id NOT IN (SELECT registration_id FROM final_grades)"
)->fetch_assoc()['c'];
?>
<?php include 'includes/header.php'; ?>

<!-- ── Page Header ── -->
<div class="page-header">
    <div>
        <h4><i class="bi bi-grid-fill me-2" style="color:#6366f1"></i>Dashboard</h4>
        <div class="page-subtitle">Tổng quan hệ thống quản lý thực tập · <?= date('l, d/m/Y') ?></div>
    </div>
    <div class="d-flex gap-2">
        <a href="modules/registrations/add.php" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i>Đăng ký mới
        </a>
        <a href="modules/grades/list.php" class="btn btn-sm" style="background:#f1f5f9;color:#475569;border:none;">
            <i class="bi bi-calculator me-1"></i>Tính điểm
        </a>
    </div>
</div>

<!-- ── Alert: At-risk ── -->
<?php if (!empty($at_risk)): ?>
<div class="alert alert-warning d-flex align-items-start gap-3 mb-4">
    <div style="width:36px;height:36px;background:#fef3c7;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
        <i class="bi bi-exclamation-triangle-fill text-warning"></i>
    </div>
    <div>
        <div class="fw-semibold mb-1">Cảnh báo Compliance — <?= count($at_risk) ?> sinh viên chưa nộp nhật ký trong 7 ngày</div>
        <div class="d-flex flex-wrap gap-2 mt-1">
            <?php foreach ($at_risk as $ar): ?>
            <span class="badge" style="background:#fef3c7;color:#92400e;font-weight:600;font-size:.75rem;padding:5px 10px;border-radius:6px;">
                <i class="bi bi-person me-1"></i><?= htmlspecialchars($ar['full_name']) ?>
                <?= $ar['student_code'] ? '(' . $ar['student_code'] . ')' : '' ?>
            </span>
            <?php endforeach; ?>
        </div>
        <a href="modules/journals/list.php" class="small fw-semibold mt-2 d-inline-block" style="color:#d97706;">
            Xem chi tiết nhật ký →
        </a>
    </div>
</div>
<?php endif; ?>

<?php if ($pending_grades > 0): ?>
<div class="alert alert-info d-flex align-items-center gap-3 mb-4">
    <i class="bi bi-calculator-fill fs-5" style="color:#3b82f6;flex-shrink:0;"></i>
    <div>
        <strong><?= $pending_grades ?> sinh viên</strong> đã có đủ đánh giá DN + GV, sẵn sàng tính điểm tổng hợp.
        <a href="modules/grades/list.php" class="fw-semibold ms-2" style="color:#1d4ed8;">Tính ngay →</a>
    </div>
</div>
<?php endif; ?>

<!-- ── Stat Cards Row 1 ── -->
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="stat-card stat-blue">
            <i class="bi bi-people-fill stat-icon"></i>
            <div class="stat-number"><?= $stats['students'] ?></div>
            <div class="stat-label">Sinh viên</div>
            <a href="modules/users/list.php?role=student" class="stat-link">
                Xem danh sách <i class="bi bi-arrow-right"></i>
            </a>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="stat-card stat-green">
            <i class="bi bi-building-fill stat-icon"></i>
            <div class="stat-number"><?= $stats['companies'] ?></div>
            <div class="stat-label">Doanh nghiệp đối tác</div>
            <a href="modules/companies/list.php" class="stat-link">
                Xem danh sách <i class="bi bi-arrow-right"></i>
            </a>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="stat-card stat-purple">
            <i class="bi bi-briefcase-fill stat-icon"></i>
            <div class="stat-number"><?= $stats['positions_open'] ?></div>
            <div class="stat-label">Vị trí đang mở</div>
            <a href="modules/positions/list.php?status=open" class="stat-link">
                Xem danh sách <i class="bi bi-arrow-right"></i>
            </a>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="stat-card stat-orange">
            <i class="bi bi-hourglass-split stat-icon"></i>
            <div class="stat-number"><?= $stats['reg_pending'] ?></div>
            <div class="stat-label">Đăng ký chờ duyệt</div>
            <a href="modules/registrations/list.php?status=pending" class="stat-link">
                Xem & duyệt <i class="bi bi-arrow-right"></i>
            </a>
        </div>
    </div>
</div>

<!-- ── Stat Cards Row 2 ── -->
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="stat-card stat-teal">
            <i class="bi bi-clipboard-check-fill stat-icon"></i>
            <div class="stat-number"><?= $stats['reg_approved'] ?></div>
            <div class="stat-label">Đang thực tập</div>
            <a href="modules/registrations/list.php?status=approved" class="stat-link">
                Xem danh sách <i class="bi bi-arrow-right"></i>
            </a>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="stat-card stat-blue" style="background:linear-gradient(135deg,#0ea5e9,#0284c7)">
            <i class="bi bi-journal-richtext stat-icon"></i>
            <div class="stat-number"><?= $stats['journals'] ?></div>
            <div class="stat-label">Nhật ký đã nộp</div>
            <a href="modules/journals/list.php" class="stat-link">
                Xem nhật ký <i class="bi bi-arrow-right"></i>
            </a>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="stat-card stat-green" style="background:linear-gradient(135deg,#22c55e,#16a34a)">
            <i class="bi bi-award-fill stat-icon"></i>
            <div class="stat-number"><?= $stats['grades'] ?></div>
            <div class="stat-label">Điểm đã chốt</div>
            <a href="modules/grades/list.php" class="stat-link">
                Xem điểm <i class="bi bi-arrow-right"></i>
            </a>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="stat-card stat-purple" style="background:linear-gradient(135deg,#ec4899,#db2777)">
            <i class="bi bi-graph-up stat-icon"></i>
            <div class="stat-number"><?= $stats['avg_grade'] ?: '—' ?></div>
            <div class="stat-label">Điểm TB tổng hợp</div>
            <a href="modules/grades/list.php" class="stat-link">
                Chi tiết <i class="bi bi-arrow-right"></i>
            </a>
        </div>
    </div>
</div>

<!-- ── Main Content Grid ── -->
<div class="row g-4">

    <!-- Left: Recent Registrations -->
    <div class="col-xl-7">
        <div class="card table-card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-clock-history me-2 text-primary"></i>Đăng ký Gần đây</span>
                <a href="modules/registrations/list.php" class="btn btn-sm" style="background:#f1f5f9;color:#6366f1;font-size:.78rem;border:none;">
                    Xem tất cả <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($recent_regs)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-inbox fs-1 d-block mb-2 opacity-25"></i>
                    Chưa có đăng ký nào
                </div>
                <?php else: ?>
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>Sinh viên</th>
                            <th>Vị trí / Doanh nghiệp</th>
                            <th>Ngày đăng ký</th>
                            <th>Trạng thái</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($recent_regs as $r):
                        $sc = match($r['status']) {
                            'pending'   => ['warning','Chờ duyệt'],
                            'approved'  => ['success','Đã duyệt'],
                            'rejected'  => ['danger','Từ chối'],
                            'cancelled' => ['secondary','Đã hủy'],
                            default     => ['secondary',$r['status']]
                        };
                        $initials = strtoupper(mb_substr($r['student_name'], 0, 1));
                        $colors   = ['#6366f1','#10b981','#f59e0b','#ef4444','#8b5cf6','#14b8a6'];
                        $color    = $colors[crc32($r['student_name']) % count($colors)];
                    ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="avatar-sm" style="background:<?= $color ?>20;color:<?= $color ?>">
                                    <?= $initials ?>
                                </div>
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
                        <td style="font-size:.78rem;color:#94a3b8;white-space:nowrap">
                            <?= date('d/m/Y', strtotime($r['registered_at'])) ?>
                        </td>
                        <td>
                            <span class="badge bg-<?= $sc[0] ?>"><?= $sc[1] ?></span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Right Column -->
    <div class="col-xl-5 d-flex flex-column gap-4">

        <!-- Registration Status Breakdown -->
        <div class="card">
            <div class="card-header">
                <i class="bi bi-pie-chart-fill me-2 text-primary"></i>Tình trạng Đăng ký
            </div>
            <div class="card-body">
                <?php
                $total = max($stats['reg_total'], 1);
                $items = [
                    ['Đã duyệt',   $stats['reg_approved'], '#10b981'],
                    ['Chờ duyệt',  $stats['reg_pending'],  '#f59e0b'],
                    ['Từ chối',    $stats['reg_rejected'],  '#ef4444'],
                ];
                ?>
                <?php foreach ($items as [$label, $count, $color]): ?>
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span style="font-size:.82rem;font-weight:500;color:#374151"><?= $label ?></span>
                        <span style="font-size:.82rem;font-weight:700;color:<?= $color ?>"><?= $count ?></span>
                    </div>
                    <div class="progress" style="height:7px;">
                        <div class="progress-bar" style="width:<?= round($count/$total*100) ?>%;background:<?= $color ?>"></div>
                    </div>
                </div>
                <?php endforeach; ?>
                <div class="d-flex justify-content-between mt-3 pt-2" style="border-top:1px solid #f1f5f9">
                    <span style="font-size:.8rem;color:#94a3b8">Tổng đăng ký</span>
                    <strong style="font-size:.9rem"><?= $stats['reg_total'] ?></strong>
                </div>
            </div>
        </div>

        <!-- Top Companies -->
        <div class="card">
            <div class="card-header">
                <i class="bi bi-building-fill me-2 text-success"></i>Doanh nghiệp Nổi bật
            </div>
            <div class="card-body p-0">
                <?php if (empty($top_companies)): ?>
                <div class="text-center py-4 text-muted small">Chưa có dữ liệu</div>
                <?php else: ?>
                <ul class="list-unstyled mb-0">
                <?php foreach ($top_companies as $i => $c):
                    $pct = $c['total_quota'] > 0 ? round($c['total_filled']/$c['total_quota']*100) : 0;
                    $colors2 = ['#6366f1','#10b981','#f59e0b','#ef4444','#8b5cf6'];
                    $col = $colors2[$i % count($colors2)];
                ?>
                <li style="padding:12px 20px;border-bottom:1px solid #f8fafc;display:flex;align-items:center;gap:12px;">
                    <div style="width:32px;height:32px;border-radius:8px;background:<?= $col ?>20;color:<?= $col ?>;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.8rem;flex-shrink:0;">
                        <?= $i+1 ?>
                    </div>
                    <div style="flex:1;min-width:0">
                        <div style="font-size:.84rem;font-weight:600;color:#1e293b;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                            <?= htmlspecialchars($c['company_name']) ?>
                        </div>
                        <div style="font-size:.73rem;color:#94a3b8"><?= $c['pos_count'] ?> vị trí · <?= $c['total_filled'] ?>/<?= $c['total_quota'] ?> chỗ</div>
                    </div>
                    <div style="font-size:.78rem;font-weight:700;color:<?= $col ?>;flex-shrink:0"><?= $pct ?>%</div>
                </li>
                <?php endforeach; ?>
                </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- ── Quick Actions ── -->
<div class="section-divider"></div>
<div class="mb-3">
    <div style="font-size:.8rem;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:#94a3b8;margin-bottom:14px;">
        Truy cập nhanh
    </div>
    <div class="row g-3">
        <?php
        $quick_actions = [
            ['modules/users/add.php',        'bi-person-plus-fill',    '#6366f1', 'Thêm người dùng',    'Tạo tài khoản mới'],
            ['modules/companies/add.php',     'bi-building-add',        '#10b981', 'Thêm doanh nghiệp',  'Đối tác mới'],
            ['modules/positions/add.php',     'bi-briefcase-fill',      '#8b5cf6', 'Thêm vị trí',        'Mở vị trí thực tập'],
            ['modules/registrations/add.php', 'bi-clipboard-plus-fill', '#f59e0b', 'Đăng ký thực tập',   'Cho sinh viên'],
            ['modules/journals/add.php',      'bi-journal-plus',        '#0ea5e9', 'Nộp nhật ký',        'Cập nhật tiến độ'],
            ['modules/grades/list.php',       'bi-calculator-fill',     '#ec4899', 'Tính điểm',          'Tổng hợp cuối kỳ'],
        ];
        foreach ($quick_actions as [$url, $icon, $color, $title, $sub]):
        ?>
        <div class="col-xl-2 col-md-4 col-6">
            <a href="<?= $url ?>" class="quick-action-card flex-column text-center" style="padding:20px 12px;">
                <div class="qa-icon mx-auto mb-2" style="background:<?= $color ?>18;color:<?= $color ?>">
                    <i class="bi <?= $icon ?>"></i>
                </div>
                <div class="qa-title"><?= $title ?></div>
                <div class="qa-sub"><?= $sub ?></div>
            </a>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- ── System Summary ── -->
<div class="row g-3 mt-1">
    <div class="col-md-4">
        <div class="card" style="border-left:4px solid #6366f1;">
            <div class="card-body py-3 px-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div style="font-size:.75rem;color:#94a3b8;font-weight:600;text-transform:uppercase;letter-spacing:.5px">Tổng người dùng</div>
                        <div style="font-size:1.6rem;font-weight:800;color:#1e293b"><?= $stats['total_users'] ?></div>
                        <div style="font-size:.75rem;color:#64748b"><?= $stats['students'] ?> SV · <?= $stats['lecturers'] ?> GV</div>
                    </div>
                    <div style="width:48px;height:48px;background:#6366f120;border-radius:12px;display:flex;align-items:center;justify-content:center;color:#6366f1;font-size:1.4rem;">
                        <i class="bi bi-people-fill"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card" style="border-left:4px solid #10b981;">
            <div class="card-body py-3 px-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div style="font-size:.75rem;color:#94a3b8;font-weight:600;text-transform:uppercase;letter-spacing:.5px">Vị trí Thực tập</div>
                        <div style="font-size:1.6rem;font-weight:800;color:#1e293b"><?= $stats['positions_open'] + $stats['positions_full'] ?></div>
                        <div style="font-size:.75rem;color:#64748b"><?= $stats['positions_open'] ?> mở · <?= $stats['positions_full'] ?> đầy</div>
                    </div>
                    <div style="width:48px;height:48px;background:#10b98120;border-radius:12px;display:flex;align-items:center;justify-content:center;color:#10b981;font-size:1.4rem;">
                        <i class="bi bi-briefcase-fill"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card" style="border-left:4px solid #f59e0b;">
            <div class="card-body py-3 px-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div style="font-size:.75rem;color:#94a3b8;font-weight:600;text-transform:uppercase;letter-spacing:.5px">Phân công GVHD</div>
                        <div style="font-size:1.6rem;font-weight:800;color:#1e293b"><?= $stats['assignments'] ?></div>
                        <div style="font-size:.75rem;color:#64748b">/ <?= $stats['reg_approved'] ?> SV đang thực tập</div>
                    </div>
                    <div style="width:48px;height:48px;background:#f59e0b20;border-radius:12px;display:flex;align-items:center;justify-content:center;color:#f59e0b;font-size:1.4rem;">
                        <i class="bi bi-person-check-fill"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
