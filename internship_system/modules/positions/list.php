<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';

// ---- Handle DELETE ----
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];

    // BUSINESS RULE: Cannot delete position that has registrations
    $check = $conn->prepare("SELECT COUNT(*) AS cnt FROM internship_registrations WHERE position_id = ?");
    $check->bind_param('i', $id);
    $check->execute();
    $row = $check->get_result()->fetch_assoc();

    if ($row['cnt'] > 0) {
        setFlash('error', 'Không thể xóa: Vị trí này đã có sinh viên đăng ký.');
    } else {
        $stmt = $conn->prepare("DELETE FROM internship_positions WHERE position_id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        setFlash('success', 'Đã xóa vị trí thực tập thành công.');
    }
    redirect('list.php');
}

$search     = isset($_GET['search'])  ? sanitize($_GET['search'])  : '';
$company_f  = isset($_GET['company']) ? (int)$_GET['company']      : 0;
$status_f   = isset($_GET['status'])  ? sanitize($_GET['status'])  : '';

$sql = "SELECT p.*, c.company_name,
               (p.quota - p.filled) AS available
        FROM internship_positions p
        JOIN companies c ON p.company_id = c.company_id
        WHERE 1=1";
$params = []; $types = '';

if ($search !== '') {
    $sql .= " AND (p.title LIKE ? OR p.industry LIKE ?)";
    $like = "%$search%";
    $params = array_merge($params, [$like, $like]);
    $types .= 'ss';
}
if ($company_f > 0) {
    $sql .= " AND p.company_id = ?";
    $params[] = $company_f; $types .= 'i';
}
if ($status_f !== '') {
    $sql .= " AND p.status = ?";
    $params[] = $status_f; $types .= 's';
}
$sql .= " ORDER BY p.created_at DESC";

$stmt = $conn->prepare($sql);
if ($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$positions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$companies = $conn->query("SELECT company_id, company_name FROM companies WHERE status='active' ORDER BY company_name")->fetch_all(MYSQLI_ASSOC);
?>
<?php include '../../includes/header.php'; ?>

<div class="page-header">
    <div>
        <h4><i class="bi bi-briefcase-fill me-2" style="color:#8b5cf6"></i>Vị trí Thực tập</h4>
        <div class="page-subtitle">Tổng: <?= count($positions) ?> vị trí</div>
    </div>
    <a href="add.php" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg me-1"></i>Thêm vị trí
    </a>
</div>

<?php showFlash(); ?>

<div class="card mb-4" style="border-radius:12px;">
    <div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label mb-1">Tìm kiếm</label>
                <div class="input-group">
                    <span class="input-group-text" style="background:#f8fafc;border-color:#e2e8f0;border-radius:8px 0 0 8px;">
                        <i class="bi bi-search text-muted"></i>
                    </span>
                    <input type="text" name="search" class="form-control"
                           style="border-left:none;border-radius:0 8px 8px 0;"
                           placeholder="Tiêu đề, ngành..." value="<?= htmlspecialchars($search) ?>">
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label mb-1">Doanh nghiệp</label>
                <select name="company" class="form-select">
                    <option value="">Tất cả doanh nghiệp</option>
                    <?php foreach ($companies as $c): ?>
                    <option value="<?= $c['company_id'] ?>" <?= $company_f==$c['company_id']?'selected':'' ?>>
                        <?= htmlspecialchars($c['company_name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label mb-1">Trạng thái</label>
                <select name="status" class="form-select">
                    <option value="">Tất cả</option>
                    <option value="open"   <?= $status_f==='open'   ?'selected':'' ?>>Đang mở</option>
                    <option value="full"   <?= $status_f==='full'   ?'selected':'' ?>>Đã đầy</option>
                    <option value="closed" <?= $status_f==='closed' ?'selected':'' ?>>Đã đóng</option>
                </select>
            </div>
            <div class="col-md-1">
                <label class="form-label mb-1 d-block">&nbsp;</label>
                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search"></i></button>
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
        <span><i class="bi bi-table me-2"></i>Danh sách vị trí</span>
        <span class="badge" style="background:#ede9fe;color:#6d28d9;font-size:.78rem;"><?= count($positions) ?> kết quả</span>
    </div>
    <div class="card-body p-0">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Vị trí</th>
                    <th>Doanh nghiệp</th>
                    <th>Ngành</th>
                    <th>Quota</th>
                    <th>Còn lại</th>
                    <th>Thời gian</th>
                    <th>Trạng thái</th>
                    <th style="width:100px">Thao tác</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($positions)): ?>
                <tr><td colspan="9" class="text-center py-5">
                    <i class="bi bi-inbox fs-1 d-block mb-2 text-muted opacity-25"></i>
                    <span class="text-muted">Không có dữ liệu</span>
                </td></tr>
            <?php else: ?>
                <?php foreach ($positions as $i => $p):
                    $statusStyle = match($p['status']) {
                        'open'   => ['background:#dcfce7;color:#166534', 'Đang mở'],
                        'full'   => ['background:#fee2e2;color:#991b1b', 'Đã đầy'],
                        'closed' => ['background:#f1f5f9;color:#64748b', 'Đã đóng'],
                        default  => ['background:#f1f5f9;color:#64748b', $p['status']]
                    };
                ?>
                <tr>
                    <td style="color:#94a3b8;font-size:.8rem"><?= $i + 1 ?></td>
                    <td><strong style="font-size:.875rem"><?= htmlspecialchars($p['title']) ?></strong></td>
                    <td style="color:#64748b;font-size:.82rem"><?= htmlspecialchars($p['company_name']) ?></td>
                    <td style="color:#64748b;font-size:.82rem"><?= htmlspecialchars($p['industry'] ?? '—') ?></td>
                    <td class="text-center">
                        <span class="fw-semibold"><?= $p['quota'] ?></span>
                    </td>
                    <td class="text-center">
                        <span class="badge" style="background:<?= $p['available'] > 0 ? '#dcfce7;color:#166534' : '#fee2e2;color:#991b1b' ?>">
                            <?= $p['available'] ?>
                        </span>
                    </td>
                    <td style="font-size:.78rem;color:#94a3b8;white-space:nowrap">
                        <?= date('d/m/Y', strtotime($p['start_date'])) ?><br>
                        → <?= date('d/m/Y', strtotime($p['end_date'])) ?>
                    </td>
                    <td>
                        <span class="badge" style="<?= $statusStyle[0] ?>"><?= $statusStyle[1] ?></span>
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="edit.php?id=<?= $p['position_id'] ?>" class="btn btn-warning btn-sm">
                                <i class="bi bi-pencil-fill"></i>
                            </a>
                            <a href="list.php?delete=<?= $p['position_id'] ?>"
                               class="btn btn-danger btn-sm"
                               onclick="return confirm('Xác nhận xóa vị trí này?')">
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
