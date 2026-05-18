<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';

// ---- Handle DELETE ----
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];

    // BUSINESS RULE: Cannot delete user who has active registrations
    $check = $conn->prepare(
        "SELECT COUNT(*) AS cnt FROM internship_registrations WHERE student_id = ? AND status IN ('pending','approved')"
    );
    $check->bind_param('i', $id);
    $check->execute();
    $row = $check->get_result()->fetch_assoc();

    if ($row['cnt'] > 0) {
        setFlash('error', 'Không thể xóa: Người dùng đang có đăng ký thực tập đang hoạt động.');
    } else {
        $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        setFlash('success', 'Đã xóa người dùng thành công.');
    }
    redirect('list.php');
}

// ---- Fetch all users ----
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$role_filter = isset($_GET['role']) ? sanitize($_GET['role']) : '';

$sql = "SELECT u.*, d.department_name
        FROM users u
        LEFT JOIN departments d ON u.department_id = d.department_id
        WHERE 1=1";
$params = [];
$types  = '';

if ($search !== '') {
    $sql .= " AND (u.full_name LIKE ? OR u.email LIKE ? OR u.student_code LIKE ?)";
    $like = "%$search%";
    $params = array_merge($params, [$like, $like, $like]);
    $types .= 'sss';
}
if ($role_filter !== '') {
    $sql .= " AND u.role = ?";
    $params[] = $role_filter;
    $types .= 's';
}
$sql .= " ORDER BY u.created_at DESC";

$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<?php include '../../includes/header.php'; ?>

<div class="page-header">
    <div>
        <h4><i class="bi bi-people-fill me-2" style="color:#6366f1"></i>Quản lý Người dùng</h4>
        <div class="page-subtitle">Tổng: <?= count($users) ?> người dùng trong hệ thống</div>
    </div>
    <a href="add.php" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg me-1"></i>Thêm người dùng
    </a>
</div>

<?php showFlash(); ?>

<!-- Search / Filter -->
<div class="card mb-4" style="border-radius:12px;">
    <div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-5">
                <label class="form-label mb-1">Tìm kiếm</label>
                <div class="input-group">
                    <span class="input-group-text" style="background:#f8fafc;border-color:#e2e8f0;border-radius:8px 0 0 8px;">
                        <i class="bi bi-search text-muted"></i>
                    </span>
                    <input type="text" name="search" class="form-control"
                           style="border-left:none;border-radius:0 8px 8px 0;"
                           placeholder="Tên, email, mã sinh viên..." value="<?= htmlspecialchars($search) ?>">
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label mb-1">Vai trò</label>
                <select name="role" class="form-select">
                    <option value="">Tất cả vai trò</option>
                    <option value="student"     <?= $role_filter==='student'     ?'selected':'' ?>>Sinh viên</option>
                    <option value="lecturer"    <?= $role_filter==='lecturer'    ?'selected':'' ?>>Giảng viên</option>
                    <option value="company_rep" <?= $role_filter==='company_rep' ?'selected':'' ?>>Đại diện DN</option>
                    <option value="admin"       <?= $role_filter==='admin'       ?'selected':'' ?>>Admin</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label mb-1 d-block">&nbsp;</label>
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search me-1"></i>Tìm
                </button>
            </div>
            <div class="col-md-2">
                <label class="form-label mb-1 d-block">&nbsp;</label>
                <a href="list.php" class="btn w-100" style="background:#f1f5f9;color:#64748b;border:none;">
                    <i class="bi bi-x-lg me-1"></i>Xóa lọc
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card table-card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-table me-2"></i>Danh sách người dùng</span>
        <span class="badge" style="background:#ede9fe;color:#6d28d9;font-size:.78rem;"><?= count($users) ?> kết quả</span>
    </div>
    <div class="card-body p-0">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Người dùng</th>
                    <th>Email</th>
                    <th>Vai trò</th>
                    <th>Mã SV</th>
                    <th>Khoa</th>
                    <th>Trạng thái</th>
                    <th style="width:100px">Thao tác</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($users)): ?>
                <tr>
                    <td colspan="8" class="text-center py-5">
                        <i class="bi bi-inbox fs-1 d-block mb-2 text-muted opacity-25"></i>
                        <span class="text-muted">Không tìm thấy người dùng nào</span>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($users as $i => $u):
                    $roles = ['student'=>'Sinh viên','lecturer'=>'Giảng viên',
                              'company_rep'=>'Đại diện DN','admin'=>'Admin'];
                    $badge = ['student'=>'#3b82f6','lecturer'=>'#10b981',
                              'company_rep'=>'#f59e0b','admin'=>'#ef4444'];
                    $r = $u['role'];
                    $initials = strtoupper(mb_substr($u['full_name'], 0, 1));
                    $colors = ['#6366f1','#10b981','#f59e0b','#ef4444','#8b5cf6','#14b8a6'];
                    $color  = $colors[crc32($u['full_name']) % count($colors)];
                ?>
                <tr>
                    <td style="color:#94a3b8;font-size:.8rem"><?= $i + 1 ?></td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="avatar-sm" style="background:<?= $color ?>20;color:<?= $color ?>">
                                <?= $initials ?>
                            </div>
                            <span class="fw-semibold"><?= htmlspecialchars($u['full_name']) ?></span>
                        </div>
                    </td>
                    <td style="color:#64748b"><?= htmlspecialchars($u['email']) ?></td>
                    <td>
                        <span class="badge" style="background:<?= $badge[$r] ?? '#94a3b8' ?>20;color:<?= $badge[$r] ?? '#94a3b8' ?>">
                            <?= $roles[$r] ?? $r ?>
                        </span>
                    </td>
                    <td style="color:#64748b"><?= htmlspecialchars($u['student_code'] ?? '—') ?></td>
                    <td style="color:#64748b;font-size:.82rem"><?= htmlspecialchars($u['department_name'] ?? '—') ?></td>
                    <td>
                        <?php if ($u['status'] === 'active'): ?>
                        <span class="badge" style="background:#dcfce7;color:#166534;">
                            <i class="bi bi-circle-fill me-1" style="font-size:.5rem"></i>Hoạt động
                        </span>
                        <?php else: ?>
                        <span class="badge" style="background:#f1f5f9;color:#64748b;">
                            <i class="bi bi-circle me-1" style="font-size:.5rem"></i>Vô hiệu
                        </span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="edit.php?id=<?= $u['user_id'] ?>" class="btn btn-warning btn-sm" title="Chỉnh sửa">
                                <i class="bi bi-pencil-fill"></i>
                            </a>
                            <a href="list.php?delete=<?= $u['user_id'] ?>"
                               class="btn btn-danger btn-sm" title="Xóa"
                               onclick="return confirm('Xác nhận xóa người dùng này?')">
                                <i class="bi bi-trash-fill"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
