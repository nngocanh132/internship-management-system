<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) redirect('list.php');

$stmt = $conn->prepare("SELECT * FROM internship_assignments WHERE assignment_id=?");
$stmt->bind_param('i', $id);
$stmt->execute();
$assign = $stmt->get_result()->fetch_assoc();
if (!$assign) { setFlash('error', 'Không tìm thấy phân công.'); redirect('list.php'); }

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lecturer_id = (int)($_POST['lecturer_id'] ?? 0);
    $note        = sanitize($_POST['note'] ?? '');

    if ($lecturer_id <= 0) $errors[] = 'Vui lòng chọn giảng viên.';

    if (empty($errors)) {
        $stmt = $conn->prepare("UPDATE internship_assignments SET lecturer_id=?, note=? WHERE assignment_id=?");
        $stmt->bind_param('isi', $lecturer_id, $note, $id);
        if ($stmt->execute()) {
            setFlash('success', 'Đã cập nhật phân công.');
            redirect('list.php');
        } else {
            $errors[] = 'Lỗi: ' . $conn->error;
        }
    }
    $assign['lecturer_id'] = $lecturer_id;
    $assign['note']        = $note;
}

$lecturers = $conn->query(
    "SELECT user_id, full_name FROM users WHERE role='lecturer' AND status='active' ORDER BY full_name"
)->fetch_all(MYSQLI_ASSOC);
?>
<?php include '../../includes/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4><i class="bi bi-pencil-square me-2 text-warning"></i>Chỉnh sửa Phân công GVHD</h4>
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
                    <label class="form-label fw-semibold">Giảng viên hướng dẫn <span class="text-danger">*</span></label>
                    <select name="lecturer_id" class="form-select" required>
                        <?php foreach ($lecturers as $l): ?>
                        <option value="<?= $l['user_id'] ?>"
                            <?= $assign['lecturer_id']==$l['user_id'] ?'selected':'' ?>>
                            <?= htmlspecialchars($l['full_name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Ghi chú</label>
                    <textarea name="note" class="form-control" rows="2"><?= htmlspecialchars($assign['note'] ?? '') ?></textarea>
                </div>
            </div>
            <hr>
            <button type="submit" class="btn btn-warning"><i class="bi bi-save me-1"></i>Cập nhật</button>
            <a href="list.php" class="btn btn-secondary ms-2">Hủy</a>
        </form>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
