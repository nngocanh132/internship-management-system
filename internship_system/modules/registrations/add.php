<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id  = (int)($_POST['student_id'] ?? 0);
    $position_id = (int)($_POST['position_id'] ?? 0);
    $note        = sanitize($_POST['note'] ?? '');

    if ($student_id <= 0)  $errors[] = 'Vui lòng chọn sinh viên.';
    if ($position_id <= 0) $errors[] = 'Vui lòng chọn vị trí thực tập.';

    if (empty($errors)) {
        // BUSINESS RULE 1: Student can only register once per position
        $chk = $conn->prepare(
            "SELECT registration_id FROM internship_registrations WHERE student_id=? AND position_id=?"
        );
        $chk->bind_param('ii', $student_id, $position_id);
        $chk->execute();
        if ($chk->get_result()->num_rows > 0) {
            $errors[] = 'Sinh viên đã đăng ký vị trí này rồi.';
        }
    }

    if (empty($errors)) {
        // BUSINESS RULE 2: Student cannot have 2 active (pending/approved) registrations
        $chk2 = $conn->prepare(
            "SELECT COUNT(*) AS cnt FROM internship_registrations
             WHERE student_id=? AND status IN ('pending','approved')"
        );
        $chk2->bind_param('i', $student_id);
        $chk2->execute();
        $cnt = $chk2->get_result()->fetch_assoc()['cnt'];
        if ($cnt >= 1) {
            $errors[] = 'Sinh viên đã có đăng ký đang chờ duyệt hoặc đã được duyệt. Không thể đăng ký thêm.';
        }
    }

    if (empty($errors)) {
        // BUSINESS RULE 3: Position must be open and not full
        $chk3 = $conn->prepare("SELECT status, quota, filled FROM internship_positions WHERE position_id=?");
        $chk3->bind_param('i', $position_id);
        $chk3->execute();
        $pos = $chk3->get_result()->fetch_assoc();
        if (!$pos || $pos['status'] !== 'open') {
            $errors[] = 'Vị trí thực tập không còn mở đăng ký.';
        } elseif ($pos['filled'] >= $pos['quota']) {
            $errors[] = 'Vị trí thực tập đã đầy chỗ (quota: ' . $pos['quota'] . ').';
        }
    }

    if (empty($errors)) {
        $stmt = $conn->prepare(
            "INSERT INTO internship_registrations (student_id, position_id, note) VALUES (?, ?, ?)"
        );
        $stmt->bind_param('iis', $student_id, $position_id, $note);
        if ($stmt->execute()) {
            setFlash('success', 'Đã thêm đăng ký thực tập thành công.');
            redirect('list.php');
        } else {
            $errors[] = 'Lỗi khi lưu: ' . $conn->error;
        }
    }
}

$students  = $conn->query("SELECT user_id, full_name, student_code FROM users WHERE role='student' AND status='active' ORDER BY full_name")->fetch_all(MYSQLI_ASSOC);
$positions = $conn->query("SELECT p.position_id, p.title, c.company_name, p.quota, p.filled
                           FROM internship_positions p
                           JOIN companies c ON p.company_id = c.company_id
                           WHERE p.status='open' ORDER BY c.company_name, p.title")->fetch_all(MYSQLI_ASSOC);
?>
<?php include '../../includes/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4><i class="bi bi-clipboard-plus me-2 text-primary"></i>Thêm Đăng ký Thực tập</h4>
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
                    <label class="form-label fw-semibold">Sinh viên <span class="text-danger">*</span></label>
                    <select name="student_id" class="form-select" required>
                        <option value="">-- Chọn sinh viên --</option>
                        <?php foreach ($students as $s): ?>
                        <option value="<?= $s['user_id'] ?>"
                            <?= ($_POST['student_id']??'')==$s['user_id'] ?'selected':'' ?>>
                            <?= htmlspecialchars($s['full_name']) ?>
                            <?= $s['student_code'] ? '(' . $s['student_code'] . ')' : '' ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Vị trí thực tập <span class="text-danger">*</span></label>
                    <select name="position_id" class="form-select" required>
                        <option value="">-- Chọn vị trí --</option>
                        <?php foreach ($positions as $p): ?>
                        <option value="<?= $p['position_id'] ?>"
                            <?= ($_POST['position_id']??'')==$p['position_id'] ?'selected':'' ?>>
                            <?= htmlspecialchars($p['company_name']) ?> — <?= htmlspecialchars($p['title']) ?>
                            (Còn: <?= $p['quota'] - $p['filled'] ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Ghi chú</label>
                    <textarea name="note" class="form-control" rows="3"><?= htmlspecialchars($_POST['note'] ?? '') ?></textarea>
                </div>
            </div>
            <hr>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save me-1"></i>Lưu đăng ký
            </button>
            <a href="list.php" class="btn btn-secondary ms-2">Hủy</a>
        </form>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
