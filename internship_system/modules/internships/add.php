<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $registration_id = (int)($_POST['registration_id'] ?? 0);
    $week_number     = (int)($_POST['week_number'] ?? 0);
    $content         = sanitize($_POST['content'] ?? '');
    $tasks_done      = sanitize($_POST['tasks_done'] ?? '');
    $issues          = sanitize($_POST['issues'] ?? '');

    if ($registration_id <= 0) $errors[] = 'Vui lòng chọn đăng ký thực tập.';
    if ($week_number < 1)      $errors[] = 'Số tuần phải >= 1.';
    if (empty($content))       $errors[] = 'Nội dung nhật ký không được để trống.';

    // BUSINESS RULE: One journal per week per registration
    if (empty($errors)) {
        $chk = $conn->prepare(
            "SELECT journal_id FROM weekly_journals WHERE registration_id=? AND week_number=?"
        );
        $chk->bind_param('ii', $registration_id, $week_number);
        $chk->execute();
        if ($chk->get_result()->num_rows > 0) {
            $errors[] = "Nhật ký tuần $week_number cho đăng ký này đã tồn tại.";
        }
    }

    if (empty($errors)) {
        $stmt = $conn->prepare(
            "INSERT INTO weekly_journals (registration_id, week_number, content, tasks_done, issues)
             VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->bind_param('iisss', $registration_id, $week_number, $content, $tasks_done, $issues);
        if ($stmt->execute()) {
            setFlash('success', 'Đã thêm nhật ký tuần ' . $week_number . ' thành công.');
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
     ORDER BY u.full_name"
)->fetch_all(MYSQLI_ASSOC);
?>
<?php include '../../includes/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4><i class="bi bi-journal-plus me-2 text-primary"></i>Thêm Nhật ký Tuần</h4>
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
                <div class="col-md-8">
                    <label class="form-label fw-semibold">Đăng ký thực tập <span class="text-danger">*</span></label>
                    <select name="registration_id" class="form-select" required>
                        <option value="">-- Chọn sinh viên / vị trí --</option>
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
                    <label class="form-label fw-semibold">Số tuần <span class="text-danger">*</span></label>
                    <input type="number" name="week_number" class="form-control" min="1"
                           value="<?= (int)($_POST['week_number'] ?? 1) ?>" required>
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Nội dung công việc tuần này <span class="text-danger">*</span></label>
                    <textarea name="content" class="form-control" rows="4" required><?= htmlspecialchars($_POST['content'] ?? '') ?></textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Công việc đã hoàn thành</label>
                    <textarea name="tasks_done" class="form-control" rows="3"><?= htmlspecialchars($_POST['tasks_done'] ?? '') ?></textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Vấn đề gặp phải</label>
                    <textarea name="issues" class="form-control" rows="3"><?= htmlspecialchars($_POST['issues'] ?? '') ?></textarea>
                </div>
            </div>
            <hr>
            <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Lưu nhật ký</button>
            <a href="list.php" class="btn btn-secondary ms-2">Hủy</a>
        </form>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
