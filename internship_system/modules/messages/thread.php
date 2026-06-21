<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';
requireLogin();

$uid    = $_SESSION['user_id'];
$with   = (int)($_GET['with'] ?? 0);
if (!$with) { redirect('inbox.php'); }

// Get partner info
$pu = $conn->prepare("SELECT user_id,full_name,avatar,role FROM users WHERE user_id=?");
$pu->bind_param('i',$with); $pu->execute();
$partner = $pu->get_result()->fetch_assoc();
if (!$partner) { redirect('inbox.php'); }

// Send message
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $content = trim($_POST['content'] ?? '');
    $reg_id  = (int)($_POST['registration_id'] ?? 0) ?: null;
    if (!empty($content)) {
        $stmt = $conn->prepare("INSERT INTO messages (sender_id,receiver_id,registration_id,content) VALUES (?,?,?,?)");
        $stmt->bind_param('iiis',$uid,$with,$reg_id,$content);
        $stmt->execute();
    }
    redirect("thread.php?with=$with");
}

// Mark all as read
$conn->prepare("UPDATE messages SET is_read=1 WHERE sender_id=? AND receiver_id=?")
     ->bind_param('ii',$with,$uid);
$mr = $conn->prepare("UPDATE messages SET is_read=1 WHERE sender_id=? AND receiver_id=?");
$mr->bind_param('ii',$with,$uid); $mr->execute();

// Fetch messages
$msgs = $conn->prepare("SELECT * FROM messages WHERE (sender_id=? AND receiver_id=?) OR (sender_id=? AND receiver_id=?) ORDER BY sent_at ASC");
$msgs->bind_param('iiii',$uid,$with,$with,$uid); $msgs->execute();
$thread = $msgs->get_result()->fetch_all(MYSQLI_ASSOC);

// Get shared registrations for context
$regs = $conn->prepare("SELECT r.registration_id,p.title,c.name AS cname FROM internship_registrations r JOIN internship_positions p ON r.position_id=p.position_id JOIN companies c ON p.company_id=c.company_id WHERE r.student_id=? OR (EXISTS(SELECT 1 FROM companies co WHERE co.user_id=? AND p.company_id=co.company_id)) LIMIT 5");
$regs->bind_param('ii',$uid,$uid); $regs->execute();
$shared_regs = $regs->get_result()->fetch_all(MYSQLI_ASSOC);

$partnerAv = $partner['avatar'] ? UPLOAD_URL.'/'.$partner['avatar'] : 'https://ui-avatars.com/api/?name='.urlencode($partner['full_name']).'&background=5D7B6F&color=fff&size=60';
$myAv = !empty($_SESSION['avatar']) ? UPLOAD_URL.'/'.$_SESSION['avatar'] : 'https://ui-avatars.com/api/?name='.urlencode($_SESSION['full_name']).'&background=A4C3A2&color=2A3F38&size=60';
?>
<?php include '../../includes/header.php'; ?>
<div class="page-header fade-up">
  <div class="d-flex align-items-center gap-3">
    <img src="<?=$partnerAv?>" style="width:42px;height:42px;border-radius:50%;object-fit:cover">
    <div>
      <h4 style="margin:0"><?=htmlspecialchars($partner['full_name'])?></h4>
      <div class="page-subtitle"><?=getRoleLabel($partner['role'])?></div>
    </div>
  </div>
  <a href="inbox.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Hộp thư</a>
</div>

<!-- Message thread -->
<div class="card fade-up-1" style="margin-bottom:16px">
  <div class="card-body" id="msgContainer" style="max-height:480px;overflow-y:auto;padding:20px">
    <?php if(empty($thread)): ?>
    <div class="text-center text-muted py-4">
      <i class="bi bi-chat-square fs-1 d-block mb-2 opacity-25"></i>
      Chưa có tin nhắn nào. Hãy bắt đầu cuộc trò chuyện!
    </div>
    <?php else: foreach($thread as $m):
      $is_mine = ($m['sender_id'] == $uid);
      $av = $is_mine ? $myAv : $partnerAv;
    ?>
    <div class="d-flex <?=$is_mine?'flex-row-reverse':''?> gap-2 mb-3">
      <img src="<?=$av?>" style="width:32px;height:32px;border-radius:50%;object-fit:cover;flex-shrink:0">
      <div style="max-width:70%">
        <div style="background:<?=$is_mine?'var(--deep-sage)':'rgba(164,195,162,.15)'?>;
          color:<?=$is_mine?'#fff':'var(--text-dark)'?>;
          padding:10px 14px;border-radius:<?=$is_mine?'16px 4px 16px 16px':'4px 16px 16px 16px'?>;
          font-size:.875rem;line-height:1.5">
          <?=nl2br(htmlspecialchars($m['content']))?>
        </div>
        <div class="small text-muted mt-1 <?=$is_mine?'text-end':''?>" style="font-size:.7rem">
          <?=date('d/m/Y H:i',strtotime($m['sent_at']))?>
        </div>
      </div>
    </div>
    <?php endforeach; endif; ?>
  </div>
</div>

<!-- Compose -->
<div class="card fade-up-2">
  <div class="card-body">
    <form method="POST" id="msgForm">
      <input type="hidden" name="registration_id" value="<?=(int)($_GET['reg']??0)?>">
      <?php if(!empty($shared_regs)): ?>
      <div class="mb-2">
        <select name="registration_id" class="form-select form-select-sm" style="max-width:400px">
          <option value="">— Liên quan đến vị trí (tùy chọn) —</option>
          <?php foreach($shared_regs as $sr): ?>
          <option value="<?=$sr['registration_id']?>" <?=($_GET['reg']??'')==$sr['registration_id']?'selected':''?>>
            <?=htmlspecialchars($sr['title'])?> @ <?=htmlspecialchars($sr['cname'])?>
          </option>
          <?php endforeach; ?>
        </select>
      </div>
      <?php endif; ?>
      <div class="d-flex gap-2">
        <textarea name="content" class="form-control" rows="2" placeholder="Nhập tin nhắn..." required
                  style="resize:none" onkeydown="if(event.key==='Enter'&&!event.shiftKey){event.preventDefault();document.getElementById('sendBtn').click()}"></textarea>
        <button type="submit" id="sendBtn" class="btn btn-primary" style="white-space:nowrap;align-self:flex-end">
          <i class="bi bi-send-fill me-1"></i>Gửi
        </button>
      </div>
      <div class="small text-muted mt-1">Enter để gửi, Shift+Enter để xuống dòng</div>
    </form>
  </div>
</div>

<script>
// Scroll to bottom
const c = document.getElementById('msgContainer');
if(c) c.scrollTop = c.scrollHeight;
</script>
<?php include '../../includes/footer.php'; ?>
