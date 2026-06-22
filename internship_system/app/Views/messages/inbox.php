<?php // View: messages/inbox — nhận $convos, $role từ controller ?>
<?php include BASE_PATH_FS . 'includes/header.php'; ?>
<div class="ph fu">
  <div><h4><i class="bi bi-chat-dots-fill me-2"></i>Hộp thư</h4><div class="ph-sub"><?=count($convos)?> cuộc trò chuyện</div></div>
</div>
<?php showFlash(); ?>

<?php if(empty($convos)): ?>
<div class="card text-center py-5 fu1"><div class="card-body">
  <i class="bi bi-chat-square" style="font-size:3rem;color:var(--tl)"></i>
  <h5 class="mt-3 fw7">Chưa có tin nhắn</h5>
  <p class="text-muted">Tin nhắn sẽ xuất hiện khi công ty chấp nhận đơn ứng tuyển của bạn.</p>
</div></div>
<?php else: ?>
<div class="row g-3 fu1">
<?php foreach($convos as $i=>$cv):
  $av=$cv['partner_av']?UPLOAD_URL.'/'.$cv['partner_av']:'https://ui-avatars.com/api/?name='.urlencode($cv['partner_name']).'&background=5D7B6F&color=fff&size=60';
  $params=$role==='student'?['company_id'=>$cv['partner_cid'],'app_id'=>$cv['application_id']]:['student_id'=>$cv['partner_sid'],'app_id'=>$cv['application_id']];
  $url='chat.php?'.http_build_query(array_filter($params));
?>
<div class="col-md-6 col-lg-4" style="animation:fadeUp .32s <?=$i*.04?>s ease both">
  <a href="<?=$url?>" style="text-decoration:none">
    <div class="card" style="border:1.5px solid <?=$cv['unread']>0?'var(--ds)':'rgba(164,195,162,.22)'?>;transition:all .2s"
         onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform=''">
      <div class="card-body d-flex align-items-center gap-3">
        <img src="<?=$av?>" style="width:46px;height:46px;border-radius:50%;object-fit:cover;flex-shrink:0">
        <div style="flex:1;min-width:0">
          <div class="d-flex justify-content-between align-items-center">
            <span class="fw7" style="font-size:.88rem"><?=htmlspecialchars($cv['partner_name'])?></span>
            <?php if($cv['unread']>0): ?><span class="badge bg-primary"><?=$cv['unread']?></span><?php endif; ?>
          </div>
          <?php if($cv['job_title']): ?><div class="small text-muted" style="font-size:.7rem"><?=htmlspecialchars($cv['job_title'])?></div><?php endif; ?>
          <div class="small text-muted mt-1" style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;font-size:.78rem">
            <?=htmlspecialchars(mb_substr($cv['last_msg']??'Chưa có tin nhắn',0,50))?>
          </div>
          <?php if($cv['last_at']): ?><div style="font-size:.67rem;color:var(--tl);margin-top:2px"><?=date('d/m H:i',strtotime($cv['last_at']))?></div><?php endif; ?>
        </div>
      </div>
    </div>
  </a>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>
<?php include BASE_PATH_FS . 'includes/footer.php'; ?>
