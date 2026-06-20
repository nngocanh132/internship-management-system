<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $registration_id = (int)($_POST['registration_id'] ?? 0);
    $attitude_score  = (float)($_POST['attitude_score'] ?? 0);
    $skill_score     = (float)($_POST['skill_score'] ?? 0);
    $result_score    = (float)($_POST['result_score'] ?? 0);
    $comment         = sanitize($_POST['comment'] ?? '');

    if ($registration_id <= 0) $errors[] = 'Vui lòng chọn sinh viên.';

    // BUSINESS RULE: Scores must be between 0 and 10
    foreach (['attitude_score' => $attitude_score, 'skill_score' => $skill_score, 'result_score' => $result_score] as $field => $val) {
        if ($val < 0 || $val > 10) {
            $errors[] = "Điểm $field phải từ 0 đến 10.";
        }
    }

    // BUSINESS RULE: Cannot evaluate twice for same registration
    if (empty($errors)) {
        $chk = $conn->prepare("SELECT eval_id FROM company_evaluations WHERE registration_id=?");
        $chk->bind_param('i', $registration_id);
        $chk->execute();
        if ($chk->get_result()->num_rows > 0) {
            $errors[] = 'Đăng ký này đã có đánh giá từ doanh nghiệp rồi.';
        }
    }

    if (empty($errors)) {
        // Calculate total: average of 3 scores
        $total = round(($attitude_score + $skill_score + $result_score) / 3, 2);

        $stmt = $conn->prepare(
            "INSERT INTO company_evaluations
             (registration_id, attitude_score, skill_score, result_score, total_score, comment)
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param('idddds', $registration_id, $attitude_score, $skill_score, $result_score, $total, $comment);
        if ($stmt->execute()) {
            setFlash('success', 'Đã thêm đánh giá doanh nghiệp thành công.');
            redirect('list.php');
        } else {
            $errors[] = 'Lỗi: ' . $conn->error;
        }
    }
}

$registrations = $conn->query(
    "SELECT r.registration_id, u.full_name, u.student_code, p.title, c.company_name
     FROM internship_registrations r
     JOIN users u ON r.student_id = u.user_id
     JOIN internship_positions p ON r.position_id = p.position_id
     JOIN companies c ON p.company_id = c.company_id
     WHERE r.status = 'approved'
       AND r.registration_id NOT IN (SELECT registration_id FROM company_evaluations)
     ORDER BY u.full_name"
)->fetch_all(MYSQLI_ASSOC);
?>
<?php include '../../includes/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4><i class="bi bi-star-fill me-2 text-primary"></i>Thêm Đánh giá Doanh nghiệp</h4>
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
                    <label class="form-label fw-semibold">Sinh viên (đã duyệt, chưa đánh giá) <span class="text-danger">*</span></label>
                    <select name="registration_id" class="form-select" required>
                        <option value="">-- Chọn sinh viên --</option>
                        <?php foreach ($registrations as $r): ?>
                        <option value="<?= $r['registration_id'] ?>"
                            <?= ($_POST['registration_id']??'')==$r['registration_id'] ?'selected':'' ?>>
                            <?= htmlspecialchars($r['full_name']) ?>
                            <?= $r['student_code'] ? '(' . $r['student_code'] . ')' : '' ?>
                            — <?= htmlspecialchars($r['title']) ?> @ <?= htmlspecialchars($r['company_name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Thái độ (0–10) <span class="text-danger">*</span></label>
                    <input type="number" name="attitude_score" class="form-control"
                           min="0" max="10" step="0.5"
                           value="<?= $_POST['attitude_score'] ?? '' ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Kỹ năng (0–10) <span class="text-danger">*</span></label>
                    <input type="number" name="skill_score" class="form-control"
                           min="0" max="10" step="0.5"
                           value="<?= $_POST['skill_score'] ?? '' ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Kết quả (0–10) <span class="text-danger">*</span></label>
                    <input type="number" name="result_score" class="form-control"
                           min="0" max="10" step="0.5"
                           value="<?= $_POST['result_score'] ?? '' ?>" required>
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Nhận xét</label>
                    <textarea name="comment" class="form-control" rows="3"><?= htmlspecialchars($_POST['comment'] ?? '') ?></textarea>
                </div>
            </div>
            <hr>
            <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Lưu đánh giá</button>
            <a href="list.php" class="btn btn-secondary ms-2">Hủy</a>
        </form>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
