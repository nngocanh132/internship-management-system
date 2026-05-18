<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM company_evaluations WHERE eval_id=?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    setFlash('success', 'Đã xóa đánh giá.');
    redirect('list.php');
}

$evals = $conn->query(
    "SELECT e.*, u.full_name AS student_name, u.student_code,
            p.title AS position_title, c.company_name
     FROM company_evaluations e
     JOIN internship_registrations r ON e.registration_id = r.registration_id
     JOIN users u ON r.student_id = u.user_id
     JOIN internship_positions p ON r.position_id = p.position_id
     JOIN companies c ON p.company_id = c.company_id
     ORDER BY e.evaluated_at DESC"
)->fetch_all(MYSQLI_ASSOC);
?>
<?php include '../../includes/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4><i class="bi bi-star me-2 text-primary"></i>Đánh giá từ Doanh nghiệp</h4>
    <a href="add.php" class="btn btn-primary btn-sm"><i class="bi bi-plus-circle me-1"></i>Thêm đánh giá</a>
</div>

<?php showFlash(); ?>

<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover table-bordered mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Sinh viên</th>
                    <th>Vị trí / DN</th>
                    <th>Thái độ</th>
                    <th>Kỹ năng</th>
                    <th>Kết quả</th>
                    <th>Tổng điểm</th>
                    <th>Ngày đánh giá</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($evals)): ?>
                <tr><td colspan="9" class="text-center text-muted py-4">Chưa có đánh giá nào</td></tr>
            <?php else: ?>
                <?php foreach ($evals as $i => $e): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td>
                        <?= htmlspecialchars($e['student_name']) ?>
                        <?= $e['student_code'] ? '<br><small class="text-muted">' . $e['student_code'] . '</small>' : '' ?>
                    </td>
                    <td>
                        <?= htmlspecialchars($e['position_title']) ?>
                        <br><small class="text-muted"><?= htmlspecialchars($e['company_name']) ?></small>
                    </td>
                    <td class="text-center"><?= number_format($e['attitude_score'], 1) ?></td>
                    <td class="text-center"><?= number_format($e['skill_score'], 1) ?></td>
                    <td class="text-center"><?= number_format($e['result_score'], 1) ?></td>
                    <td class="text-center">
                        <strong class="text-<?= $e['total_score'] >= 5 ? 'success' : 'danger' ?>">
                            <?= number_format($e['total_score'], 2) ?>
                        </strong>
                    </td>
                    <td><small><?= $e['evaluated_at'] ?></small></td>
                    <td>
                        <a href="edit.php?id=<?= $e['eval_id'] ?>" class="btn btn-warning btn-sm">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <a href="list.php?delete=<?= $e['eval_id'] ?>"
                           class="btn btn-danger btn-sm"
                           onclick="return confirm('Xóa đánh giá này?')">
                            <i class="bi bi-trash"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
