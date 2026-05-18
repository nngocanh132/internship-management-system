<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';

// ---- Handle STATUS CHANGE (Approve / Reject) ----
if (isset($_GET['approve'])) {
    $id = (int)$_GET['approve'];

    // Fetch registration + position info
    $stmt = $conn->prepare(
        "SELECT r.*, p.quota, p.filled, p.status AS pos_status
         FROM internship_registrations r
         JOIN internship_positions p ON r.position_id = p.position_id
         WHERE r.registration_id = ?"
    );
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $reg = $stmt->get_result()->fetch_assoc();

    if (!$reg) {
        setFlash('error', 'Không tìm thấy đăng ký.');
    } elseif ($reg['pos_status'] === 'full') {
        setFlash('error', 'Không thể duyệt: Vị trí thực tập đã đầy chỗ.');
    } elseif ($reg['filled'] >= $reg['quota']) {
        setFlash('error', 'Không thể duyệt: Vị trí đã đạt quota tối đa.');
    } else {
        $conn->begin_transaction();
        try {
            // Approve registration
            $u = $conn->prepare("UPDATE internship_registrations SET status='approved' WHERE registration_id=?");
            $u->bind_param('i', $id);
            $u->execute();

            // Increment filled count
            $u2 = $conn->prepare("UPDATE internship_positions SET filled = filled + 1 WHERE position_id=?");
            $u2->bind_param('i', $reg['position_id']);
            $u2->execute();

            // Auto-close if full
            $u3 = $conn->prepare(
                "UPDATE internship_positions SET status='full'
                 WHERE position_id=? AND filled >= quota"
            );
            $u3->bind_param('i', $reg['position_id']);
            $u3->execute();

            $conn->commit();
            setFlash('success', 'Đã duyệt đăng ký thành công.');
        } catch (Exception $e) {
            $conn->rollback();
            setFlash('error', 'Lỗi khi duyệt: ' . $e->getMessage());
        }
    }
    redirect('list.php');
}

if (isset($_GET['reject'])) {
    $id = (int)$_GET['reject'];
    $stmt = $conn->prepare("UPDATE internship_registrations SET status='rejected' WHERE registration_id=?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    setFlash('success', 'Đã từ chối đăng ký.');
    redirect('list.php');
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    // BUSINESS RULE: Cannot delete approved registration
    $chk = $conn->prepare("SELECT status FROM internship_registrations WHERE registration_id=?");
    $chk->bind_param('i', $id);
    $chk->execute();
    $r = $chk->get_result()->fetch_assoc();
    if ($r && $r['status'] === 'approved') {
        setFlash('error', 'Không thể xóa đăng ký đã được duyệt.');
    } else {
        $stmt = $conn->prepare("DELETE FROM internship_registrations WHERE registration_id=?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        setFlash('success', 'Đã xóa đăng ký.');
    }
    redirect('list.php');
}

// ---- Fetch registrations ----
$status_f = isset($_GET['status']) ? sanitize($_GET['status']) : '';

$sql = "SELECT r.*, u.full_name AS student_name, u.student_code,
               p.title AS position_title, c.company_name
        FROM internship_registrations r
        JOIN users u ON r.student_id = u.user_id
        JOIN internship_positions p ON r.position_id = p.position_id
        JOIN companies c ON p.company_id = c.company_id
        WHERE 1=1";
$params = []; $types = '';

if ($status_f !== '') {
    $sql .= " AND r.status = ?";
    $params[] = $status_f; $types .= 's';
}
$sql .= " ORDER BY r.registered_at DESC";

$stmt = $conn->prepare($sql);
if ($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$regs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<?php include '../../includes/header.php'; ?>

<div class="page-header">
    <div>
        <h4><i class="bi bi-clipboard-check-fill me-2" style="color:#f59e0b"></i>Đăng ký Thực tập</h4>
        <div class="page-subtitle">Tổng: <?= count($regs) ?> đăng ký</div>
    </div>
    <a href="add.php" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg me-1"></i>Thêm đăng ký
    </a>
</div>

<?php showFlash(); ?>

<div class="card mb-4" style="border-radius:12px;">
    <div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label mb-1">Trạng thái</label>
                <select name="status" class="form-select">
                    <option value="">Tất cả trạng thái</option>
                    <option value="pending"   <?= $status_f==='pending'   ?'selected':'' ?>>Chờ duyệt</option>
                    <option value="approved"  <?= $status_f==='approved'  ?'selected':'' ?>>Đã duyệt</option>
                    <option value="rejected"  <?= $status_f==='rejected'  ?'selected':'' ?>>Từ chối</option>
                    <option value="cancelled" <?= $status_f==='cancelled' ?'selected':'' ?>>Đã hủy</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label mb-1 d-block">&nbsp;</label>
                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-funnel me-1"></i>Lọc</button>
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
        <span><i class="bi bi-table me-2"></i>Danh sách đăng ký</span>
        <span class="badge" style="background:#fef3c7;color:#92400e;font-size:.78rem;"><?= count($regs) ?> kết quả</span>
    </div>
    <div class="card-body p-0">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Sinh viên</th>
                    <th>Vị trí / Doanh nghiệp</th>
                    <th>Ngày đăng ký</th>
                    <th>Trạng thái</th>
                    <th style="width:140px">Thao tác</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($regs)): ?>
                <tr><td colspan="6" class="text-center py-5">
                    <i class="bi bi-inbox fs-1 d-block mb-2 text-muted opacity-25"></i>
                    <span class="text-muted">Không có dữ liệu</span>
                </td></tr>
            <?php else: ?>
                <?php foreach ($regs as $i => $r):
                    $sc = match($r['status']) {
                        'pending'   => ['background:#fef3c7;color:#92400e', 'Chờ duyệt'],
                        'approved'  => ['background:#dcfce7;color:#166534', 'Đã duyệt'],
                        'rejected'  => ['background:#fee2e2;color:#991b1b', 'Từ chối'],
                        'cancelled' => ['background:#f1f5f9;color:#64748b', 'Đã hủy'],
                        default     => ['background:#f1f5f9;color:#64748b', $r['status']]
                    };
                    $colors = ['#6366f1','#10b981','#f59e0b','#ef4444','#8b5cf6','#14b8a6'];
                    $color  = $colors[crc32($r['student_name']) % count($colors)];
                    $initials = strtoupper(mb_substr($r['student_name'], 0, 1));
                ?>
                <tr>
                    <td style="color:#94a3b8;font-size:.8rem"><?= $i + 1 ?></td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="avatar-sm" style="background:<?= $color ?>20;color:<?= $color ?>">
                                <?= $initials ?>
                            </div>
                            <div>
                                <div class="fw-semibold" style="font-size:.875rem"><?= htmlspecialchars($r['student_name']) ?></div>
                                <div class="text-muted" style="font-size:.75rem"><?= htmlspecialchars($r['student_code'] ?? '') ?></div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div style="font-size:.875rem;font-weight:500"><?= htmlspecialchars($r['position_title']) ?></div>
                        <div class="text-muted" style="font-size:.75rem"><?= htmlspecialchars($r['company_name']) ?></div>
                    </td>
                    <td style="font-size:.78rem;color:#94a3b8;white-space:nowrap">
                        <?= date('d/m/Y H:i', strtotime($r['registered_at'])) ?>
                    </td>
                    <td>
                        <span class="badge" style="<?= $sc[0] ?>"><?= $sc[1] ?></span>
                    </td>
                    <td>
                        <div class="d-flex gap-1 flex-wrap">
                            <?php if ($r['status'] === 'pending'): ?>
                            <a href="list.php?approve=<?= $r['registration_id'] ?>"
                               class="btn btn-success btn-sm" title="Duyệt"
                               onclick="return confirm('Duyệt đăng ký này?')">
                                <i class="bi bi-check-lg"></i>
                            </a>
                            <a href="list.php?reject=<?= $r['registration_id'] ?>"
                               class="btn btn-sm" style="background:#f1f5f9;color:#64748b;border:none;" title="Từ chối"
                               onclick="return confirm('Từ chối đăng ký này?')">
                                <i class="bi bi-x-lg"></i>
                            </a>
                            <?php endif; ?>
                            <a href="edit.php?id=<?= $r['registration_id'] ?>" class="btn btn-warning btn-sm">
                                <i class="bi bi-pencil-fill"></i>
                            </a>
                            <?php if ($r['status'] !== 'approved'): ?>
                            <a href="list.php?delete=<?= $r['registration_id'] ?>"
                               class="btn btn-danger btn-sm"
                               onclick="return confirm('Xóa đăng ký này?')">
                                <i class="bi bi-trash-fill"></i>
                            </a>
                            <?php endif; ?>
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
