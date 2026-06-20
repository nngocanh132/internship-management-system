<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';
requireRole(['admin', 'lecturer']);

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) redirect('list.php');

$stmt = $conn->prepare("SELECT * FROM skills WHERE skill_id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$skill = $stmt->get_result()->fetch_assoc();
if (!$skill) { setFlash('error', 'Không tìm thấy kỹ năng.'); redirect('list.php'); }

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $skill_name  = sanitize($_POST['skill_name'] ?? '');
    $description = sanitize($_POST['description'] ?? '');

    if (empty($skill_name)) $errors[] = 'Tên kỹ năng không được để trống.';

    // BUSINESS RULE: Duplicate name check (exclude current)
    if (empty($errors)) {
        $chk = $conn->prepare("SELECT skill_id FROM skills WHERE skill_name = ? AND skill_id != ?");
        $chk->bind_param('si', $skill_name, $id);
        $chk->execute();
        if ($chk->get_result()->num_rows > 0) {
            $errors[] = 'Tên kỹ năng đã được sử dụng.';
        }
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("UPDATE skills SET skill_name=?, description=? WHERE skill_id=?");
        $stmt->bind_param('ssi', $skill_name, $description, $id);

        if ($stmt->execute()) {
            setFlash('success', "Đã cập nhật kỹ năng <strong>$skill_name</strong>.");
            redirect('list.php');
        } else {
            $errors[] = 'Lỗi khi cập nhật: ' . $conn->error;
        }
    }
    $skill = array_merge($skill, $_POST);
}
?>
<?php include '../../includes/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4><i class="bi bi-pencil-square me-2 text-warning"></i>Chỉnh sửa Kỹ năng</h4>
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
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Tên kỹ năng <span class="text-danger">*</span></label>
                    <input type="text" name="skill_name" class="form-control"
                           value="<?= htmlspecialchars($skill['skill_name']) ?>" required>
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Mô tả</label>
                    <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($skill['description'] ?? '') ?></textarea>
                </div>
            </div>
            <hr>
            <button type="submit" class="btn btn-warning">
                <i class="bi bi-save me-1"></i>Cập nhật
            </button>
            <a href="list.php" class="btn btn-secondary ms-2">Hủy</a>
        </form>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
