<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $registration_id = (int)($_POST['registration_id'] ?? 0);
    $lecturer_id     = (int)($_POST['lecturer_id'] ?? 0);
    $note            = sanitize($_POST['note'] ?? '');

    if ($registration_id <= 0) $errors[] = 'Vui lòng chọn đăng ký thực tập.';
    if ($lecturer_id <= 0)     $errors[] = 'Vui lòng chọn giảng viên.';

    // BUSINESS RULE: Each approved registration can only have one assignment
    if (empty($errors)) {
        $chk = $conn->prepare("SELECT assignment_id FROM internship_assignments WHERE registration_id=?");
        $chk->bind_param('i', $registration_id);
        $chk->execute();
        if ($chk->get_result()->num_rows > 0) {
            $errors[] = 'Đăng ký này đã được phân công GVHD rồi.';
        }
    }

    // BUSINESS RULE: Lecturer must have role = 'lecturer'
    if (empty($errors)) {
        $chk2 = $conn->prepare("SELECT role FROM users WHERE user_id=?");
        $chk2->bind_param('i', $lecturer_id);
        $chk2->execute();
        $u = $chk2->get_result()->fetch_assoc();
        if (!$u || $u['role'] !== 'lecturer') {
            $errors[] = 'Người được chọn không phải là giảng viên.';
        }
    }

    if (empty($errors)) {
        $stmt = $conn->prepare(
            "INSERT INTO internship_assignments (registration_id, lecturer_id, note) VALUES (?, ?, ?)"
        );
        $stmt->bind_param('iis', $registration_id, $lecturer_id, $note);
        if ($stmt->execute()) {
            setFlash('success', 'Đã phân công GVHD thành công.');
            redirect('list.php');
        } else {
            $errors[] = 'Lỗi: ' . $conn->error;
        }
    }
}

// Only approved registrations without assignment
$registrations = $conn->query(
    "SELECT r.registration_id, u.full_name, u.student_code, p.title, c.company_name
     FROM internship_registrations r
     JOIN users u ON r.student_id = u.user_id
     JOIN internship_positions p ON r.position_id = p.position_id
     JOIN companies c ON p.company_id = c.company_id
     WHERE r.status = 'approved'
       AND r.registration_id NOT IN (SELECT registration_id FROM internship_assignments)
     ORDER BY u.full_name"
)->fetch_all(MYSQLI_ASSOC);

$lecturers = $conn->query(
    "SELECT user_id, full_name FROM users WHERE role='lecturer' AND status='active' ORDER BY full_name"
)->fetch_all(MYSQLI_ASSOC);
?>
<?php include '../../includes/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4><i class="bi bi-person-plus me-2 text-primary"></i>Thêm Phân công GVHD</h4>
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
                    <label class="form-label fw-semibold">Đăng ký thực tập (đã duyệt) <span class="text-danger">*</span></label>
                    <select name="registration_id" class="form-select" required>
                        <option value="">-- Chọn sinh viên --</option>
                        <?php foreach ($registrations as $r): ?>
                        <option value="<?= $r['registration_id'] ?>"
                            <?= ($_POST['registration_id']??'')==$r['registration_id'] ?'selected':'' ?>>
                            <?= htmlspecialchars($r['full_name']) ?>
                            <?= $r['student_code'] ? '(' . $r['student_code'] . ')' : '' ?>
                            — <?= htmlspecialchars($r['title']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Giảng viên hướng dẫn <span class="text-danger">*</span></label>
                    <select name="lecturer_id" class="form-select" required>
                        <option value="">-- Chọn giảng viên --</option>
                        <?php foreach ($lecturers as $l): ?>
                        <option value="<?= $l['user_id'] ?>"
                            <?= ($_POST['lecturer_id']??'')==$l['user_id'] ?'selected':'' ?>>
                            <?= htmlspecialchars($l['full_name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Ghi chú</label>
                    <textarea name="note" class="form-control" rows="2"><?= htmlspecialchars($_POST['note'] ?? '') ?></textarea>
                </div>
            </div>
            <hr>
            <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Lưu phân công</button>
            <a href="list.php" class="btn btn-secondary ms-2">Hủy</a>
        </form>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
