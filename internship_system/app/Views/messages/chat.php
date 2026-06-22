<?php // View: messages/chat — nhận $thread, $partner_name, $partner_av, $my_av, $interview, $role, $app_id, $uid từ controller ?>
<?php include BASE_PATH_FS . 'includes/header.php'; ?>
<div class="ph fu">
  <div class="d-flex align-items-center gap-3">
    <img src="<?=$partner_av?>" style="width:40px;height:40px;border-radius:50%;object-fit:cover;flex-shrink:0">
    <div><h4 style="margin:0"><?=htmlspecialchars($partner_name)?></h4><div class="ph-sub"><?=$role==='student'?'Doanh nghiệp':'Sinh viên'?></div></div>
  </div>
  <a href="inbox.php" class="btn btn-secondary"><i class="bi bi-arrow-left me-1"></i>Hộp thư</a>
</div>

<?php if($interview): ?>
<div class="alert alert-info fu mb-3" style="border-radius:12px">
  <div class="fw7"><i class="bi bi-camera-video me-2"></i>Lịch phỏng vấn</div>
  <div class="small mt-1">📅 <?=date('d/m/Y H:i',strtotime($interview['interview_date']))?></div>
  <?php if($interview['address']): ?><div class="small">📍 <?=htmlspecialchars($interview['address'])?></div><?php endif; ?>
  <?php if($interview['meeting_link']): ?><a href="<?=htmlspecialchars($interview['meeting_link'])?>" target="_blank" class="btn btn-sm btn-primary mt-1"><i class="bi bi-camera-video me-1"></i>Tham gia</a><?php endif; ?>
  <span class="badge ms-2" style="<?=$interview['result']==='passed'?'background:rgba(74,158,106,.2);color:#1a4a2a':($interview['result']==='failed'?'background:rgba(192,96,80,.15);color:#9a3030':'background:rgba(196,154,108,.12);color:#a07040')?>">
    <?=match($interview['result']){'passed'=>'🎉 Đã đậu','failed'=>'😞 Không đậu',default=>'⏳ Chờ kết quả'}?>
  </span>
</div>
<?php endif; ?>

<?php if($role==='company'&&$app_id): ?>
<div class="card mb-3 fu1"><div class="card-body">
  <h6 class="fw7 mb-3" style="color:var(--ds)">
    <i class="bi bi-calendar-<?=$interview?'check':'plus'?> me-2"></i>
    <?=$interview?'Chỉnh sửa lịch phỏng vấn':'Hẹn lịch phỏng vấn'?>
  </h6>
  <?php if($interview): ?>
  <div class="alert alert-info py-2 mb-2" style="font-size:.82rem;">
    <i class="bi bi-info-circle me-1"></i>
    Lịch hiện tại: <strong><?=date('d/m/Y H:i',strtotime($interview['interview_date']))?></strong>
    <?=$interview['address']?' — '.htmlspecialchars($interview['address']):''?>
    — Bạn có thể cập nhật lại bên dưới.
  </div>
  <?php endif; ?>
  <form method="POST" action="set_interview.php">
    <input type="hidden" name="app_id" value="<?=$app_id?>">
    <input type="hidden" name="redirect" value="<?=htmlspecialchars(http_build_query($_GET))?>">
    <div class="row g-2">
      <div class="col-md-4">
        <label class="form-label">Ngày giờ <span class="text-danger">*</span></label>
        <input type="datetime-local" name="interview_date" class="form-control"
               value="<?=$interview['interview_date']??''?>" min="<?=date('Y-m-d\TH:i')?>" required>
        <div class="small text-muted mt-1">Không được chọn thời điểm trong quá khứ</div>
      </div>
      <div class="col-md-4"><label class="form-label">Địa điểm</label><input type="text" name="address" class="form-control" placeholder="Địa chỉ..." value="<?=htmlspecialchars($interview['address']??'')?>"></div>
      <div class="col-md-4"><label class="form-label">Link Meet/Zoom</label><input type="url" name="meeting_link" class="form-control" placeholder="https://..." value="<?=htmlspecialchars($interview['meeting_link']??'')?>"></div>
    </div>
    <div class="mt-2 d-flex gap-2">
      <button type="submit" class="btn btn-primary btn-sm">
        <i class="bi bi-calendar-check me-1"></i><?=$interview?'Cập nhật lịch PV':'Lưu lịch PV'?>
      </button>
      <?php if($interview): ?>
      <a href="set_interview_result.php?app_id=<?=$app_id?>&result=passed<?='&'.http_build_query($_GET)?>" class="btn btn-success btn-sm" onclick="return confirm('Xác nhận sinh viên đậu phỏng vấn?')"><i class="bi bi-check me-1"></i>Đậu PV</a>
      <a href="set_interview_result.php?app_id=<?=$app_id?>&result=failed<?='&'.http_build_query($_GET)?>" class="btn btn-danger btn-sm" onclick="return confirm('Xác nhận sinh viên rớt phỏng vấn?')"><i class="bi bi-x me-1"></i>Rớt PV</a>
      <?php endif; ?>
    </div>
  </form>
</div></div>
<?php endif; ?>

<div class="card fu2 mb-3"><div class="card-body p-0">
  <div id="mc" style="max-height:420px;overflow-y:auto;padding:18px">
    <?php if(empty($thread)): ?>
    <div class="text-center py-5 text-muted"><i class="bi bi-chat-square fs-2 d-block mb-2 opacity-25"></i>Bắt đầu cuộc trò chuyện</div>
    <?php else: foreach($thread as $m):
      $mine=($m['sender_id']==$uid);
      $mav=$mine?$my_av:$partner_av;
    ?>
    <div class="d-flex <?=$mine?'flex-row-reverse':''?> gap-2 mb-3">
      <img src="<?=$mav?>" style="width:30px;height:30px;border-radius:50%;object-fit:cover;flex-shrink:0">
      <div style="max-width:68%">
        <div style="background:<?=$mine?'var(--ds)':'rgba(164,195,162,.15)'?>;color:<?=$mine?'#fff':'var(--td)'?>;
          padding:9px 13px;border-radius:<?=$mine?'14px 4px 14px 14px':'4px 14px 14px 14px'?>;
          font-size:.85rem;line-height:1.5">
          <?=nl2br(htmlspecialchars($m['message_content']))?>
        </div>
        <div class="small text-muted mt-1 <?=$mine?'text-end':''?>" style="font-size:.67rem"><?=date('d/m H:i',strtotime($m['sent_at']))?></div>
      </div>
    </div>
    <?php endforeach; endif; ?>
  </div>
</div></div>

<div class="card fu3"><div class="card-body">
  <form method="POST" class="d-flex gap-2" id="mf">
    <textarea name="content" id="mc2" class="form-control" rows="2" placeholder="Nhập tin nhắn... (Enter gửi, Shift+Enter xuống dòng)" required style="resize:none"
      onkeydown="if(event.key==='Enter'&&!event.shiftKey){event.preventDefault();document.getElementById('sb2').click()}"></textarea>
    <button type="submit" id="sb2" class="btn btn-primary" style="align-self:flex-end;white-space:nowrap">
      <i class="bi bi-send-fill"></i>
    </button>
  </form>
</div></div>
<script>const mc=document.getElementById('mc'); if(mc) mc.scrollTop=mc.scrollHeight;</script>
<?php include BASE_PATH_FS . 'includes/footer.php'; ?>
