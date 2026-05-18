<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';

// ---- Handle DELETE ----
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];

    // BUSINESS RULE: Cannot delete company that has internship positions
    $check = $conn->prepare("SELECT COUNT(*) AS cnt FROM internship_positions WHERE company_id = ?");
    $check->bind_param('i', $id);
    $check->execute();
    $row = $check->get_result()->fetch_assoc();

    if ($row['cnt'] > 0) {
        setFlash('error', 'Không thể xóa: Doanh nghiệp đang có vị trí thực tập liên kết.');
    } else {
        $stmt = $conn->prepare("DELETE FROM companies WHERE company_id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        setFlash('success', 'Đã xóa doanh nghiệp thành công.');
    }
    redirect('list.php');
}

$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';

$sql = "SELECT c.*, COUNT(p.position_id) AS total_positions
        FROM companies c
        LEFT JOIN internship_positions p ON c.company_id = p.company_id
        WHERE 1=1";
$params = []; $types = '';

if ($search !== '') {
    $sql .= " AND (c.company_name LIKE ? OR c.industry LIKE ? OR c.contact_email LIKE ?)";
    $like = "%$search%";
    $params = [$like, $like, $like];
    $types = 'sss';
}
$sql .= " GROUP BY c.company_id ORDER BY c.created_at DESC";

$stmt = $conn->prepare($sql);
if ($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$companies = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<?php include '../../includes/header.php'; ?>

<div class="page-header">
    <div>
        <h4><i class="bi bi-building-fill me-2" style="color:#10b981"></i>Quản lý Doanh nghiệp</h4>
        <div class="page-subtitle">Tổng: <?= count($companies) ?> doanh nghiệp đối tác</div>
    </div>
    <a href="add.php" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg me-1"></i>Thêm doanh nghiệp
    </a>
</div>

<?php showFlash(); ?>

<div class="card mb-4" style="border-radius:12px;">
    <div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-7">
                <label class="form-label mb-1">Tìm kiếm</label>
                <div class="input-group">
                    <span class="input-group-text" style="background:#f8fafc;border-color:#e2e8f0;border-radius:8px 0 0 8px;">
                        <i class="bi bi-search text-muted"></i>
                    </span>
                    <input type="text" name="search" class="form-control"
                           style="border-left:none;border-radius:0 8px 8px 0;"
                           placeholder="Tên, ngành, email..." value="<?= htmlspecialchars($search) ?>">
                </div>
            </div>
            <div class="col-md-2">
                <label class="form-label mb-1 d-block">&nbsp;</label>
                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search me-1"></i>Tìm</button>
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
        <span><i class="bi bi-table me-2"></i>Danh sách doanh nghiệp</span>
        <span class="badge" style="background:#dcfce7;color:#166534;font-size:.78rem;"><?= count($companies) ?> kết quả</span>
    </div>
    <div class="card-body p-0">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Doanh nghiệp</th>
                    <th>Ngành</th>
                    <th>Email liên hệ</th>
                    <th>Điện thoại</th>
                    <th>Vị trí</th>
                    <th>Trạng thái</th>
                    <th style="width:100px">Thao tác</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($companies)): ?>
                <tr><td colspan="8" class="text-center py-5">
                    <i class="bi bi-inbox fs-1 d-block mb-2 text-muted opacity-25"></i>
                    <span class="text-muted">Không có dữ liệu</span>
                </td></tr>
            <?php else: ?>
                <?php foreach ($companies as $i => $c):
                    $colors = ['#6366f1','#10b981','#f59e0b','#ef4444','#8b5cf6'];
                    $color  = $colors[$i % count($colors)];
                    $initials = strtoupper(mb_substr($c['company_name'], 0, 2));
                ?>
                <tr>
                    <td style="color:#94a3b8;font-size:.8rem"><?= $i + 1 ?></td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="avatar-sm" style="background:<?= $color ?>20;color:<?= $color ?>;font-size:.7rem;">
                                <?= $initials ?>
                            </div>
                            <strong><?= htmlspecialchars($c['company_name']) ?></strong>
                        </div>
                    </td>
                    <td style="color:#64748b;font-size:.82rem"><?= htmlspecialchars($c['industry'] ?? '—') ?></td>
                    <td style="color:#64748b;font-size:.82rem"><?= htmlspecialchars($c['contact_email'] ?? '—') ?></td>
                    <td style="color:#64748b;font-size:.82rem"><?= htmlspecialchars($c['phone'] ?? '—') ?></td>
                    <td>
                        <span class="badge" style="background:#eff6ff;color:#1d4ed8;"><?= $c['total_positions'] ?> vị trí</span>
                    </td>
                    <td>
                        <?php if ($c['status'] === 'active'): ?>
                        <span class="badge" style="background:#dcfce7;color:#166534;">
                            <i class="bi bi-circle-fill me-1" style="font-size:.5rem"></i>Hoạt động
                        </span>
                        <?php else: ?>
                        <span class="badge" style="background:#f1f5f9;color:#64748b;">Vô hiệu</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="edit.php?id=<?= $c['company_id'] ?>" class="btn btn-warning btn-sm">
                                <i class="bi bi-pencil-fill"></i>
                            </a>
                            <a href="list.php?delete=<?= $c['company_id'] ?>"
                               class="btn btn-danger btn-sm"
                               onclick="return confirm('Xác nhận xóa doanh nghiệp này?')">
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
