<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';
requireRole(['student','company']);

$uid  = $_SESSION['user_id'];
$role = getRole();
$app_id = (int)($_GET['app_id']??0);

// Get conversation context
if($role==='student'){
    $sp=$conn->prepare("SELECT student_id FROM student_profiles WHERE user_id=?");
    $sp->bind_param('i',$uid); $sp->execute();
    $my_profile_id=$sp->get_result()->fetch_assoc()['student_id']??0;
    $partner_cid=(int)($_GET['company_id']??0);
    // Get student_id for conversation
    $conv_student=$my_profile_id;
    $conv_company=$partner_cid;
    // Get partner display
    $pq=$conn->prepare("SELECT company_name AS name,logo AS avatar FROM company_profiles WHERE company_id=?");
    $pq->bind_param('i',$partner_cid); $pq->execute();
    $partner=$pq->get_result()->fetch_assoc();
} else {
    $cp=$conn->prepare("SELECT company_id FROM company_profiles WHERE user_id=?");
    $cp->bind_param('i',$uid); $cp->execute();
    $my_profile_id=$cp->get_result()->fetch_assoc()['company_id']??0;
    $partner_sid=(int)($_GET['student_id']??0);
    $conv_student=$partner_sid;
    $conv_company=$my_profile_id;
    $pq=$conn->prepare("SELECT full_name AS name,avatar FROM student_profiles WHERE student_id=?");
    $pq->bind_param('i',$partner_sid); $pq->execute();
    $partner=$pq->get_result()->fetch_assoc();
}

if(!$partner){ setFlash('error','Không tìm thấy đối thoại.'); redirect('inbox.php'); }

$conv_id=getOrCreateConversation($conn,$conv_student,$conv_company,$app_id?:null);

// Send message
if($_SERVER['REQUEST_METHOD']==='POST'){
    $content=trim($_POST['content']??'');
    if(!empty($content)){
        $ins=$conn->prepare("INSERT INTO messages (conversation_id,sender_id,message_content) VALUES (?,?,?)");
        $ins->bind_param('iis',$conv_id,$uid,$content); $ins->execute();
    }
    redirect("chat.php?".http_build_query($_GET));
}

// Mark read
$conn->prepare("UPDATE messages SET is_read=1 WHERE conversation_id=? AND sender_id!=?")->bind_param('ii',$conv_id,$uid);
$mr=$conn->prepare("UPDATE messages SET is_read=1 WHERE conversation_id=? AND sender_id!=?");
$mr->bind_param('ii',$conv_id,$uid); $mr->execute();

// Fetch messages
$msgs=$conn->prepare("SELECT m.*,u.email FROM messages m JOIN users u ON m.sender_id=u.user_id WHERE m.conversation_id=? ORDER BY m.sent_at ASC");
$msgs->bind_param('i',$conv_id); $msgs->execute();
$thread=$msgs->get_result()->fetch_all(MYSQLI_ASSOC);

$partner_av=isset($partner['logo'])?($partner['logo']?UPLOAD_URL.'/'.$partner['logo']:null):($partner['avatar']?UPLOAD_URL.'/'.$partner['avatar']:null);
$partner_name=$partner['name']??'—';
if(!$partner_av) $partner_av='https://ui-avatars.com/api/?name='.urlencode($partner_name).'&background=5D7B6F&color=fff&size=60';
$my_av='https://ui-avatars.com/api/?name='.urlencode($_SESSION['full_name']??'U').'&background=A4C3A2&color=2A3F38&size=60';

// Also get interview info if any
$interview=null;
if($app_id){
    $iq=$conn->prepare("SELECT * FROM interviews WHERE application_id=?");
    $iq->bind_param('i',$app_id); $iq->execute();
    $interview=$iq->get_result()->fetch_assoc();
}
?>
<?php include '../../includes/header.php'; ?>
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

<!-- Company: set interview -->
<?php if($role==='company'&&$app_id): ?>
<div class="card mb-3 fu1"><div class="card-body">
  <h6 class="fw7 mb-3" style="color:var(--ds)"><i class="bi bi-calendar-plus me-2"></i>Hẹn lịch phỏng vấn</h6>
  <form method="POST" action="set_interview.php">
    <input type="hidden" name="app_id" value="<?=$app_id?>">
    <input type="hidden" name="redirect" value="<?=htmlspecialchars(http_build_query($_GET))?>">
    <div class="row g-2">
      <div class="col-md-4"><label class="form-label">Ngày giờ</label><input type="datetime-local" name="interview_date" class="form-control" value="<?=$interview['interview_date']??''?>"></div>
      <div class="col-md-4"><label class="form-label">Địa điểm</label><input type="text" name="address" class="form-control" placeholder="Địa chỉ..." value="<?=htmlspecialchars($interview['address']??'')?>"></div>
      <div class="col-md-4"><label class="form-label">Link Meet/Zoom</label><input type="url" name="meeting_link" class="form-control" placeholder="https://..." value="<?=htmlspecialchars($interview['meeting_link']??'')?>"></div>
    </div>
    <div class="mt-2 d-flex gap-2">
      <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-calendar-check me-1"></i>Lưu lịch PV</button>
      <?php if($interview): ?>
      <a href="set_interview_result.php?app_id=<?=$app_id?>&result=passed<?='&'.http_build_query($_GET)?>" class="btn btn-success btn-sm" onclick="return confirm('Xác nhận sinh viên đậu phỏng vấn?')"><i class="bi bi-check me-1"></i>Đậu PV</a>
      <a href="set_interview_result.php?app_id=<?=$app_id?>&result=failed<?='&'.http_build_query($_GET)?>" class="btn btn-danger btn-sm" onclick="return confirm('Xác nhận sinh viên rớt phỏng vấn?')"><i class="bi bi-x me-1"></i>Rớt PV</a>
      <?php endif; ?>
    </div>
  </form>
</div></div>
<?php endif; ?>

<!-- Messages -->
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

<!-- Input -->
<div class="card fu3"><div class="card-body">
  <form method="POST" class="d-flex gap-2" id="mf">
    <textarea name="content" id="mc2" class="form-control" rows="2" placeholder="Nhập tin nhắn... (Enter gửi, Shift+Enter xuống dòng)" required style="resize:none"
      onkeydown="if(event.key==='Enter'&&!event.shiftKey){event.preventDefault();document.getElementById('sb2').click()}"></textarea>
    <button type="submit" id="sb2" class="btn btn-primary" style="align-self:flex-end;white-space:nowrap">
      <i class="bi bi-send-fill"></i>
    </button>
  </form>
</div></div>

<script>
const mc=document.getElementById('mc'); if(mc) mc.scrollTop=mc.scrollHeight;
</script>
<?php include '../../includes/footer.php'; ?>
