<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) { redirect('list.php'); }

// Fetch existing user
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
if (!$user) {
    setFlash('error', 'Không tìm thấy người dùng.');
    redirect('list.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name     = sanitize($_POST['full_name'] ?? '');
    $email         = sanitize($_POST['email'] ?? '');
    $role          = sanitize($_POST['role'] ?? '');
    $department_id = (int)($_POST['department_id'] ?? 0);
    $phone         = sanitize($_POST['phone'] ?? '');
    $student_code  = sanitize($_POST['student_code'] ?? '');
    $status        = sanitize($_POST['status'] ?? 'active');
    $new_password  = $_POST['new_password'] ?? '';

    if (empty($full_name)) $errors[] = 'Họ tên không được để trống.';
    if (empty($email))     $errors[] = 'Email không được để trống.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email không hợp lệ.';

    // BUSINESS RULE: Duplicate email check (exclude current user)
    if (empty($errors)) {
        $chk = $conn->prepare("SELECT user_id FROM users WHERE email = ? AND user_id != ?");
        $chk->bind_param('si', $email, $id);
        $chk->execute();
        if ($chk->get_result()->num_rows > 0) {
            $errors[] = 'Email đã được sử dụng bởi tài khoản khác.';
        }
    }

    // BUSINESS RULE: Duplicate student_code check (exclude current user)
    if (empty($errors) && !empty($student_code)) {
        $chk2 = $conn->prepare("SELECT user_id FROM users WHERE student_code = ? AND user_id != ?");
        $chk2->bind_param('si', $student_code, $id);
        $chk2->execute();
        if ($chk2->get_result()->num_rows > 0) {
            $errors[] = 'Mã sinh viên đã tồn tại.';
        }
    }

    if (empty($errors)) {
        $dept = $department_id > 0 ? $department_id : null;
        $code = !empty($student_code) ? $student_code : null;

        if (!empty($new_password) && strlen($new_password) >= 6) {
            $hashed = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare(
                "UPDATE users SET full_name=?, email=?, password=?, role=?, department_id=?, phone=?, student_code=?, status=?
                 WHERE user_id=?"
            );
            $stmt->bind_param('ssssisssi', $full_name, $email, $hashed, $role, $dept, $phone, $code, $status, $id);
        } else {
            $stmt = $conn->prepare(
                "UPDATE users SET full_name=?, email=?, role=?, department_id=?, phone=?, student_code=?, status=?
                 WHERE user_id=?"
            );
            $stmt->bind_param('ssssssi', $full_name, $email, $role, $dept, $phone, $code, $status, $id);
        }

        if ($stmt->execute()) {
            setFlash('success', "Đã cập nhật người dùng <strong>$full_name</strong>.");
            redirect('list.php');
        } else {
            $errors[] = 'Lỗi khi cập nhật: ' . $conn->error;
        }
    }

    // Re-populate form with POST data on error
    $user = array_merge($user, $_POST);
}

$departments = $conn->query("SELECT * FROM departments ORDER BY department_name")->fetch_all(MYSQLI_ASSOC);
?>
<?php include '../../includes/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4><i class="bi bi-pencil-square me-2 text-warning"></i>Chỉnh sửa Người dùng</h4>
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
                    <label class="form-label fw-semibold">Họ và tên <span class="text-danger">*</span></label>
                    <input type="text" name="full_name" class="form-control"
                           value="<?= htmlspecialchars($user['full_name']) ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                    <input type="email" name="email" class="form-control"
                           value="<?= htmlspecialchars($user['email']) ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Mật khẩu mới <span class="text-muted">(để trống = giữ nguyên)</span></label>
                    <input type="password" name="new_password" class="form-control" minlength="6">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Vai trò</label>
                    <select name="role" class="form-select">
                        <option value="student"     <?= $user['role']==='student'     ?'selected':'' ?>>Sinh viên</option>
                        <option value="lecturer"    <?= $user['role']==='lecturer'    ?'selected':'' ?>>Giảng viên</option>
                        <option value="company_rep" <?= $user['role']==='company_rep' ?'selected':'' ?>>Đại diện DN</option>
                        <option value="admin"       <?= $user['role']==='admin'       ?'selected':'' ?>>Admin</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Khoa / Bộ môn</label>
                    <select name="department_id" class="form-select">
                        <option value="">-- Chọn khoa --</option>
                        <?php foreach ($departments as $d): ?>
                        <option value="<?= $d['department_id'] ?>"
                            <?= $user['department_id']==$d['department_id'] ?'selected':'' ?>>
                            <?= htmlspecialchars($d['department_name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Số điện thoại</label>
                    <input type="text" name="phone" class="form-control"
                           value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Mã sinh viên</label>
                    <input type="text" name="student_code" class="form-control"
                           value="<?= htmlspecialchars($user['student_code'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Trạng thái</label>
                    <select name="status" class="form-select">
                        <option value="active"   <?= $user['status']==='active'   ?'selected':'' ?>>Hoạt động</option>
                        <option value="inactive" <?= $user['status']==='inactive' ?'selected':'' ?>>Vô hiệu hóa</option>
                    </select>
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
