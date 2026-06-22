<?php // View: reports/list — nhận $reports, $role từ controller ?>
<?php include BASE_PATH_FS . 'includes/header.php'; ?>
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
      <td><div class="fw7 small"><?=htmlspecialchars($r['title'])?></div><div class="small text-muted"><?=htmlspecialchars($r['company_name'])?></div></td>
      <td class="small"><?=htmlspecialchars($r['lecturer_name']??'—')?></td>
      <td><?php if($r['report_file']): ?><a href="<?=UPLOAD_URL.'/'.$r['report_file']?>" target="_blank" class="btn btn-secondary btn-sm"><i class="bi bi-file-earmark-pdf"></i></a><?php else: ?>—<?php endif; ?></td>
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
<?php include BASE_PATH_FS . 'includes/footer.php'; ?>
