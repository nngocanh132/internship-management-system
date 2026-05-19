<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';

// ---- Auto-calculate grade for a registration ----
if (isset($_GET['calculate'])) {
    $reg_id = (int)$_GET['calculate'];

    // Fetch both evaluations
    $ce = $conn->prepare("SELECT total_score FROM company_evaluations WHERE registration_id=?");
    $ce->bind_param('i', $reg_id);
    $ce->execute();
    $company_eval = $ce->get_result()->fetch_assoc();

    $le = $conn->prepare("SELECT total_score FROM lecturer_evaluations WHERE registration_id=?");
    $le->bind_param('i', $reg_id);
    $le->execute();
    $lecturer_eval = $le->get_result()->fetch_assoc();

    if (!$company_eval || !$lecturer_eval) {
        setFlash('error', 'Cần có đủ cả đánh giá DN và GV trước khi tính điểm.');
    } else {
        // BUSINESS RULE: Dual Evaluation — DN 60%, GV 40%
        $company_weight  = 0.60;
        $lecturer_weight = 0.40;
        $final = round(
            ($company_eval['total_score'] * $company_weight) +
            ($lecturer_eval['total_score'] * $lecturer_weight),
            2
        );

        // Letter grade
        $letter = match(true) {
            $final >= 9.0 => 'A+',
            $final >= 8.5 => 'A',
            $final >= 8.0 => 'B+',
            $final >= 7.0 => 'B',
            $final >= 6.5 => 'C+',
            $final >= 5.5 => 'C',
            $final >= 5.0 => 'D+',
            $final >= 4.0 => 'D',
            default       => 'F'
        };

        // Upsert final_grades
        $existing = $conn->prepare("SELECT grade_id FROM final_grades WHERE registration_id=?");
        $existing->bind_param('i', $reg_id);
        $existing->execute();
        $ex = $existing->get_result()->fetch_assoc();

        if ($ex) {
            $upd = $conn->prepare(
                "UPDATE final_grades
                 SET company_score=?, lecturer_score=?, company_weight=?, lecturer_weight=?,
                     final_score=?, letter_grade=?, status='finalized'
                 WHERE registration_id=?"
            );
            $upd->bind_param('ddddssi',
                $company_eval['total_score'], $lecturer_eval['total_score'],
                $company_weight, $lecturer_weight, $final, $letter, $reg_id
            );
            $upd->execute();
        } else {
            $ins = $conn->prepare(
                "INSERT INTO final_grades
                 (registration_id, company_score, lecturer_score, company_weight, lecturer_weight, final_score, letter_grade, status)
                 VALUES (?, ?, ?, ?, ?, ?, ?, 'finalized')"
            );
            $ins->bind_param('iddddds',
                $reg_id, $company_eval['total_score'], $lecturer_eval['total_score'],
                $company_weight, $lecturer_weight, $final, $letter
            );
            $ins->execute();
        }
        setFlash('success', "Đã tính điểm tổng hợp: <strong>$final ($letter)</strong>");
    }
    redirect('list.php');
}

// ---- Fetch all grades ----
$grades = $conn->query(
    "SELECT g.*, u.full_name AS student_name, u.student_code,
            p.title AS position_title, c.company_name
     FROM final_grades g
     JOIN internship_registrations r ON g.registration_id = r.registration_id
     JOIN users u ON r.student_id = u.user_id
     JOIN internship_positions p ON r.position_id = p.position_id
     JOIN companies c ON p.company_id = c.company_id
     ORDER BY g.final_score DESC"
)->fetch_all(MYSQLI_ASSOC);

// Registrations that have both evaluations but no grade yet
$pending_calc = $conn->query(
    "SELECT r.registration_id, u.full_name, u.student_code, p.title, c.company_name
     FROM internship_registrations r
     JOIN users u ON r.student_id = u.user_id
     JOIN internship_positions p ON r.position_id = p.position_id
     JOIN companies c ON p.company_id = c.company_id
     WHERE r.status = 'approved'
       AND r.registration_id IN (SELECT registration_id FROM company_evaluations)
       AND r.registration_id IN (SELECT registration_id FROM lecturer_evaluations)
       AND r.registration_id NOT IN (SELECT registration_id FROM final_grades)
     ORDER BY u.full_name"
)->fetch_all(MYSQLI_ASSOC);
?>
<?php include '../../includes/header.php'; ?>

<div class="page-header">
    <div>
        <h4><i class="bi bi-award-fill me-2" style="color:#ec4899"></i>Điểm Tổng hợp Kỳ Thực tập</h4>
        <div class="page-subtitle">Công thức: Điểm cuối = (Điểm DN × 60%) + (Điểm GV × 40%)</div>
    </div>
</div>

<?php showFlash(); ?>

<?php if (!empty($pending_calc)): ?>
<div class="alert alert-info mb-4">
    <div class="fw-semibold mb-2"><i class="bi bi-calculator-fill me-2"></i>Sẵn sàng tính điểm — <?= count($pending_calc) ?> sinh viên</div>
    <div class="d-flex flex-wrap gap-2">
        <?php foreach ($pending_calc as $pc): ?>
        <div style="background:#fff;border:1.5px solid #bfdbfe;border-radius:10px;padding:10px 14px;display:flex;align-items:center;gap:10px;">
            <div>
                <div style="font-size:.85rem;font-weight:600;color:#1e293b">
                    <?= htmlspecialchars($pc['full_name']) ?>
                    <?= $pc['student_code'] ? '<span style="color:#94a3b8;font-weight:400">(' . $pc['student_code'] . ')</span>' : '' ?>
                </div>
                <div style="font-size:.75rem;color:#64748b"><?= htmlspecialchars($pc['title']) ?> @ <?= htmlspecialchars($pc['company_name']) ?></div>
            </div>
            <a href="list.php?calculate=<?= $pc['registration_id'] ?>"
               class="btn btn-success btn-sm ms-2"
               onclick="return confirm('Tính điểm tổng hợp? (DN 60% + GV 40%)')">
                <i class="bi bi-calculator me-1"></i>Tính
            </a>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<div class="card table-card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-table me-2"></i>Bảng điểm tổng hợp</span>
        <span class="badge" style="background:#fce7f3;color:#9d174d;font-size:.78rem;"><?= count($grades) ?> sinh viên</span>
    </div>
    <div class="card-body p-0">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Sinh viên</th>
                    <th>Vị trí / DN</th>
                    <th class="text-center">Điểm DN<br><small style="font-weight:400;color:#94a3b8">(60%)</small></th>
                    <th class="text-center">Điểm GV<br><small style="font-weight:400;color:#94a3b8">(40%)</small></th>
                    <th class="text-center">Điểm cuối</th>
                    <th class="text-center">Xếp loại</th>
                    <th class="text-center">Trạng thái</th>
                    <th>Ngày tính</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($grades)): ?>
                <tr><td colspan="9" class="text-center py-5">
                    <i class="bi bi-award fs-1 d-block mb-2 text-muted opacity-25"></i>
                    <span class="text-muted">Chưa có điểm nào được tính</span>
                </td></tr>
            <?php else: ?>
                <?php foreach ($grades as $i => $g):
                    $letterStyle = match(true) {
                        in_array($g['letter_grade'], ['A+','A']) => 'background:#dcfce7;color:#166534',
                        in_array($g['letter_grade'], ['B+','B']) => 'background:#dbeafe;color:#1e40af',
                        in_array($g['letter_grade'], ['C+','C']) => 'background:#fef3c7;color:#92400e',
                        in_array($g['letter_grade'], ['D+','D']) => 'background:#f1f5f9;color:#475569',
                        default => 'background:#fee2e2;color:#991b1b'
                    };
                    $scoreColor = $g['final_score'] >= 5 ? '#166534' : '#991b1b';
                    $colors = ['#6366f1','#10b981','#f59e0b','#ef4444','#8b5cf6','#14b8a6'];
                    $color  = $colors[crc32($g['student_name']) % count($colors)];
                    $initials = strtoupper(mb_substr($g['student_name'], 0, 1));
                ?>
                <tr>
                    <td style="color:#94a3b8;font-size:.8rem"><?= $i + 1 ?></td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="avatar-sm" style="background:<?= $color ?>20;color:<?= $color ?>">
                                <?= $initials ?>
                            </div>
                            <div>
                                <div class="fw-semibold" style="font-size:.875rem"><?= htmlspecialchars($g['student_name']) ?></div>
                                <div class="text-muted" style="font-size:.75rem"><?= htmlspecialchars($g['student_code'] ?? '') ?></div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div style="font-size:.875rem;font-weight:500"><?= htmlspecialchars($g['position_title']) ?></div>
                        <div class="text-muted" style="font-size:.75rem"><?= htmlspecialchars($g['company_name']) ?></div>
                    </td>
                    <td class="text-center fw-semibold"><?= number_format($g['company_score'], 2) ?></td>
                    <td class="text-center fw-semibold"><?= number_format($g['lecturer_score'], 2) ?></td>
                    <td class="text-center">
                        <span style="font-size:1.3rem;font-weight:800;color:<?= $scoreColor ?>">
                            <?= number_format($g['final_score'], 2) ?>
                        </span>
                    </td>
                    <td class="text-center">
                        <span class="badge" style="<?= $letterStyle ?>;font-size:.85rem;padding:6px 12px;">
                            <?= $g['letter_grade'] ?>
                        </span>
                    </td>
                    <td class="text-center">
                        <span class="badge" style="<?= $g['status']==='finalized' ? 'background:#dcfce7;color:#166534' : 'background:#fef3c7;color:#92400e' ?>">
                            <?= $g['status']==='finalized' ? 'Đã chốt' : 'Chờ' ?>
                        </span>
                    </td>
                    <td style="font-size:.78rem;color:#94a3b8;white-space:nowrap">
                        <?= date('d/m/Y', strtotime($g['calculated_at'])) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
