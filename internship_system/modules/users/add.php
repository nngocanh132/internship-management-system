<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name     = sanitize($_POST['full_name'] ?? '');
    $email         = sanitize($_POST['email'] ?? '');
    $password      = $_POST['password'] ?? '';
    $role          = sanitize($_POST['role'] ?? '');
    $department_id = (int)($_POST['department_id'] ?? 0);
    $phone         = sanitize($_POST['phone'] ?? '');
    $student_code  = sanitize($_POST['student_code'] ?? '');
    $status        = sanitize($_POST['status'] ?? 'active');

    // --- Validation ---
    if (empty($full_name))  $errors[] = 'Họ tên không được để trống.';
    if (empty($email))      $errors[] = 'Email không được để trống.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email không hợp lệ.';
    if (strlen($password) < 6) $errors[] = 'Mật khẩu phải có ít nhất 6 ký tự.';
    if (empty($role))       $errors[] = 'Vui lòng chọn vai trò.';

    // BUSINESS RULE 1: Prevent duplicate email
    if (empty($errors)) {
        $chk = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
        $chk->bind_param('s', $email);
        $chk->execute();
        if ($chk->get_result()->num_rows > 0) {
            $errors[] = 'Email đã tồn tại trong hệ thống.';
        }
    }

    // BUSINESS RULE 2: Prevent duplicate student_code (if provided)
    if (empty($errors) && !empty($student_code)) {
        $chk2 = $conn->prepare("SELECT user_id FROM users WHERE student_code = ?");
        $chk2->bind_param('s', $student_code);
        $chk2->execute();
        if ($chk2->get_result()->num_rows > 0) {
            $errors[] = 'Mã sinh viên đã tồn tại.';
        }
    }

    if (empty($errors)) {
        $hashed = md5($password); // In production use password_hash()
        $dept   = $department_id > 0 ? $department_id : null;
        $code   = !empty($student_code) ? $student_code : null;

        $stmt = $conn->prepare(
            "INSERT INTO users (full_name, email, password, role, department_id, phone, student_code, status)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param('ssssisss', $full_name, $email, $hashed, $role, $dept, $phone, $code, $status);

        if ($stmt->execute()) {
            setFlash('success', "Đã thêm người dùng <strong>$full_name</strong> thành công.");
            redirect('list.php');
        } else {
            $errors[] = 'Lỗi khi lưu dữ liệu: ' . $conn->error;
        }
    }
}

// Fetch departments for dropdown
$departments = $conn->query("SELECT * FROM departments ORDER BY department_name")->fetch_all(MYSQLI_ASSOC);
?>
<?php include '../../includes/header.php'; ?>

<div class="page-header">
    <div>
        <h4><i class="bi bi-person-plus-fill me-2" style="color:#6366f1"></i>Thêm Người dùng Mới</h4>
    </div>
    <a href="list.php" class="btn btn-sm" style="background:#f1f5f9;color:#64748b;border:none;">
        <i class="bi bi-arrow-left me-1"></i>Quay lại
    </a>
</div>

<?php if ($errors): ?>
<div class="alert alert-danger mb-4">
    <div class="fw-semibold mb-1"><i class="bi bi-exclamation-circle me-1"></i>Vui lòng kiểm tra lại:</div>
    <ul class="mb-0 ps-3">
        <?php foreach ($errors as $e): ?><li><?= $e ?></li><?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-body p-4">
        <form method="POST">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Họ và tên <span class="text-danger">*</span></label>
                    <input type="text" name="full_name" class="form-control"
                           value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email <span class="text-danger">*</span></label>
                    <input type="email" name="email" class="form-control"
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Mật khẩu <span class="text-danger">*</span></label>
                    <input type="password" name="password" class="form-control" minlength="6" required>
                    <div class="form-text">Tối thiểu 6 ký tự</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Vai trò <span class="text-danger">*</span></label>
                    <select name="role" class="form-select" required>
                        <option value="">-- Chọn vai trò --</option>
                        <option value="student"     <?= ($_POST['role']??'')==='student'     ?'selected':'' ?>>Sinh viên</option>
                        <option value="lecturer"    <?= ($_POST['role']??'')==='lecturer'    ?'selected':'' ?>>Giảng viên</option>
                        <option value="company_rep" <?= ($_POST['role']??'')==='company_rep' ?'selected':'' ?>>Đại diện Doanh nghiệp</option>
                        <option value="admin"       <?= ($_POST['role']??'')==='admin'       ?'selected':'' ?>>Admin</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Khoa / Bộ môn</label>
                    <select name="department_id" class="form-select">
                        <option value="">-- Chọn khoa --</option>
                        <?php foreach ($departments as $d): ?>
                        <option value="<?= $d['department_id'] ?>"
                            <?= ($_POST['department_id']??'')==$d['department_id'] ?'selected':'' ?>>
                            <?= htmlspecialchars($d['department_name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Số điện thoại</label>
                    <input type="text" name="phone" class="form-control"
                           value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Mã sinh viên</label>
                    <input type="text" name="student_code" class="form-control"
                           value="<?= htmlspecialchars($_POST['student_code'] ?? '') ?>"
                           placeholder="Chỉ dành cho sinh viên">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Trạng thái</label>
                    <select name="status" class="form-select">
                        <option value="active"   <?= ($_POST['status']??'active')==='active'   ?'selected':'' ?>>Hoạt động</option>
                        <option value="inactive" <?= ($_POST['status']??'')==='inactive' ?'selected':'' ?>>Vô hiệu hóa</option>
                    </select>
                </div>
            </div>
            <div style="height:1px;background:#f1f5f9;margin:24px 0;"></div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save me-1"></i>Lưu người dùng
                </button>
                <a href="list.php" class="btn" style="background:#f1f5f9;color:#64748b;border:none;">Hủy</a>
            </div>
        </form>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
