<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $company_id  = (int)($_POST['company_id'] ?? 0);
    $title       = sanitize($_POST['title'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $requirements= sanitize($_POST['requirements'] ?? '');
    $industry    = sanitize($_POST['industry'] ?? '');
    $quota       = (int)($_POST['quota'] ?? 1);
    $start_date  = sanitize($_POST['start_date'] ?? '');
    $end_date    = sanitize($_POST['end_date'] ?? '');
    $status      = sanitize($_POST['status'] ?? 'open');

    // --- Validation ---
    if ($company_id <= 0)  $errors[] = 'Vui lòng chọn doanh nghiệp.';
    if (empty($title))     $errors[] = 'Tiêu đề vị trí không được để trống.';
    if ($quota < 1)        $errors[] = 'Quota phải ít nhất là 1.';
    if (empty($start_date))$errors[] = 'Ngày bắt đầu không được để trống.';
    if (empty($end_date))  $errors[] = 'Ngày kết thúc không được để trống.';

    // BUSINESS RULE 1: end_date must be after start_date
    if (!empty($start_date) && !empty($end_date) && $end_date <= $start_date) {
        $errors[] = 'Ngày kết thúc phải sau ngày bắt đầu.';
    }

    // BUSINESS RULE 2: Prevent duplicate title at same company
    if (empty($errors)) {
        $chk = $conn->prepare(
            "SELECT position_id FROM internship_positions WHERE company_id = ? AND title = ?"
        );
        $chk->bind_param('is', $company_id, $title);
        $chk->execute();
        if ($chk->get_result()->num_rows > 0) {
            $errors[] = 'Vị trí với tiêu đề này đã tồn tại tại doanh nghiệp đã chọn.';
        }
    }

    // BUSINESS RULE 3: Company must be active
    if (empty($errors)) {
        $chk3 = $conn->prepare("SELECT status FROM companies WHERE company_id = ?");
        $chk3->bind_param('i', $company_id);
        $chk3->execute();
        $comp = $chk3->get_result()->fetch_assoc();
        if (!$comp || $comp['status'] !== 'active') {
            $errors[] = 'Doanh nghiệp đã chọn không còn hoạt động.';
        }
    }

    if (empty($errors)) {
        $stmt = $conn->prepare(
            "INSERT INTO internship_positions
             (company_id, title, description, requirements, industry, quota, start_date, end_date, status)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param('issssisss', $company_id, $title, $description, $requirements, $industry, $quota, $start_date, $end_date, $status);

        if ($stmt->execute()) {
            setFlash('success', "Đã thêm vị trí <strong>$title</strong> thành công.");
            redirect('list.php');
        } else {
            $errors[] = 'Lỗi khi lưu: ' . $conn->error;
        }
    }
}

$companies = $conn->query("SELECT company_id, company_name FROM companies WHERE status='active' ORDER BY company_name")->fetch_all(MYSQLI_ASSOC);
?>
<?php include '../../includes/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4><i class="bi bi-briefcase me-2 text-primary"></i>Thêm Vị trí Thực tập</h4>
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
                    <label class="form-label fw-semibold">Doanh nghiệp <span class="text-danger">*</span></label>
                    <select name="company_id" class="form-select" required>
                        <option value="">-- Chọn doanh nghiệp --</option>
                        <?php foreach ($companies as $c): ?>
                        <option value="<?= $c['company_id'] ?>"
                            <?= ($_POST['company_id']??'')==$c['company_id'] ?'selected':'' ?>>
                            <?= htmlspecialchars($c['company_name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Tiêu đề vị trí <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control"
                           value="<?= htmlspecialchars($_POST['title'] ?? '') ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Ngành nghề</label>
                    <input type="text" name="industry" class="form-control"
                           value="<?= htmlspecialchars($_POST['industry'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Quota (số lượng) <span class="text-danger">*</span></label>
                    <input type="number" name="quota" class="form-control" min="1"
                           value="<?= (int)($_POST['quota'] ?? 1) ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Trạng thái</label>
                    <select name="status" class="form-select">
                        <option value="open"   <?= ($_POST['status']??'open')==='open'   ?'selected':'' ?>>Đang mở</option>
                        <option value="closed" <?= ($_POST['status']??'')==='closed' ?'selected':'' ?>>Đóng</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Ngày bắt đầu <span class="text-danger">*</span></label>
                    <input type="date" name="start_date" class="form-control"
                           value="<?= htmlspecialchars($_POST['start_date'] ?? '') ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Ngày kết thúc <span class="text-danger">*</span></label>
                    <input type="date" name="end_date" class="form-control"
                           value="<?= htmlspecialchars($_POST['end_date'] ?? '') ?>" required>
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Mô tả công việc</label>
                    <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Yêu cầu</label>
                    <textarea name="requirements" class="form-control" rows="3"><?= htmlspecialchars($_POST['requirements'] ?? '') ?></textarea>
                </div>
            </div>
            <hr>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save me-1"></i>Lưu vị trí
            </button>
            <a href="list.php" class="btn btn-secondary ms-2">Hủy</a>
        </form>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
