<?php // View: applications/my_applications — nhận $rows từ controller ?>
<?php include BASE_PATH_FS . 'includes/header.php'; ?>
<div class="ph fu">
  <div><h4><i class="bi bi-clipboard-check-fill me-2"></i>Đơn ứng tuyển của tôi</h4><div class="ph-sub">Tổng: <?=count($rows)?> đơn</div></div>
  <a href="../internships/browse.php" class="btn btn-primary"><i class="bi bi-search me-1"></i>Tìm thêm</a>
</div>
<?php showFlash(); ?>

<?php if(empty($rows)): ?>
<div class="card text-center py-5 fu1"><div class="card-body">
  <i class="bi bi-clipboard-x" style="font-size:3rem;color:var(--tl)"></i>
  <h5 class="mt-3 fw7">Chưa có đơn ứng tuyển nào</h5>
  <p class="text-muted">Hãy tìm vị trí thực tập và nộp đơn ngay!</p>
  <a href="../internships/browse.php" class="btn btn-primary"><i class="bi bi-search me-1"></i>Tìm việc thực tập</a>
</div></div>
<?php else: foreach($rows as $i=>$a):
  [$lbl,$bg,$c]=appStatusLabel($a['status']);
  $logoUrl=$a['logo']?UPLOAD_URL.'/'.$a['logo']:'https://ui-avatars.com/api/?name='.urlencode($a['company_name']).'&background=A4C3A2&color=2A3F38&size=60';
?>
<div class="card mb-3 fu" style="border-left:4px solid <?=$c?>;animation-delay:<?=$i*.04?>s">
  <div class="card-body">
    <div class="row align-items-center g-3">
      <div class="col-md-7">
        <div class="d-flex align-items-center gap-3 mb-2">
          <img src="<?=$logoUrl?>" style="width:40px;height:40px;border-radius:10px;object-fit:cover;flex-shrink:0">
          <div>
            <h6 class="fw8 mb-0" style="font-size:.95rem"><?=htmlspecialchars($a['title'])?></h6>
            <div class="small" style="color:var(--tl)"><?=htmlspecialchars($a['company_name'])?><?=$a['location']?' · '.htmlspecialchars($a['location']):''?></div>
          </div>
        </div>
        <!-- Progress flow -->
        <div class="d-flex align-items-center gap-1 my-2">
          <?php
          $flow=['pending_admin','approved_admin','approved_company','interview_passed','internship_active','internship_completed'];
          $flow_labels=['Chờ TN','TN duyệt','CTy duyệt','PV qua','TT','Hoàn thành'];
          $cur_idx=array_search($a['status'],$flow);
          $is_rejected=str_contains($a['status'],'rejected')||$a['status']==='interview_failed';
          foreach($flow as $fi=>$fv):
            $done=$cur_idx!==false&&$fi<=$cur_idx&&!$is_rejected;
            $active=$cur_idx===$fi&&!$is_rejected;
            $bg2=$is_rejected&&$fi<=$cur_idx?'#9a3030':($done?'var(--ds)':($active?'#a07040':'rgba(164,195,162,.3)'));
            $tc2=$done||$active||($is_rejected&&$fi<=$cur_idx)?'#fff':'var(--tl)';
          ?>
          <div style="flex:1;text-align:center">
            <div style="width:24px;height:24px;border-radius:50%;background:<?=$bg2?>;color:<?=$tc2?>;font-size:.62rem;display:flex;align-items:center;justify-content:center;margin:0 auto 2px;font-weight:700">
              <?=$is_rejected&&$fi===$cur_idx?'✗':($done?($fi<$cur_idx?'✓':$fi+1):$fi+1)?>
            </div>
            <div style="font-size:.55rem;color:var(--tl)"><?=$flow_labels[$fi]?></div>
          </div>
          <?php if($fi<count($flow)-1): ?>
          <div style="flex:1;height:2px;background:<?=$done&&$fi<$cur_idx?'var(--ds)':'rgba(164,195,162,.25)'?>;margin-bottom:14px"></div>
          <?php endif; ?>
          <?php endforeach; ?>
        </div>
        <div class="small text-muted"><i class="bi bi-calendar3 me-1"></i>Nộp: <?=date('d/m/Y H:i',strtotime($a['applied_at']))?></div>
      </div>
      <div class="col-md-5 text-md-end">
        <div class="mb-2"><span class="badge" style="background:<?=$bg?>;color:<?=$c?>;padding:6px 12px;font-size:.78rem"><?=$lbl?></span></div>
        <div class="d-flex gap-2 justify-content-md-end flex-wrap mt-2">
          <?php if($a['cv_file']): ?>
          <a href="<?=UPLOAD_URL.'/'.$a['cv_file']?>" target="_blank" class="btn btn-secondary btn-sm"><i class="bi bi-file-earmark-pdf me-1"></i>CV</a>
          <?php endif; ?>
          <?php if(in_array($a['status'],['approved_company','interview_passed','internship_active','internship_completed'])): ?>
          <a href="<?=BASE_PATH?>/messages/chat.php?company_id=<?=$a['company_id']?>&app_id=<?=$a['application_id']?>" class="btn btn-primary btn-sm">
            <i class="bi bi-chat-dots-fill me-1"></i>Nhắn tin
          </a>
          <?php endif; ?>
          <?php if($a['status']==='internship_active'): ?>
          <a href="<?=BASE_PATH?>/registrations/my_internship.php" class="btn btn-success btn-sm"><i class="bi bi-briefcase-fill me-1"></i>Xem TT</a>
          <?php elseif($a['status']==='internship_completed'): ?>
          <a href="<?=BASE_PATH?>/registrations/my_internship.php" class="btn btn-sm" style="background:rgba(74,158,106,.15);color:#2d6a40;border:1px solid rgba(74,158,106,.3)"><i class="bi bi-trophy-fill me-1"></i>Xem kết quả</a>
          <?php endif; ?>
        </div>
        <?php if($a['interview_date']): ?>
        <div class="mt-2 p-2" style="background:rgba(74,138,150,.08);border-radius:8px;font-size:.78rem">
          <div class="fw7" style="color:#3a8a96"><i class="bi bi-camera-video me-1"></i>Lịch phỏng vấn</div>
          <div><?=date('d/m/Y H:i',strtotime($a['interview_date']))?></div>
          <?php if($a['iv_address']): ?><div><i class="bi bi-geo-alt me-1"></i><?=htmlspecialchars($a['iv_address'])?></div><?php endif; ?>
          <?php if($a['meeting_link']): ?><a href="<?=htmlspecialchars($a['meeting_link'])?>" target="_blank" class="btn btn-sm" style="background:rgba(74,138,150,.15);color:#3a8a96;padding:2px 10px;border-radius:5px;font-size:.72rem;"><i class="bi bi-camera-video me-1"></i>Tham gia</a><?php endif; ?>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
<?php endforeach; endif; ?>
<?php include BASE_PATH_FS . 'includes/footer.php'; ?>
