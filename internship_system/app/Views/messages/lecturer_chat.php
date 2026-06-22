<?php // View: messages/lecturer_chat — nhận $thread,$partner_name,$partner_av,$my_av,$role,$uid,$partner_uid,$partner,$back_url ?>
<?php include BASE_PATH_FS . 'includes/header.php'; ?>
<div class="ph fu">
  <div class="d-flex align-items-center gap-3">
    <img src="<?=$partner_av?>" style="width:44px;height:44px;border-radius:50%;object-fit:cover;flex-shrink:0;border:2px solid var(--sg)">
    <div>
      <h4 style="margin:0"><?=htmlspecialchars($partner_name)?></h4>
      <div class="ph-sub"><?=$role==='lecturer'?'Sinh viên thực tập':'Giảng viên hướng dẫn'?>
        <?php if(isset($partner['department'])&&$partner['department']): ?> · <?=htmlspecialchars($partner['department'])?><?php endif; ?>
        <?php if(isset($partner['phone'])&&$partner['phone']): ?> · <a href="tel:<?=htmlspecialchars($partner['phone'])?>" style="color:var(--ds)"><?=htmlspecialchars($partner['phone'])?></a><?php endif; ?>
      </div>
    </div>
  </div>
  <a href="<?=$back_url?>" class="btn btn-secondary"><i class="bi bi-arrow-left me-1"></i>Quay lại</a>
</div>
<?php showFlash(); ?>

<div class="card mb-3 fu1"><div class="card-body p-0">
  <div id="msgBox" style="min-height:200px;max-height:460px;overflow-y:auto;padding:18px">
    <?php if(empty($thread)): ?>
    <div class="text-center py-5 text-muted">
      <i class="bi bi-chat-square fs-2 d-block mb-2 opacity-25"></i>
      <?=$role==='student'?'Chưa có tin nhắn. Hãy hỏi GVHD những gì bạn cần hỗ trợ!':'Chưa có tin nhắn. Bạn có thể nhắn để hướng dẫn sinh viên.'?>
    </div>
    <?php else: foreach($thread as $m):
      $is_mine=($m['sender_id']==$uid); $av_url=$is_mine?$my_av:$partner_av;
    ?>
    <div class="d-flex <?=$is_mine?'flex-row-reverse':''?> gap-2 mb-3">
      <img src="<?=$av_url?>" style="width:30px;height:30px;border-radius:50%;object-fit:cover;flex-shrink:0">
      <div style="max-width:70%">
        <div style="background:<?=$is_mine?'var(--ds)':'rgba(164,195,162,.18)'?>;color:<?=$is_mine?'#fff':'var(--td)'?>;padding:10px 14px;border-radius:<?=$is_mine?'16px 4px 16px 16px':'4px 16px 16px 16px'?>;font-size:.875rem;line-height:1.6">
          <?=nl2br(htmlspecialchars($m['message_content']))?>
        </div>
        <div class="small text-muted mt-1 <?=$is_mine?'text-end':''?>" style="font-size:.68rem">
          <?=date('d/m/Y H:i',strtotime($m['sent_at']))?>
          <?php if($is_mine&&($m['is_read']??0)): ?> ✓<?php endif; ?>
        </div>
      </div>
    </div>
    <?php endforeach; endif; ?>
  </div>
</div></div>

<div class="card fu2"><div class="card-body">
  <form method="POST" id="msgForm">
    <div class="d-flex gap-2">
      <textarea name="content" id="msgInput" class="form-control" rows="2"
        placeholder="Nhập tin nhắn... (Enter để gửi, Shift+Enter xuống dòng)"
        required style="resize:none"
        onkeydown="if(event.key==='Enter'&&!event.shiftKey){event.preventDefault();document.getElementById('sendBtn').click()}"></textarea>
      <button type="submit" id="sendBtn" class="btn btn-primary px-3" style="align-self:flex-end">
        <i class="bi bi-send-fill"></i>
      </button>
    </div>
    <div class="small text-muted mt-1"><i class="bi bi-info-circle me-1"></i>Enter để gửi · Shift+Enter xuống dòng</div>
  </form>
</div></div>
<script>const box=document.getElementById('msgBox');if(box)box.scrollTop=box.scrollHeight;</script>
<?php include BASE_PATH_FS . 'includes/footer.php'; ?>
