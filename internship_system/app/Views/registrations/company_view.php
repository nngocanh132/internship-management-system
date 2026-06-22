<?php // View: registrations/company_view — nhận $regs từ RegistrationController ?>
<?php include BASE_PATH_FS . 'includes/header.php'; ?>
<div class="ph fu">
  <div><h4><i class="bi bi-people-fill me-2"></i>Sinh viên đang thực tập</h4><div class="ph-sub">Tổng: <?=count($regs)?></div></div>
</div>
<?php showFlash(); ?>

<?php if(empty($regs)): ?>
<div class="card text-center py-5 fu1"><div class="card-body">
  <i class="bi bi-people" style="font-size:3rem;color:var(--tl)"></i>
  <h5 class="mt-3 fw7">Chưa có sinh viên nào</h5>
  <p class="text-muted">Sinh viên sẽ xuất hiện khi bắt đầu thực tập.</p>
</div></div>
<?php else: ?>
<div class="card tc fu1"><div class="card-body p-0"><table class="table mb-0">
  <thead><tr><th>#</th><th>Sinh viên</th><th>Vị trí</th><th>GVHD</th><th>Thời gian</th><th>Trạng thái</th></tr></thead>
  <tbody>
  <?php foreach($regs as $i=>$r):
    $av=($r['s_av']??'')?UPLOAD_URL.'/'.$r['s_av']:null;
  ?>
  <tr>
    <td class="small text-muted"><?=$i+1?></td>
    <td><div class="d-flex align-items-center gap-2">
      <?php if($av): ?><img src="<?=$av?>" style="width:30px;height:30px;border-radius:7px;object-fit:cover">
      <?php else: ?><div class="av"><?=strtoupper(mb_substr($r['full_name'],0,1))?></div><?php endif; ?>
      <div><div class="fw7 small"><?=htmlspecialchars($r['full_name'])?></div><div class="small text-muted"><?=htmlspecialchars($r['student_code']??'')?> · GPA: <?=$r['gpa']??'—'?></div></div>
    </div></td>
    <td class="fw7 small"><?=htmlspecialchars($r['title'])?></td>
    <td class="small"><?=htmlspecialchars($r['lecturer_name']??'Chưa phân công')?></td>
    <td class="small text-muted"><?=$r['start_date']?date('d/m/Y',strtotime($r['start_date'])):''?><?php if($r['end_date']??''): ?><br>→ <?=date('d/m/Y',strtotime($r['end_date']))?><?php endif; ?></td>
    <td><span class="badge" style="<?=$r['status']==='active'?'background:rgba(74,158,106,.12);color:#2d6a40':'background:rgba(93,123,111,.12);color:var(--ds)'?>"><?=$r['status']==='active'?'🚀 Đang TT':'🏆 Hoàn thành'?></span></td>
  </tr>
  <?php endforeach; ?>
  </tbody>
</table></div></div>
<?php endif; ?>
<?php include BASE_PATH_FS . 'includes/footer.php'; ?>
