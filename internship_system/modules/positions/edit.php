<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) redirect('list.php');

$stmt = $conn->prepare("SELECT * FROM internship_positions WHERE position_id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$pos = $stmt->get_result()->fetch_assoc();
if (!$pos) { setFlash('error', 'Không tìm thấy vị trí.'); redirect('list.php'); }

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

    if ($company_id <= 0)  $errors[] = 'Vui lòng chọn doanh nghiệp.';
    if (empty($title))     $errors[] = 'Tiêu đề không được để trống.';
    if ($quota < 1)        $errors[] = 'Quota phải ít nhất là 1.';

    // BUSINESS RULE 1: end_date after start_date
    if (!empty($start_date) && !empty($end_date) && $end_date <= $start_date) {
        $errors[] = 'Ngày kết thúc phải sau ngày bắt đầu.';
    }

    // BUSINESS RULE 2: quota cannot be less than filled
    if ($quota < $pos['filled']) {
        $errors[] = "Quota ($quota) không thể nhỏ hơn số đã đăng ký ({$pos['filled']}).";
    }

    // BUSINESS RULE 3: Duplicate title at same company (exclude current)
    if (empty($errors)) {
        $chk = $conn->prepare(
            "SELECT position_id FROM internship_positions WHERE company_id=? AND title=? AND position_id!=?"
        );
        $chk->bind_param('isi', $company_id, $title, $id);
        $chk->execute();
        if ($chk->get_result()->num_rows > 0) {
            $errors[] = 'Vị trí với tiêu đề này đã tồn tại tại doanh nghiệp đã chọn.';
        }
    }

    if (empty($errors)) {
        // Auto-update status based on quota
        if ($quota <= $pos['filled']) $status = 'full';

        $stmt = $conn->prepare(
            "UPDATE internship_positions
             SET company_id=?, title=?, description=?, requirements=?, industry=?, quota=?, start_date=?, end_date=?, status=?
             WHERE position_id=?"
        );
        $stmt->bind_param('issssisssi', $company_id, $title, $description, $requirements, $industry, $quota, $start_date, $end_date, $status, $id);

        if ($stmt->execute()) {
            setFlash('success', "Đã cập nhật vị trí <strong>$title</strong>.");
            redirect('list.php');
        } else {
            $errors[] = 'Lỗi khi cập nhật: ' . $conn->error;
        }
    }
    $pos = array_merge($pos, $_POST);
}

$companies = $conn->query("SELECT company_id, company_name FROM companies WHERE status='active' ORDER BY company_name")->fetch_all(MYSQLI_ASSOC);
?>
<?php include '../../includes/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4><i class="bi bi-pencil-square me-2 text-warning"></i>Chỉnh sửa Vị trí Thực tập</h4>
    <a href="list.php" class="btn btn-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Quay lại</a>
</div>

<?php if ($errors): ?>
<div class="alert alert-danger">
    <ul class="mb-0"><?php foreach ($errors as $e): ?><li><?= $e ?></li><?php endforeach; ?></ul>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <div class="alert alert-info py-2 small">
            <i class="bi bi-info-circle me-1"></i>
            Đã có <strong><?= $pos['filled'] ?></strong> sinh viên đăng ký vị trí này.
        </div>
        <form method="POST">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Doanh nghiệp <span class="text-danger">*</span></label>
                    <select name="company_id" class="form-select" required>
                        <?php foreach ($companies as $c): ?>
                        <option value="<?= $c['company_id'] ?>"
                            <?= $pos['company_id']==$c['company_id'] ?'selected':'' ?>>
                            <?= htmlspecialchars($c['company_name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Tiêu đề vị trí <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control"
                           value="<?= htmlspecialchars($pos['title']) ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Ngành nghề</label>
                    <input type="text" name="industry" class="form-control"
                           value="<?= htmlspecialchars($pos['industry'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Quota <span class="text-danger">*</span></label>
                    <input type="number" name="quota" class="form-control" min="<?= $pos['filled'] ?>"
                           value="<?= $pos['quota'] ?>" required>
                    <div class="form-text">Tối thiểu: <?= $pos['filled'] ?> (đã đăng ký)</div>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Trạng thái</label>
                    <select name="status" class="form-select">
                        <option value="open"   <?= $pos['status']==='open'   ?'selected':'' ?>>Đang mở</option>
                        <option value="closed" <?= $pos['status']==='closed' ?'selected':'' ?>>Đóng</option>
                        <option value="full"   <?= $pos['status']==='full'   ?'selected':'' ?>>Đã đầy</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Ngày bắt đầu</label>
                    <input type="date" name="start_date" class="form-control"
                           value="<?= $pos['start_date'] ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Ngày kết thúc</label>
                    <input type="date" name="end_date" class="form-control"
                           value="<?= $pos['end_date'] ?>">
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Mô tả</label>
                    <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($pos['description'] ?? '') ?></textarea>
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Yêu cầu</label>
                    <textarea name="requirements" class="form-control" rows="3"><?= htmlspecialchars($pos['requirements'] ?? '') ?></textarea>
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
