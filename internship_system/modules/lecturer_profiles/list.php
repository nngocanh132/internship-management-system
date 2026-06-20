<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';
requireRole(['admin','lecturer']);

if (isset($_GET['delete'])) {
    $psid=(int)$_GET['delete'];
    $s=$conn->prepare("DELETE FROM position_skills WHERE position_skill_id=?");
    $s->bind_param('i',$psid); $s->execute();
    setFlash('success','Đã gỡ kỹ năng khỏi vị trí.');
    redirect('list.php'.(isset($_GET['position_id'])?'?position_id='.(int)$_GET['position_id']:''));
}

$filter_pos=isset($_GET['position_id'])?(int)$_GET['position_id']:0;
$positions_all=$conn->query("SELECT position_id,title FROM internship_positions ORDER BY title")->fetch_all(MYSQLI_ASSOC);

$sql="SELECT ps.position_skill_id, p.position_id, p.title AS position_title,
             s.skill_id, s.skill_name, c.name AS company_name
      FROM position_skills ps
      JOIN internship_positions p ON ps.position_id=p.position_id
      JOIN skills s ON ps.skill_id=s.skill_id
      JOIN companies c ON p.company_id=c.company_id WHERE 1=1";
$params=[]; $types='';
if ($filter_pos>0) { $sql.=" AND ps.position_id=?"; $params[]=$filter_pos; $types.='i'; }
$sql.=" ORDER BY p.title,s.skill_name";
$stmt=$conn->prepare($sql);
if ($params) $stmt->bind_param($types,...$params);
$stmt->execute();
$rows=$stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<?php include '../../includes/header.php'; ?>
<div class="page-header">
    <div>
        <h4><i class="bi bi-tags-fill me-2" style="color:#34d399"></i>Kỹ năng theo Vị trí Thực tập</h4>
        <div class="page-subtitle">Tổng: <?= count($rows) ?> liên kết</div>
    </div>
    <a href="add.php" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>Gán kỹ năng</a>
</div>
<?php showFlash(); ?>
<div class="card mb-4"><div class="card-body py-3">
    <form method="GET" class="row g-2 align-items-end">
        <div class="col-md-7">
            <label class="form-label mb-1">Lọc theo vị trí</label>
            <select name="position_id" class="form-select">
                <option value="0">— Tất cả vị trí —</option>
                <?php foreach ($positions_all as $p): ?>
                <option value="<?= $p['position_id'] ?>" <?= $filter_pos===$p['position_id']?'selected':'' ?>><?= htmlspecialchars($p['title']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2"><label class="form-label mb-1 d-block">&nbsp;</label>
            <button type="submit" class="btn btn-primary w-100"><i class="bi bi-funnel me-1"></i>Lọc</button></div>
        <div class="col-md-2"><label class="form-label mb-1 d-block">&nbsp;</label>
            <a href="list.php" class="btn btn-secondary w-100"><i class="bi bi-x-lg me-1"></i>Xóa lọc</a></div>
    </form>
</div></div>
<div class="card table-card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-table me-2"></i>Danh sách Kỹ năng – Vị trí</span>
        <span class="badge" style="background:#d1fae5;color:#065f46"><?= count($rows) ?> kết quả</span>
    </div>
    <div class="card-body p-0">
        <table class="table mb-0">
            <thead><tr><th>#</th><th>Vị trí thực tập</th><th>Doanh nghiệp</th><th>Kỹ năng</th><th style="width:80px">Thao tác</th></tr></thead>
            <tbody>
            <?php if (empty($rows)): ?>
                <tr><td colspan="5" class="text-center py-5"><i class="bi bi-inbox fs-1 d-block mb-2 text-muted opacity-25"></i><span class="text-muted">Không có dữ liệu</span></td></tr>
            <?php else: ?>
                <?php foreach ($rows as $i=>$r): ?>
                <tr>
                    <td style="color:#94a3b8;font-size:.8rem"><?= $i+1 ?></td>
                    <td class="fw-semibold"><?= htmlspecialchars($r['position_title']) ?></td>
                    <td style="color:#64748b;font-size:.82rem"><?= htmlspecialchars($r['company_name']) ?></td>
                    <td><span class="badge" style="background:#fef3c7;color:#92400e"><i class="bi bi-lightning-charge-fill me-1"></i><?= htmlspecialchars($r['skill_name']) ?></span></td>
                    <td><a href="list.php?delete=<?= $r['position_skill_id'] ?><?= $filter_pos?'&position_id='.$filter_pos:'' ?>" class="btn btn-danger btn-sm" onclick="return confirm('Gỡ kỹ năng này?')"><i class="bi bi-trash-fill"></i></a></td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include '../../includes/footer.php'; ?>
