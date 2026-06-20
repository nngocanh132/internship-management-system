<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';

// ---- Auto-calculate grade for an assignment ----
if (isset($_GET['calculate'])) {
    $asgn_id = (int)$_GET['calculate'];

    $ce = $conn->prepare("SELECT score FROM company_evaluations WHERE assignment_id=? LIMIT 1");
    $ce->bind_param('i', $asgn_id);
    $ce->execute();
    $company_eval = $ce->get_result()->fetch_assoc();

    $le = $conn->prepare("SELECT score FROM lecturer_evaluations WHERE assignment_id=? LIMIT 1");
    $le->bind_param('i', $asgn_id);
    $le->execute();
    $lecturer_eval = $le->get_result()->fetch_assoc();

    if (!$company_eval || !$lecturer_eval) {
        setFlash('error', 'Cần có đủ cả đánh giá DN và GV trước khi tính điểm.');
    } else {
        // DN 60%, GV 40%
        $final = round(($company_eval['score'] * 0.60) + ($lecturer_eval['score'] * 0.40), 2);
        $result = $final >= 5.0 ? 'pass' : 'fail';

        $existing = $conn->prepare("SELECT final_id FROM final_grades WHERE assignment_id=?");
        $existing->bind_param('i', $asgn_id);
        $existing->execute();
        $ex = $existing->get_result()->fetch_assoc();

        if ($ex) {
            $upd = $conn->prepare(
                "UPDATE final_grades SET company_score=?, lecturer_score=?, final_score=?, result_status=?, calculated_at=NOW()
                 WHERE assignment_id=?"
            );
            $upd->bind_param('dddsi', $company_eval['score'], $lecturer_eval['score'], $final, $result, $asgn_id);
            $upd->execute();
        } else {
            $ins = $conn->prepare(
                "INSERT INTO final_grades (assignment_id, company_score, lecturer_score, final_score, result_status)
                 VALUES (?, ?, ?, ?, ?)"
            );
            $ins->bind_param('iddds', $asgn_id, $company_eval['score'], $lecturer_eval['score'], $final, $result);
            $ins->execute();
        }
        setFlash('success', "Đã tính điểm tổng hợp: <strong>$final</strong> — " . ($result === 'pass' ? 'Đạt' : 'Không đạt'));
    }
    redirect('list.php');
}

// ---- Fetch all grades ----
$grades = $conn->query(
    "SELECT g.*, u.full_name AS student_name, u.student_code,
            p.title AS position_title, c.name AS company_name
     FROM final_grades g
     JOIN internship_assignments a ON g.assignment_id = a.assignment_id
     JOIN internship_registrations r ON a.registration_id = r.registration_id
     JOIN users u ON r.student_id = u.user_id
     JOIN internship_positions p ON r.position_id = p.position_id
     JOIN companies c ON p.company_id = c.company_id
     ORDER BY g.final_score DESC"
)->fetch_all(MYSQLI_ASSOC);

// Assignments that have both evaluations but no grade yet
$pending_calc = $conn->query(
    "SELECT a.assignment_id, u.full_name, u.student_code, p.title, c.name AS company_name
     FROM internship_assignments a
     JOIN internship_registrations r ON a.registration_id = r.registration_id
     JOIN users u ON r.student_id = u.user_id
     JOIN internship_positions p ON r.position_id = p.position_id
     JOIN companies c ON p.company_id = c.company_id
     WHERE a.assignment_id IN (SELECT assignment_id FROM company_evaluations)
       AND a.assignment_id IN (SELECT assignment_id FROM lecturer_evaluations)
       AND a.assignment_id NOT IN (SELECT assignment_id FROM final_grades)
     ORDER BY u.full_name"
)->fetch_all(MYSQLI_ASSOC);
?>
<?php include '../../includes/header.php'; ?>

<div class="page-header">
    <div>
        <h4><i class="bi bi-award-fill me-2" style="color:var(--terracotta)"></i>Điểm Tổng hợp Kỳ Thực tập</h4>
        <div class="page-subtitle">Công thức: Điểm cuối = (Điểm DN × 60%) + (Điểm GV × 40%)</div>
    </div>
</div>

<?php showFlash(); ?>

<?php if (!empty($pending_calc)): ?>
<div class="alert alert-info mb-4">
    <div class="fw-semibold mb-2"><i class="bi bi-calculator-fill me-2"></i>Sẵn sàng tính điểm — <?= count($pending_calc) ?> sinh viên</div>
    <div class="d-flex flex-wrap gap-2">
        <?php foreach ($pending_calc as $pc): ?>
        <div style="background:#fff;border:1.5px solid var(--blush);border-radius:10px;padding:10px 14px;display:flex;align-items:center;gap:10px;">
            <div>
                <div style="font-size:.85rem;font-weight:600;color:var(--ev-dark)">
                    <?= htmlspecialchars($pc['full_name']) ?>
                    <?= $pc['student_code'] ? '<span style="color:var(--sage);font-weight:400">(' . $pc['student_code'] . ')</span>' : '' ?>
                </div>
                <div style="font-size:.75rem;color:var(--sage)"><?= htmlspecialchars($pc['title']) ?> @ <?= htmlspecialchars($pc['company_name']) ?></div>
            </div>
            <a href="list.php?calculate=<?= $pc['assignment_id'] ?>"
               class="btn btn-primary btn-sm ms-2"
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
        <span class="badge" style="background:var(--ev-light);color:var(--evergreen);font-size:.78rem;"><?= count($grades) ?> sinh viên</span>
    </div>
    <div class="card-body p-0">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Sinh viên</th>
                    <th>Vị trí / DN</th>
                    <th class="text-center">Điểm DN<br><small style="font-weight:400;color:var(--sage)">(60%)</small></th>
                    <th class="text-center">Điểm GV<br><small style="font-weight:400;color:var(--sage)">(40%)</small></th>
                    <th class="text-center">Điểm cuối</th>
                    <th class="text-center">Kết quả</th>
                    <th>Ngày tính</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($grades)): ?>
                <tr><td colspan="8" class="text-center py-5">
                    <i class="bi bi-award fs-1 d-block mb-2 text-muted opacity-25"></i>
                    <span class="text-muted">Chưa có điểm nào được tính</span>
                </td></tr>
            <?php else: ?>
                <?php foreach ($grades as $i => $g):
                    $scoreColor = ($g['final_score'] ?? 0) >= 5 ? 'var(--evergreen)' : 'var(--terracotta)';
                    $colors = ['var(--evergreen)','var(--terracotta)','var(--sage)','#8b5cf6','#0ea5e9'];
                    $color  = $colors[crc32($g['student_name']) % count($colors)];
                    $ini    = strtoupper(mb_substr($g['student_name'], 0, 1));
                ?>
                <tr>
                    <td style="color:var(--sage);font-size:.8rem"><?= $i + 1 ?></td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="avatar-sm" style="background:<?= $color ?>20;color:<?= $color ?>"><?= $ini ?></div>
                            <div>
                                <div class="fw-semibold" style="font-size:.875rem"><?= htmlspecialchars($g['student_name']) ?></div>
                                <div style="font-size:.75rem;color:var(--sage)"><?= htmlspecialchars($g['student_code'] ?? '') ?></div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div style="font-size:.875rem;font-weight:500"><?= htmlspecialchars($g['position_title']) ?></div>
                        <div style="font-size:.75rem;color:var(--sage)"><?= htmlspecialchars($g['company_name']) ?></div>
                    </td>
                    <td class="text-center fw-semibold"><?= number_format($g['company_score'] ?? 0, 2) ?></td>
                    <td class="text-center fw-semibold"><?= number_format($g['lecturer_score'] ?? 0, 2) ?></td>
                    <td class="text-center">
                        <span style="font-size:1.3rem;font-weight:800;color:<?= $scoreColor ?>">
                            <?= number_format($g['final_score'] ?? 0, 2) ?>
                        </span>
                    </td>
                    <td class="text-center">
                        <span class="badge" style="<?= $g['result_status']==='pass' ? 'background:#f0fdf4;color:#166534' : 'background:#fef2f2;color:#991b1b' ?>">
                            <?= $g['result_status']==='pass' ? 'Đạt' : 'Không đạt' ?>
                        </span>
                    </td>
                    <td style="font-size:.78rem;color:var(--sage);white-space:nowrap">
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
