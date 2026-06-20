<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) redirect('list.php');

$stmt = $conn->prepare("SELECT * FROM weekly_journals WHERE journal_id=?");
$stmt->bind_param('i', $id);
$stmt->execute();
$journal = $stmt->get_result()->fetch_assoc();
if (!$journal) { setFlash('error', 'Không tìm thấy nhật ký.'); redirect('list.php'); }

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $content    = sanitize($_POST['content'] ?? '');
    $tasks_done = sanitize($_POST['tasks_done'] ?? '');
    $issues     = sanitize($_POST['issues'] ?? '');

    if (empty($content)) $errors[] = 'Nội dung không được để trống.';

    if (empty($errors)) {
        $stmt = $conn->prepare(
            "UPDATE weekly_journals SET content=?, tasks_done=?, issues=? WHERE journal_id=?"
        );
        $stmt->bind_param('sssi', $content, $tasks_done, $issues, $id);
        if ($stmt->execute()) {
            setFlash('success', 'Đã cập nhật nhật ký.');
            redirect('list.php');
        } else {
            $errors[] = 'Lỗi: ' . $conn->error;
        }
    }
    $journal = array_merge($journal, $_POST);
}
?>
<?php include '../../includes/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4><i class="bi bi-pencil-square me-2 text-warning"></i>Chỉnh sửa Nhật ký Tuần <?= $journal['week_number'] ?></h4>
    <a href="list.php" class="btn btn-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Quay lại</a>
</div>

<?php if ($errors): ?>
<div class="alert alert-danger">
    <ul class="mb-0"><?php foreach ($errors as $e): ?><li><?= $e ?></li><?php endforeach; ?></ul>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <form method="POST">
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label fw-semibold">Nội dung <span class="text-danger">*</span></label>
                    <textarea name="content" class="form-control" rows="4" required><?= htmlspecialchars($journal['content']) ?></textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Công việc đã hoàn thành</label>
                    <textarea name="tasks_done" class="form-control" rows="3"><?= htmlspecialchars($journal['tasks_done'] ?? '') ?></textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Vấn đề gặp phải</label>
                    <textarea name="issues" class="form-control" rows="3"><?= htmlspecialchars($journal['issues'] ?? '') ?></textarea>
                </div>
            </div>
            <hr>
            <button type="submit" class="btn btn-warning"><i class="bi bi-save me-1"></i>Cập nhật</button>
            <a href="list.php" class="btn btn-secondary ms-2">Hủy</a>
        </form>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
