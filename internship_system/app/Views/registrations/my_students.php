<?php // View: registrations/my_students — nhận $students từ RegistrationController ?>
<?php include BASE_PATH_FS . 'includes/header.php'; ?>
<div class="ph fu">
  <div><h4><i class="bi bi-people-fill me-2"></i>Sinh viên được phân công</h4><div class="ph-sub">Tổng: <?=count($students)?></div></div>
</div>
<?php showFlash(); ?>

<?php if(empty($students)): ?>
<div class="card text-center py-5 fu1"><div class="card-body">
  <i class="bi bi-people" style="font-size:3rem;color:var(--tl)"></i>
  <h5 class="mt-3 fw7">Chưa có sinh viên được phân công</h5>
</div></div>
<?php else: ?>
<div class="row g-3 fu1">
<?php foreach($students as $i=>$s):
  $av=($s['s_av']??'')?UPLOAD_URL.'/'.$s['s_av']:'https://ui-avatars.com/api/?name='.urlencode($s['full_name']).'&background=5D7B6F&color=fff&size=60';
  $rp_map=['pending'=>['⏳ Chờ duyệt','#a07040'],'approved'=>['✅ Đã duyệt','#2d6a40'],'rejected'=>['❌ Cần sửa','#9a3030']];
  [$rl,$rc]=isset($s['report_status'])?($rp_map[$s['report_status']]??['Chưa nộp','#5a5a5a']):['Chưa nộp','#5a5a5a'];
?>
<div class="col-md-6" style="animation:fadeUp .32s <?=$i*.04?>s ease both">
  <div class="card h-100" style="border:1.5px solid rgba(164,195,162,.25)"><div class="card-body">
    <div class="d-flex align-items-center gap-3 mb-3">
      <img src="<?=$av?>" style="width:48px;height:48px;border-radius:50%;object-fit:cover;flex-shrink:0">
      <div style="flex:1">
        <div class="fw7"><?=htmlspecialchars($s['full_name'])?></div>
        <div class="small text-muted"><?=htmlspecialchars($s['student_code']??'')?> · GPA: <strong><?=$s['gpa']??'—'?></strong></div>
        <?php if($s['major']??''): ?><div class="small text-muted"><?=htmlspecialchars($s['major'])?></div><?php endif; ?>
      </div>
      <span class="badge" style="<?=$s['status']==='active'?'background:rgba(74,158,106,.12);color:#2d6a40':'background:rgba(93,123,111,.12);color:var(--ds)'?>"><?=$s['status']==='active'?'🚀 TT':'🏆 Xong'?></span>
    </div>
    <div class="small mb-2"><i class="bi bi-briefcase me-1 text-muted"></i><strong><?=htmlspecialchars($s['title'])?></strong> <span class="text-muted">@ <?=htmlspecialchars($s['company_name'])?></span></div>
    <?php if($s['j_start']??$s['j_end']??''): ?>
    <div class="small text-muted mb-2"><i class="bi bi-calendar3 me-1"></i><?=($s['j_start']??'')?date('d/m/Y',strtotime($s['j_start'])):''?> → <?=($s['j_end']??'')?date('d/m/Y',strtotime($s['j_end'])):'?'?></div>
    <?php endif; ?>
    <div class="d-flex justify-content-between align-items-center mb-2">
      <span style="color:<?=$rc?>;font-weight:600;font-size:.82rem"><?=$rl?></span>
      <?php if($s['overall_score']??0): ?><span class="badge bg-primary">Điểm: <?=number_format($s['overall_score'],1)?>/10</span><?php endif; ?>
    </div>
    <div class="d-flex gap-2 mt-2">
      <a href="<?=BASE_PATH?>/messages/lecturer_chat.php?student_uid=<?=$s['s_user_id']?>" class="btn btn-primary btn-sm flex-fill">
        <i class="bi bi-chat-dots-fill me-1"></i>Nhắn tin
      </a>
      <?php if(($s['report_status']??'')==='pending'): ?>
      <a href="<?=BASE_PATH?>/reports/list.php" class="btn btn-warning btn-sm" title="Duyệt báo cáo"><i class="bi bi-file-earmark-check"></i></a>
      <?php endif; ?>
    </div>
  </div></div>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>
<?php include BASE_PATH_FS . 'includes/footer.php'; ?>
