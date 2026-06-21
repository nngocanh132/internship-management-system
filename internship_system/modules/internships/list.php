<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM weekly_journals WHERE journal_id=?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    setFlash('success', 'Đã xóa nhật ký.');
    redirect('list.php');
}

$sql = "SELECT j.*, u.full_name AS student_name, u.student_code,
               p.title AS position_title, c.name AS company_name
        FROM weekly_journals j
        JOIN internship_assignments ia ON j.assignment_id = ia.assignment_id
        JOIN internship_registrations r ON ia.registration_id = r.registration_id
        JOIN users u ON r.student_id = u.user_id
        JOIN internship_positions p ON r.position_id = p.position_id
        JOIN companies c ON p.company_id = c.company_id
        ORDER BY j.submitted_at DESC";

$journals = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);

// Compliance check: students with no journal in last 7 days
$at_risk = $conn->query(
    "SELECT u.full_name, u.student_code, r.registration_id,
            MAX(j.submitted_at) AS last_submission
     FROM internship_registrations r
     JOIN users u ON r.student_id = u.user_id
     LEFT JOIN internship_assignments ia ON ia.registration_id = r.registration_id
     LEFT JOIN weekly_journals j ON j.assignment_id = ia.assignment_id
     WHERE r.status = 'approved'
     GROUP BY r.registration_id
     HAVING last_submission IS NULL OR last_submission < DATE_SUB(NOW(), INTERVAL 7 DAY)"
)->fetch_all(MYSQLI_ASSOC);
?>
<?php include '../../includes/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4><i class="bi bi-journal-text me-2 text-primary"></i>Nhật ký Thực tập Hàng tuần</h4>
    <a href="add.php" class="btn btn-primary btn-sm"><i class="bi bi-plus-circle me-1"></i>Thêm nhật ký</a>
</div>

<?php showFlash(); ?>

<?php if (!empty($at_risk)): ?>
<div class="alert alert-warning">
    <strong><i class="bi bi-exclamation-triangle me-1"></i>Cảnh báo Compliance:</strong>
    Các sinh viên sau chưa nộp nhật ký trong 7 ngày qua:
    <ul class="mb-0 mt-1">
        <?php foreach ($at_risk as $ar): ?>
        <li>
            <?= htmlspecialchars($ar['full_name']) ?>
            <?= $ar['student_code'] ? '(' . $ar['student_code'] . ')' : '' ?>
            — Lần cuối: <?= $ar['last_submission'] ?? 'Chưa nộp lần nào' ?>
        </li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover table-bordered mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Sinh viên</th>
                    <th>Vị trí / Doanh nghiệp</th>
                    <th>Tuần</th>
                    <th>Nội dung</th>
                    <th>Ngày nộp</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($journals)): ?>
                <tr><td colspan="7" class="text-center text-muted py-4">Chưa có nhật ký nào</td></tr>
            <?php else: ?>
                <?php foreach ($journals as $i => $j): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td>
                        <?= htmlspecialchars($j['student_name']) ?>
                        <?= $j['student_code'] ? '<br><small class="text-muted">' . $j['student_code'] . '</small>' : '' ?>
                    </td>
                    <td>
                        <?= htmlspecialchars($j['position_title']) ?>
                        <br><small class="text-muted"><?= htmlspecialchars($j['company_name']) ?></small>
                    </td>
                    <td class="text-center"><span class="badge bg-primary">Tuần <?= $j['week_number'] ?></span></td>
                    <td><?= htmlspecialchars(mb_substr($j['content'], 0, 80)) ?>...</td>
                    <td><small><?= $j['submitted_at'] ?></small></td>
                    <td>
                        <a href="edit.php?id=<?= $j['journal_id'] ?>" class="btn btn-warning btn-sm">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <a href="list.php?delete=<?= $j['journal_id'] ?>"
                           class="btn btn-danger btn-sm"
                           onclick="return confirm('Xóa nhật ký này?')">
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
