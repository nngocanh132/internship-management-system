<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';
requireRole(['admin']);

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $chk = $conn->prepare("SELECT COUNT(*) AS cnt FROM users WHERE department_id=?");
    $chk->bind_param('i', $id); $chk->execute();
    if ($chk->get_result()->fetch_assoc()['cnt'] > 0) {
        setFlash('error', 'Không thể xóa: Khoa đang có người dùng liên kết.');
    } else {
        $s = $conn->prepare("DELETE FROM departments WHERE department_id=?");
        $s->bind_param('i', $id); $s->execute();
        setFlash('success', 'Đã xóa khoa/bộ môn.');
    }
    redirect('list.php');
}

$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$sql = "SELECT d.*, COUNT(u.user_id) AS total_users
        FROM departments d LEFT JOIN users u ON d.department_id=u.department_id WHERE 1=1";
$params=[]; $types='';
if ($search!=='') {
    $sql.=" AND (d.department_name LIKE ? OR d.faculty LIKE ?)";
    $like="%$search%"; $params=[$like,$like]; $types='ss';
}
$sql.=" GROUP BY d.department_id ORDER BY d.department_id DESC";
$stmt=$conn->prepare($sql);
if ($params) $stmt->bind_param($types,...$params);
$stmt->execute();
$departments=$stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<?php include '../../includes/header.php'; ?>
<div class="page-header">
    <div>
        <h4><i class="bi bi-diagram-3-fill me-2" style="color:#a78bfa"></i>Quản lý Khoa / Bộ môn</h4>
        <div class="page-subtitle">Tổng: <?= count($departments) ?> khoa/bộ môn</div>
    </div>
    <a href="add.php" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>Thêm mới</a>
</div>
<?php showFlash(); ?>
<div class="card mb-4">
    <div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-7">
                <label class="form-label mb-1">Tìm kiếm</label>
                <div class="input-group">
                    <span class="input-group-text" style="background:#f8fafc;border-color:#e2e8f0;border-radius:8px 0 0 8px;"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control" style="border-left:none;border-radius:0 8px 8px 0;" placeholder="Tên khoa, trường..." value="<?= htmlspecialchars($search) ?>">
                </div>
            </div>
            <div class="col-md-2"><label class="form-label mb-1 d-block">&nbsp;</label>
                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search me-1"></i>Tìm</button></div>
            <div class="col-md-2"><label class="form-label mb-1 d-block">&nbsp;</label>
                <a href="list.php" class="btn btn-secondary w-100"><i class="bi bi-x-lg me-1"></i>Xóa lọc</a></div>
        </form>
    </div>
</div>
<div class="card table-card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-table me-2"></i>Danh sách Khoa / Bộ môn</span>
        <span class="badge" style="background:#ede9fe;color:#6d28d9"><?= count($departments) ?> kết quả</span>
    </div>
    <div class="card-body p-0">
        <table class="table mb-0">
            <thead><tr><th>#</th><th>Tên Khoa / Bộ môn</th><th>Trường trực thuộc</th><th>Số người dùng</th><th style="width:100px">Thao tác</th></tr></thead>
            <tbody>
            <?php if (empty($departments)): ?>
                <tr><td colspan="5" class="text-center py-5"><i class="bi bi-inbox fs-1 d-block mb-2 text-muted opacity-25"></i><span class="text-muted">Không có dữ liệu</span></td></tr>
            <?php else: ?>
                <?php $colors=['#a78bfa','#34d399','#60a5fa','#f472b6','#fb923c'];
                foreach ($departments as $i=>$d):
                    $c=$colors[$i%count($colors)]; $ini=strtoupper(mb_substr($d['department_name'],0,2)); ?>
                <tr>
                    <td style="color:#94a3b8;font-size:.8rem"><?= $i+1 ?></td>
                    <td><div class="d-flex align-items-center gap-2">
                        <div class="avatar-sm" style="background:<?= $c ?>22;color:<?= $c ?>;font-size:.7rem"><?= $ini ?></div>
                        <strong><?= htmlspecialchars($d['department_name']) ?></strong>
                    </div></td>
                    <td style="color:#64748b;font-size:.82rem"><?= htmlspecialchars($d['faculty']??'—') ?></td>
                    <td><span class="badge" style="background:#eff6ff;color:#1d4ed8"><?= $d['total_users'] ?> người</span></td>
                    <td><div class="d-flex gap-1">
                        <a href="edit.php?id=<?= $d['department_id'] ?>" class="btn btn-warning btn-sm"><i class="bi bi-pencil-fill"></i></a>
                        <a href="list.php?delete=<?= $d['department_id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Xác nhận xóa?')"><i class="bi bi-trash-fill"></i></a>
                    </div></td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include '../../includes/footer.php'; ?>
