<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';
requireRole(['admin','lecturer']);

$uid  = $_SESSION['user_id'];
$role = getRole();

if (isset($_GET['approve'])) {
    // Chỉ giảng viên mới được duyệt
    if ($role !== 'lecturer') { setFlash('error','❌ Chỉ Giảng viên hướng dẫn mới có quyền duyệt báo cáo.'); redirect('list.php'); }
    $id = (int)$_GET['approve'];
    $u  = $conn->prepare("UPDATE internship_reports SET status='approved',reviewed_at=NOW() WHERE report_id=?");
    if ($u) { $u->bind_param('i',$id); $u->execute(); }
    // Sau khi duyệt → kỳ thực tập hoàn thành
    $uc = $conn->prepare("UPDATE internship_registrations SET status='completed' WHERE registration_id=(SELECT registration_id FROM internship_reports WHERE report_id=?)");
    if ($uc) { $uc->bind_param('i',$id); $uc->execute(); }
    setFlash('success','✅ Đã duyệt báo cáo — Kỳ thực tập hoàn thành!');
    redirect('list.php');
}

if (isset($_GET['reject'])) {
    // Chỉ giảng viên mới được từ chối
    if ($role !== 'lecturer') { setFlash('error','❌ Chỉ Giảng viên hướng dẫn mới có quyền từ chối báo cáo.'); redirect('list.php'); }
    $id   = (int)$_GET['reject'];
    $note = sanitize($_GET['note'] ?? 'Cần chỉnh sửa thêm.');
    $u    = $conn->prepare("UPDATE internship_reports SET status='rejected',lecturer_comment=?,reviewed_at=NOW() WHERE report_id=?");
    if ($u) { $u->bind_param('si',$note,$id); $u->execute(); }
    setFlash('info','Đã yêu cầu sinh viên chỉnh sửa.');
    redirect('list.php');
}

$lid_cond = '';
if ($role === 'lecturer') {
    $lq = $conn->prepare("SELECT lecturer_id FROM lecturer_profiles WHERE user_id=?");
    $lid = 0;
    if ($lq) { $lq->bind_param('i',$uid); $lq->execute(); $lid = $lq->get_result()->fetch_assoc()['lecturer_id'] ?? 0; }
    $lid_cond = "AND ir.lecturer_id=$lid";
}

$reports = safeQuery($conn,"SELECT rp.*,sp.full_name,sp.student_code,sp.avatar AS s_av,
  cp.company_name,i.title,lp.full_name AS lecturer_name
  FROM internship_reports rp
  JOIN internship_registrations ir ON rp.registration_id=ir.registration_id
  JOIN student_profiles sp ON ir.student_id=sp.student_id
  JOIN internships i ON ir.internship_id=i.internship_id
  JOIN company_profiles cp ON ir.company_id=cp.company_id
  LEFT JOIN lecturer_profiles lp ON ir.lecturer_id=lp.lecturer_id
  WHERE 1=1 $lid_cond
  ORDER BY rp.submitted_at DESC");
?>
<?php include '../../includes/header.php'; ?>
<div class="ph fu">
  <div><h4><i class="bi bi-file-earmark-text-fill me-2"></i>Báo cáo Thực tập</h4><div class="ph-sub">Tổng: <?=count($reports)?></div></div>
</div>
<?php showFlash(); ?>

<?php if(empty($reports)): ?>
<div class="card text-center py-5 fu1"><div class="card-body">
  <i class="bi bi-file-earmark" style="font-size:3rem;color:var(--tl)"></i>
  <h5 class="mt-3 fw7">Chưa có báo cáo</h5>
  <p class="text-muted">Sinh viên nộp báo cáo khi kỳ thực tập kết thúc.</p>
</div></div>
<?php else: ?>
<div class="card tc fu1"><div class="card-body p-0">
  <table class="table mb-0">
    <thead>
      <tr><th>#</th><th>Sinh viên</th><th>Vị trí / DN</th><th>GVHD</th><th>File</th><th>Nộp lúc</th><th>Trạng thái</th><th>Thao tác</th></tr>
    </thead>
    <tbody>
    <?php foreach($reports as $i=>$r):
      $sc_map = ['pending'=>['⏳ Chờ duyệt','rgba(196,154,108,.12)','#a07040'],'approved'=>['✅ Đã duyệt','rgba(74,158,106,.12)','#2d6a40'],'rejected'=>['❌ Cần sửa','rgba(192,96,80,.12)','#9a3030']];
      [$sl,$sb,$sc] = $sc_map[$r['status']] ?? ['—','rgba(160,160,160,.1)','#5a5a5a'];
      $av = $r['s_av'] ? UPLOAD_URL.'/'.$r['s_av'] : null;
    ?>
    <tr>
      <td class="small text-muted"><?=$i+1?></td>
      <td>
        <div class="d-flex align-items-center gap-2">
          <?php if($av): ?><img src="<?=$av?>" style="width:30px;height:30px;border-radius:7px;object-fit:cover">
          <?php else: ?><div class="av"><?=strtoupper(mb_substr($r['full_name'],0,1))?></div><?php endif; ?>
          <div>
            <div class="fw7 small"><?=htmlspecialchars($r['full_name'])?></div>
            <div class="small text-muted"><?=htmlspecialchars($r['student_code']??'')?></div>
          </div>
        </div>
      </td>
      <td>
        <div class="fw7 small"><?=htmlspecialchars($r['title'])?></div>
        <div class="small text-muted"><?=htmlspecialchars($r['company_name'])?></div>
      </td>
      <td class="small"><?=htmlspecialchars($r['lecturer_name']??'—')?></td>
      <td>
        <?php if($r['report_file']): ?>
        <a href="<?=UPLOAD_URL.'/'.$r['report_file']?>" target="_blank" class="btn btn-secondary btn-sm">
          <i class="bi bi-file-earmark-pdf"></i>
        </a>
        <?php else: ?>—<?php endif; ?>
      </td>
      <td class="small text-muted"><?=date('d/m/Y H:i',strtotime($r['submitted_at']))?></td>
      <td><span class="badge" style="background:<?=$sb?>;color:<?=$sc?>"><?=$sl?></span></td>
      <td>
        <div class="d-flex gap-1">
          <?php if($r['status']==='pending' && $role==='lecturer'): ?>
          <a href="?approve=<?=$r['report_id']?>" class="btn btn-success btn-sm" onclick="return confirm('Duyệt báo cáo này?')" title="Duyệt"><i class="bi bi-check-lg"></i></a>
          <button class="btn btn-warning btn-sm" onclick="rejectReport(<?=$r['report_id']?>)" title="Yêu cầu sửa"><i class="bi bi-arrow-return-left"></i></button>
          <?php endif; ?>
          <?php if($r['report_file']): ?>
          <a href="<?=UPLOAD_URL.'/'.$r['report_file']?>" target="_blank" class="btn btn-primary btn-sm" title="Xem"><i class="bi bi-eye"></i></a>
          <?php endif; ?>
        </div>
      </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div></div>
<?php endif; ?>

<script>
function rejectReport(id){
  var note = prompt('Lý do yêu cầu chỉnh sửa:', 'Báo cáo cần bổ sung thêm nội dung chi tiết.');
  if (note !== null) window.location.href = 'list.php?reject=' + id + '&note=' + encodeURIComponent(note);
}
</script>
<?php include '../../includes/footer.php'; ?>
