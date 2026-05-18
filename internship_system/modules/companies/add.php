<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $company_name  = sanitize($_POST['company_name'] ?? '');
    $industry      = sanitize($_POST['industry'] ?? '');
    $address       = sanitize($_POST['address'] ?? '');
    $description   = sanitize($_POST['description'] ?? '');
    $contact_email = sanitize($_POST['contact_email'] ?? '');
    $phone         = sanitize($_POST['phone'] ?? '');
    $status        = sanitize($_POST['status'] ?? 'active');

    if (empty($company_name))  $errors[] = 'Tên doanh nghiệp không được để trống.';
    if (!empty($contact_email) && !filter_var($contact_email, FILTER_VALIDATE_EMAIL))
        $errors[] = 'Email liên hệ không hợp lệ.';

    // BUSINESS RULE: Prevent duplicate company email
    if (empty($errors) && !empty($contact_email)) {
        $chk = $conn->prepare("SELECT company_id FROM companies WHERE contact_email = ?");
        $chk->bind_param('s', $contact_email);
        $chk->execute();
        if ($chk->get_result()->num_rows > 0) {
            $errors[] = 'Email liên hệ đã tồn tại trong hệ thống.';
        }
    }

    if (empty($errors)) {
        $stmt = $conn->prepare(
            "INSERT INTO companies (company_name, industry, address, description, contact_email, phone, status)
             VALUES (?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param('sssssss', $company_name, $industry, $address, $description, $contact_email, $phone, $status);

        if ($stmt->execute()) {
            setFlash('success', "Đã thêm doanh nghiệp <strong>$company_name</strong> thành công.");
            redirect('list.php');
        } else {
            $errors[] = 'Lỗi khi lưu: ' . $conn->error;
        }
    }
}
?>
<?php include '../../includes/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4><i class="bi bi-building-add me-2 text-primary"></i>Thêm Doanh nghiệp Mới</h4>
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
                    <label class="form-label fw-semibold">Tên doanh nghiệp <span class="text-danger">*</span></label>
                    <input type="text" name="company_name" class="form-control"
                           value="<?= htmlspecialchars($_POST['company_name'] ?? '') ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Ngành nghề</label>
                    <input type="text" name="industry" class="form-control"
                           value="<?= htmlspecialchars($_POST['industry'] ?? '') ?>"
                           placeholder="VD: Software Development, Finance...">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Email liên hệ</label>
                    <input type="email" name="contact_email" class="form-control"
                           value="<?= htmlspecialchars($_POST['contact_email'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Điện thoại</label>
                    <input type="text" name="phone" class="form-control"
                           value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Địa chỉ</label>
                    <input type="text" name="address" class="form-control"
                           value="<?= htmlspecialchars($_POST['address'] ?? '') ?>">
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Mô tả</label>
                    <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Trạng thái</label>
                    <select name="status" class="form-select">
                        <option value="active"   <?= ($_POST['status']??'active')==='active'   ?'selected':'' ?>>Hoạt động</option>
                        <option value="inactive" <?= ($_POST['status']??'')==='inactive' ?'selected':'' ?>>Vô hiệu hóa</option>
                    </select>
                </div>
            </div>
            <hr>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save me-1"></i>Lưu doanh nghiệp
            </button>
            <a href="list.php" class="btn btn-secondary ms-2">Hủy</a>
        </form>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
