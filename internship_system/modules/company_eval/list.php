<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM company_evaluations WHERE evaluation_id=?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    setFlash('success', 'Đã xóa đánh giá.');
    redirect('list.php');
}

$evals = $conn->query(
    "SELECT e.*, u.full_name AS student_name, u.student_code,
            p.title AS position_title, c.name AS company_name
     FROM company_evaluations e
     JOIN internship_assignments a ON e.assignment_id = a.assignment_id
     JOIN internship_registrations r ON a.registration_id = r.registration_id
     JOIN users u ON r.student_id = u.user_id
     JOIN internship_positions p ON r.position_id = p.position_id
     JOIN companies c ON p.company_id = c.company_id
     ORDER BY e.evaluated_at DESC"
)->fetch_all(MYSQLI_ASSOC);
?>
<?php include '../../includes/header.php'; ?>

<div class="page-header">
    <div>
        <h4><i class="bi bi-star-fill me-2" style="color:var(--terracotta)"></i>Đánh giá từ Doanh nghiệp</h4>
        <div class="page-subtitle">Tổng: <?= count($evals) ?> đánh giá</div>
    </div>
    <a href="add.php" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>Thêm đánh giá</a>
</div>

<?php showFlash(); ?>

<div class="card table-card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-table me-2"></i>Danh sách đánh giá</span>
        <span class="badge" style="background:var(--ev-light);color:var(--evergreen);font-size:.78rem;"><?= count($evals) ?> kết quả</span>
    </div>
    <div class="card-body p-0">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Sinh viên</th>
                    <th>Vị trí / Doanh nghiệp</th>
                    <th>Người đánh giá</th>
                    <th class="text-center">Điểm</th>
                    <th>Nhận xét</th>
                    <th>Ngày đánh giá</th>
                    <th style="width:90px">Thao tác</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($evals)): ?>
                <tr><td colspan="8" class="text-center py-5">
                    <i class="bi bi-inbox fs-1 d-block mb-2 text-muted opacity-25"></i>
                    <span class="text-muted">Chưa có đánh giá nào</span>
                </td></tr>
            <?php else: ?>
                <?php foreach ($evals as $i => $e):
                    $colors = ['var(--evergreen)','var(--terracotta)','var(--sage)','#8b5cf6','#0ea5e9'];
                    $color  = $colors[crc32($e['student_name']) % count($colors)];
                    $ini    = strtoupper(mb_substr($e['student_name'], 0, 1));
                    $scoreColor = ($e['score'] ?? 0) >= 5 ? 'var(--evergreen)' : 'var(--terracotta)';
                ?>
                <tr>
                    <td style="color:var(--sage);font-size:.8rem"><?= $i + 1 ?></td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="avatar-sm" style="background:<?= $color ?>20;color:<?= $color ?>"><?= $ini ?></div>
                            <div>
                                <div class="fw-semibold" style="font-size:.875rem"><?= htmlspecialchars($e['student_name']) ?></div>
                                <div style="font-size:.75rem;color:var(--sage)"><?= htmlspecialchars($e['student_code'] ?? '') ?></div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div style="font-size:.875rem;font-weight:500"><?= htmlspecialchars($e['position_title']) ?></div>
                        <div style="font-size:.75rem;color:var(--sage)"><?= htmlspecialchars($e['company_name']) ?></div>
                    </td>
                    <td style="font-size:.85rem"><?= htmlspecialchars($e['evaluator_name'] ?? '—') ?></td>
                    <td class="text-center">
                        <span style="font-size:1.1rem;font-weight:800;color:<?= $scoreColor ?>">
                            <?= number_format($e['score'], 2) ?>
                        </span>
                    </td>
                    <td style="font-size:.82rem;color:#374151;max-width:200px">
                        <?= htmlspecialchars(mb_substr($e['feedback'] ?? '', 0, 80)) ?>
                        <?= strlen($e['feedback'] ?? '') > 80 ? '...' : '' ?>
                    </td>
                    <td style="font-size:.78rem;color:var(--sage);white-space:nowrap">
                        <?= date('d/m/Y', strtotime($e['evaluated_at'])) ?>
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="edit.php?id=<?= $e['evaluation_id'] ?>" class="btn btn-warning btn-sm">
                                <i class="bi bi-pencil-fill"></i>
                            </a>
                            <a href="list.php?delete=<?= $e['evaluation_id'] ?>"
                               class="btn btn-danger btn-sm"
                               onclick="return confirm('Xóa đánh giá này?')">
                                <i class="bi bi-trash-fill"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
