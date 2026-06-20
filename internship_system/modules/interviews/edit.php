<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) redirect('list.php');

$stmt = $conn->prepare("SELECT * FROM lecturer_evaluations WHERE eval_id=?");
$stmt->bind_param('i', $id);
$stmt->execute();
$eval = $stmt->get_result()->fetch_assoc();
if (!$eval) { setFlash('error', 'Không tìm thấy đánh giá.'); redirect('list.php'); }

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $report_score       = (float)($_POST['report_score'] ?? 0);
    $journal_score      = (float)($_POST['journal_score'] ?? 0);
    $presentation_score = (float)($_POST['presentation_score'] ?? 0);
    $comment            = sanitize($_POST['comment'] ?? '');

    foreach ([$report_score, $journal_score, $presentation_score] as $val) {
        if ($val < 0 || $val > 10) { $errors[] = 'Điểm phải từ 0 đến 10.'; break; }
    }

    if (empty($errors)) {
        $total = round(($report_score + $journal_score + $presentation_score) / 3, 2);
        $stmt = $conn->prepare(
            "UPDATE lecturer_evaluations
             SET report_score=?, journal_score=?, presentation_score=?, total_score=?, comment=?
             WHERE eval_id=?"
        );
        $stmt->bind_param('ddddsi', $report_score, $journal_score, $presentation_score, $total, $comment, $id);
        if ($stmt->execute()) {
            setFlash('success', 'Đã cập nhật đánh giá.');
            redirect('list.php');
        } else {
            $errors[] = 'Lỗi: ' . $conn->error;
        }
    }
    $eval = array_merge($eval, $_POST);
}
?>
<?php include '../../includes/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4><i class="bi bi-pencil-square me-2 text-warning"></i>Chỉnh sửa Đánh giá GV</h4>
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
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Điểm báo cáo (0–10)</label>
                    <input type="number" name="report_score" class="form-control"
                           min="0" max="10" step="0.5" value="<?= $eval['report_score'] ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Điểm nhật ký (0–10)</label>
                    <input type="number" name="journal_score" class="form-control"
                           min="0" max="10" step="0.5" value="<?= $eval['journal_score'] ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Điểm thuyết trình (0–10)</label>
                    <input type="number" name="presentation_score" class="form-control"
                           min="0" max="10" step="0.5" value="<?= $eval['presentation_score'] ?>">
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Nhận xét</label>
                    <textarea name="comment" class="form-control" rows="3"><?= htmlspecialchars($eval['comment'] ?? '') ?></textarea>
                </div>
            </div>
            <hr>
            <button type="submit" class="btn btn-warning"><i class="bi bi-save me-1"></i>Cập nhật</button>
            <a href="list.php" class="btn btn-secondary ms-2">Hủy</a>
        </form>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
