<?php // View: interviews/list — nhận $ivs, $role ?>
<?php include BASE_PATH_FS . 'includes/header.php'; ?>
<div class="ph fu"><div><h4><i class="bi bi-camera-video-fill me-2"></i>Lịch Phỏng vấn</h4><div class="ph-sub">Tổng: <?=count($ivs)?></div></div></div>
<?php showFlash(); ?>
<?php if(empty($ivs)): ?>
<div class="card text-center py-5 fu1"><div class="card-body">
  <i class="bi bi-camera-video" style="font-size:3rem;color:var(--tl)"></i>
  <h5 class="mt-3 fw7">Chưa có lịch phỏng vấn</h5>
  <p class="text-muted">Lịch phỏng vấn được tạo khi doanh nghiệp nhắn tin hẹn lịch.</p>
</div></div>
<?php else: ?>
<div class="row g-3 fu1">
<?php foreach($ivs as $i=>$iv):
  $result_map=['pending'=>['⏳ Chờ kết quả','rgba(196,154,108,.12)','#a07040'],'passed'=>['🎉 Đậu phỏng vấn','rgba(74,158,106,.14)','#2d6a40'],'failed'=>['😞 Không đậu','rgba(192,96,80,.14)','#9a3030']];
  [$rl,$rb,$rc]=$result_map[$iv['result']]??['—','rgba(160,160,160,.1)','#5a5a5a'];
  $sav=$iv['s_av']?UPLOAD_URL.'/'.$iv['s_av']:'https://ui-avatars.com/api/?name='.urlencode($iv['full_name']).'&background=5D7B6F&color=fff&size=60';
  $clogo=$iv['c_logo']?UPLOAD_URL.'/'.$iv['c_logo']:'https://ui-avatars.com/api/?name='.urlencode($iv['company_name']).'&background=A4C3A2&color=2A3F38&size=60';
  $chat_params=$role==='student'?['company_id'=>$iv['company_id'],'app_id'=>$iv['application_id']]:['student_id'=>$iv['student_id'],'app_id'=>$iv['application_id']];
?>
<div class="col-md-6" style="animation:fadeUp .32s <?=$i*.04?>s ease both">
  <div class="card" style="border:1.5px solid <?=$rc?>;border-radius:14px"><div class="card-body">
    <div class="d-flex align-items-center gap-3 mb-3">
      <img src="<?=$sav?>" style="width:42px;height:42px;border-radius:50%;object-fit:cover">
      <div><div class="fw7"><?=htmlspecialchars($iv['full_name'])?></div><div class="small text-muted"><?=htmlspecialchars($iv['student_code']??'')?></div></div>
      <div class="ms-auto text-end"><img src="<?=$clogo?>" style="width:32px;height:32px;border-radius:8px;object-fit:cover"><div class="small text-muted" style="font-size:.7rem"><?=htmlspecialchars($iv['company_name'])?></div></div>
    </div>
    <div class="fw7 small mb-2" style="color:var(--td)"><?=htmlspecialchars($iv['title'])?></div>
    <div style="background:rgba(164,195,162,.08);border-radius:10px;padding:12px;margin-bottom:12px">
      <?php if($iv['interview_date']): ?><div class="small mb-1"><i class="bi bi-calendar-event me-2" style="color:var(--ds)"></i><strong><?=date('d/m/Y H:i',strtotime($iv['interview_date']))?></strong></div><?php endif; ?>
      <?php if($iv['address']??''): ?><div class="small mb-1"><i class="bi bi-geo-alt me-2 text-muted"></i><?=htmlspecialchars($iv['address'])?></div><?php endif; ?>
      <?php if($iv['meeting_link']??''): ?><div class="small"><i class="bi bi-camera-video me-2 text-muted"></i><a href="<?=htmlspecialchars($iv['meeting_link'])?>" target="_blank" class="btn btn-sm" style="background:rgba(74,138,150,.15);color:#3a8a96;padding:2px 10px;border-radius:5px"><i class="bi bi-camera-video me-1"></i>Tham gia</a></div><?php endif; ?>
    </div>
    <div class="d-flex justify-content-between align-items-center">
      <span class="badge" style="background:<?=$rb?>;color:<?=$rc?>;padding:5px 12px"><?=$rl?></span>
      <a href="<?=BASE_PATH?>/messages/chat.php?<?=http_build_query($chat_params)?>" class="btn btn-primary btn-sm"><i class="bi bi-chat-dots me-1"></i>Nhắn tin</a>
    </div>
  </div></div>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>
<?php include BASE_PATH_FS . 'includes/footer.php'; ?>
