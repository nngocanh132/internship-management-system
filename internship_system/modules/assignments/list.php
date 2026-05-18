<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM internship_assignments WHERE assignment_id=?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    setFlash('success', 'Đã xóa phân công.');
    redirect('list.php');
}

$assignments = $conn->query(
    "SELECT a.*, u_lec.full_name AS lecturer_name,
            u_stu.full_name AS student_name, u_stu.student_code,
            p.title AS position_title, c.company_name
     FROM internship_assignments a
     JOIN users u_lec ON a.lecturer_id = u_lec.user_id
     JOIN internship_registrations r ON a.registration_id = r.registration_id
     JOIN users u_stu ON r.student_id = u_stu.user_id
     JOIN internship_positions p ON r.position_id = p.position_id
     JOIN companies c ON p.company_id = c.company_id
     ORDER BY a.assigned_at DESC"
)->fetch_all(MYSQLI_ASSOC);
?>
<?php include '../../includes/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4><i class="bi bi-person-check me-2 text-primary"></i>Phân công GVHD</h4>
    <a href="add.php" class="btn btn-primary btn-sm"><i class="bi bi-plus-circle me-1"></i>Thêm phân công</a>
</div>

<?php showFlash(); ?>

<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover table-bordered mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Sinh viên</th>
                    <th>Vị trí / Doanh nghiệp</th>
                    <th>GVHD</th>
                    <th>Ngày phân công</th>
                    <th>Ghi chú</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($assignments)): ?>
                <tr><td colspan="7" class="text-center text-muted py-4">Chưa có phân công nào</td></tr>
            <?php else: ?>
                <?php foreach ($assignments as $i => $a): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td>
                        <?= htmlspecialchars($a['student_name']) ?>
                        <?= $a['student_code'] ? '<br><small class="text-muted">' . $a['student_code'] . '</small>' : '' ?>
                    </td>
                    <td>
                        <?= htmlspecialchars($a['position_title']) ?>
                        <br><small class="text-muted"><?= htmlspecialchars($a['company_name']) ?></small>
                    </td>
                    <td><?= htmlspecialchars($a['lecturer_name']) ?></td>
                    <td><small><?= $a['assigned_at'] ?></small></td>
                    <td><?= htmlspecialchars($a['note'] ?? '—') ?></td>
                    <td>
                        <a href="edit.php?id=<?= $a['assignment_id'] ?>" class="btn btn-warning btn-sm">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <a href="list.php?delete=<?= $a['assignment_id'] ?>"
                           class="btn btn-danger btn-sm"
                           onclick="return confirm('Xóa phân công này?')">
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
