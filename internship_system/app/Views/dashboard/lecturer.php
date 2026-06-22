<?php // View: dashboard/lecturer — nhận $lp,$active,$total,$pending_reports,$rows ?>
<?php include BASE_PATH_FS . 'includes/header.php'; ?>
<div class="fu mb-4">
  <h3 class="fw8" style="font-size:1.5rem;font-family:'Plus Jakarta Sans',sans-serif">
    Xin chào, <?=htmlspecialchars($lp['full_name']??$_SESSION['full_name']??'Giảng viên')?> 👨‍🏫
  </h3>
  <?php if($lp&&$lp['department']): ?><p class="small text-muted"><?=htmlspecialchars($lp['department'])?></p><?php endif; ?>
</div>

<div class="row g-3 mb-4">
  <div class="col-md-4 fu"><div class="stat-card sc-green"><div class="s-bg"><i class="bi bi-people"></i></div><div class="s-icon"><i class="bi bi-people-fill"></i></div><div class="s-num"><?=$active?></div><div class="s-lbl">SV đang thực tập</div><a href="<?=BASE_PATH?>/registrations/my_students.php" class="s-link">Xem <i class="bi bi-arrow-right"></i></a></div></div>
  <div class="col-md-4 fu1"><div class="stat-card sc-warm"><div class="s-bg"><i class="bi bi-file-earmark"></i></div><div class="s-icon"><i class="bi bi-file-earmark-check-fill"></i></div><div class="s-num"><?=$pending_reports?></div><div class="s-lbl">Báo cáo chờ duyệt</div><a href="<?=BASE_PATH?>/reports/list.php" class="s-link">Duyệt ngay <i class="bi bi-arrow-right"></i></a></div></div>
  <div class="col-md-4 fu2"><div class="stat-card sc-sage"><div class="s-bg"><i class="bi bi-mortarboard"></i></div><div class="s-icon"><i class="bi bi-mortarboard-fill"></i></div><div class="s-num"><?=$total?></div><div class="s-lbl">Tổng SV hướng dẫn</div></div></div>
</div>

<div class="card tc fu3"><div class="card-header d-flex justify-content-between">
  <span><i class="bi bi-people-fill me-2"></i>Sinh viên được phân công</span>
  <a href="<?=BASE_PATH?>/reports/list.php" class="btn btn-warning btn-sm"><i class="bi bi-file-earmark-check me-1"></i>Duyệt báo cáo</a>
</div><div class="card-body p-0">
  <table class="table mb-0">
    <thead><tr><th>#</th><th>Sinh viên</th><th>Vị trí / DN</th><th>TT</th><th>Báo cáo</th></tr></thead>
    <tbody>
    <?php if(empty($rows)): ?>
      <tr><td colspan="5" class="text-center py-5 text-muted"><i class="bi bi-people fs-1 d-block mb-2 opacity-25"></i>Chưa có sinh viên được phân công</td></tr>
    <?php else: foreach($rows as $i=>$s):
      $av=($s['s_av']??'')?UPLOAD_URL.'/'.$s['s_av']:null;
      $rp_map=['pending'=>['⏳ Chờ duyệt','#a07040'],'approved'=>['✅ Đã duyệt','#2d6a40'],'rejected'=>['❌ Cần sửa','#9a3030']];
      [$rl,$rc]=isset($s['rep_status'])?($rp_map[$s['rep_status']]??['Chưa nộp','#5a5a5a']):['Chưa nộp','#5a5a5a'];
    ?>
    <tr>
      <td class="small text-muted"><?=$i+1?></td>
      <td><div class="d-flex align-items-center gap-2">
        <?php if($av): ?><img src="<?=$av?>" style="width:30px;height:30px;border-radius:7px;object-fit:cover">
        <?php else: ?><div class="av"><?=strtoupper(mb_substr($s['full_name'],0,1))?></div><?php endif; ?>
        <div><div class="fw7 small"><?=htmlspecialchars($s['full_name'])?></div><div class="small text-muted"><?=htmlspecialchars($s['student_code']??'')?> · GPA: <?=$s['gpa']?></div></div>
      </div></td>
      <td><div class="fw7 small"><?=htmlspecialchars($s['title'])?></div><div class="small text-muted"><?=htmlspecialchars($s['company_name'])?></div></td>
      <td><span class="badge" style="<?=$s['status']==='active'?'background:rgba(74,158,106,.12);color:#2d6a40':'background:rgba(93,123,111,.12);color:var(--ds)'?>"><?=$s['status']==='active'?'🚀 Đang TT':'🏆 Xong'?></span></td>
      <td><span style="font-size:.8rem;font-weight:600;color:<?=$rc?>"><?=$rl?></span></td>
    </tr>
    <?php endforeach; endif; ?>
    </tbody>
  </table>
</div></div>
<?php include BASE_PATH_FS . 'includes/footer.php'; ?>
