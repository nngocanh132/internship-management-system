<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';
requireRole('admin');

$search=sanitize($_GET['q']??''); $status_f=sanitize($_GET['status']??'');
$sql="SELECT i.*,cp.company_name,(SELECT COUNT(*) FROM applications WHERE internship_id=i.internship_id) AS app_count FROM internships i JOIN company_profiles cp ON i.company_id=cp.company_id WHERE 1=1";
$p=[]; $t='';
if($search){$sql.=" AND (i.title LIKE ? OR cp.company_name LIKE ?)";$like="%$search%";$p[]=$like;$p[]=$like;$t='ss';}
if($status_f){$sql.=" AND i.status=?";$p[]=$status_f;$t.='s';}
$sql.=" ORDER BY i.created_at DESC";
$st=$conn->prepare($sql); if($p) $st->bind_param($t,...$p); $st->execute();
$jobs=$st->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<?php include '../../includes/header.php'; ?>
<div class="ph fu"><div><h4><i class="bi bi-briefcase-fill me-2"></i>Vị trí Thực tập</h4><div class="ph-sub">Tổng: <?=count($jobs)?></div></div></div>
<?php showFlash(); ?>
<div class="card mb-3 fu1"><div class="card-body py-2">
  <form method="GET" class="d-flex gap-2">
    <input type="text" name="q" class="form-control" placeholder="Tìm tiêu đề, công ty..." value="<?=htmlspecialchars($search)?>">
    <select name="status" class="form-select" style="width:160px;flex-shrink:0">
      <option value="">Tất cả</option><option value="open" <?=$status_f==='open'?'selected':''?>>Đang mở</option><option value="closed" <?=$status_f==='closed'?'selected':''?>>Đã đóng</option>
    </select>
    <button type="submit" class="btn btn-primary px-4">Tìm</button>
    <?php if($search||$status_f): ?><a href="list.php" class="btn btn-secondary">Xóa</a><?php endif; ?>
  </form>
</div></div>
<div class="card tc fu2"><div class="card-body p-0">
  <table class="table mb-0">
    <thead><tr><th>#</th><th>Vị trí</th><th>Doanh nghiệp</th><th>Địa điểm</th><th class="text-center">SL</th><th class="text-center">Đơn</th><th>Trạng thái</th><th>Thao tác</th></tr></thead>
    <tbody>
    <?php if(empty($jobs)): ?><tr><td colspan="8" class="text-center py-5 text-muted">Không có dữ liệu</td></tr>
    <?php else: foreach($jobs as $i=>$j): ?>
    <tr>
      <td class="text-muted small"><?=$i+1?></td>
      <td><div class="fw7 small"><?=htmlspecialchars($j['title'])?></div><?php if($j['start_date']): ?><div class="small text-muted"><?=date('d/m/Y',strtotime($j['start_date']))?></div><?php endif; ?></td>
      <td class="small"><?=htmlspecialchars($j['company_name'])?></td>
      <td class="small text-muted"><?=htmlspecialchars($j['location']??'—')?></td>
      <td class="text-center fw7"><?=$j['quantity']?></td>
      <td class="text-center"><a href="../applications/list.php?internship_id=<?=$j['internship_id']?>" class="badge bg-primary" style="text-decoration:none"><?=$j['app_count']?></a></td>
      <td><span class="badge" style="<?=$j['status']==='open'?'background:rgba(74,158,106,.12);color:#2d6a40':'background:rgba(160,160,160,.12);color:#5a5a5a'?>"><?=$j['status']==='open'?'Đang mở':'Đóng'?></span></td>
      <td><a href="edit.php?id=<?=$j['internship_id']?>" class="btn btn-warning btn-sm"><i class="bi bi-pencil"></i></a></td>
    </tr>
    <?php endforeach; endif; ?>
    </tbody>
  </table>
</div></div>
<?php include '../../includes/footer.php'; ?>
