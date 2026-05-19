<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) redirect('list.php');

$stmt = $conn->prepare("SELECT * FROM internship_registrations WHERE registration_id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$reg = $stmt->get_result()->fetch_assoc();
if (!$reg) { setFlash('error', 'Không tìm thấy đăng ký.'); redirect('list.php'); }

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $note   = sanitize($_POST['note'] ?? '');
    $status = sanitize($_POST['status'] ?? '');

    // BUSINESS RULE: Cannot change status of approved registration back to pending
    if ($reg['status'] === 'approved' && $status === 'pending') {
        $errors[] = 'Không thể chuyển đăng ký đã duyệt về trạng thái chờ.';
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("UPDATE internship_registrations SET note=?, status=? WHERE registration_id=?");
        $stmt->bind_param('ssi', $note, $status, $id);
        if ($stmt->execute()) {
            setFlash('success', 'Đã cập nhật đăng ký.');
            redirect('list.php');
        } else {
            $errors[] = 'Lỗi: ' . $conn->error;
        }
    }
    $reg['note']   = $note;
    $reg['status'] = $status;
}

// Fetch related info
$info = $conn->prepare(
    "SELECT u.full_name, u.student_code, p.title, c.company_name
     FROM internship_registrations r
     JOIN users u ON r.student_id = u.user_id
     JOIN internship_positions p ON r.position_id = p.position_id
     JOIN companies c ON p.company_id = c.company_id
     WHERE r.registration_id = ?"
);
$info->bind_param('i', $id);
$info->execute();
$detail = $info->get_result()->fetch_assoc();
?>
<?php include '../../includes/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4><i class="bi bi-pencil-square me-2 text-warning"></i>Chỉnh sửa Đăng ký</h4>
    <a href="list.php" class="btn btn-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Quay lại</a>
</div>

<?php if ($errors): ?>
<div class="alert alert-danger">
    <ul class="mb-0"><?php foreach ($errors as $e): ?><li><?= $e ?></li><?php endforeach; ?></ul>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-6">
                <strong>Sinh viên:</strong> <?= htmlspecialchars($detail['full_name']) ?>
                <?= $detail['student_code'] ? '(' . $detail['student_code'] . ')' : '' ?>
            </div>
            <div class="col-md-6">
                <strong>Vị trí:</strong> <?= htmlspecialchars($detail['title']) ?>
                — <?= htmlspecialchars($detail['company_name']) ?>
            </div>
        </div>
        <form method="POST">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Trạng thái</label>
                    <select name="status" class="form-select">
                        <option value="pending"   <?= $reg['status']==='pending'   ?'selected':'' ?>>Chờ duyệt</option>
                        <option value="approved"  <?= $reg['status']==='approved'  ?'selected':'' ?>>Đã duyệt</option>
                        <option value="rejected"  <?= $reg['status']==='rejected'  ?'selected':'' ?>>Từ chối</option>
                        <option value="cancelled" <?= $reg['status']==='cancelled' ?'selected':'' ?>>Đã hủy</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Ghi chú</label>
                    <textarea name="note" class="form-control" rows="3"><?= htmlspecialchars($reg['note'] ?? '') ?></textarea>
                </div>
            </div>
            <hr>
            <button type="submit" class="btn btn-warning"><i class="bi bi-save me-1"></i>Cập nhật</button>
            <a href="list.php" class="btn btn-secondary ms-2">Hủy</a>
        </form>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
