<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';
requireRole('student');

$uid = $_SESSION['user_id'];

// Hủy đơn
if (isset($_GET['cancel'])) {
    $id = (int)$_GET['cancel'];
    $chk = $conn->prepare("SELECT registration_id,status FROM internship_registrations WHERE registration_id=? AND student_id=?");
    $chk->bind_param('ii',$id,$uid); $chk->execute();
    $r = $chk->get_result()->fetch_assoc();
    if ($r && $r['status'] === 'pending') {
        $u = $conn->prepare("UPDATE internship_registrations SET status='cancelled' WHERE registration_id=?");
        $u->bind_param('i',$id); $u->execute();
        setFlash('success','Đã hủy đơn ứng tuyển.');
    } else {
        setFlash('error','Chỉ có thể hủy đơn đang chờ duyệt.');
    }
    redirect('my.php');
}

$stmt = $conn->prepare("SELECT r.*,
    p.title, p.description, p.work_type, p.salary_range, p.deadline,
    c.name AS cname, c.location AS cloc, c.logo AS clogo
    FROM internship_registrations r
    JOIN internship_positions p ON r.position_id=p.position_id
    JOIN companies c ON p.company_id=c.company_id
    WHERE r.student_id=?
    ORDER BY r.registered_at DESC");
$stmt->bind_param('i',$uid); $stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Kiểm tra có assignment không
$has_assignment = false;
$asgn = $conn->prepare("SELECT a.assignment_id FROM internship_assignments a JOIN internship_registrations r ON a.registration_id=r.registration_id WHERE r.student_id=? LIMIT 1");
$asgn->bind_param('i',$uid); $asgn->execute();
if ($asgn->get_result()->fetch_assoc()) $has_assignment = true;
?>
<?php include '../../includes/header.php'; ?>
<div class="page-header fade-up">
  <div>
    <h4><i class="bi bi-clipboard-check-fill me-2"></i>Đơn ứng tuyển của tôi</h4>
    <div class="page-subtitle">Tổng: <?=count($rows)?> đơn</div>
  </div>
  <a href="../positions/list.php" class="btn btn-primary"><i class="bi bi-search me-1"></i>Tìm thêm vị trí</a>
</div>
<?php showFlash(); ?>

<!-- Status legend -->
<div class="card mb-3 fade-up-1" style="background:rgba(234,231,214,.4)">
  <div class="card-body py-3">
    <div class="d-flex flex-wrap gap-3 align-items-center">
      <span class="small fw-700" style="color:var(--text-mid)">Quy trình duyệt:</span>
      <?php
      $steps=[['⏳','Bạn nộp đơn','pending'],['🏫','Trường duyệt','school'],['🏢','Công ty duyệt','company'],['✅','Bắt đầu TT','approved']];
      foreach($steps as [$icon,$lbl,$key]):
      ?>
      <div style="display:flex;align-items:center;gap:5px;font-size:.8rem">
        <span><?=$icon?></span><span style="color:var(--text-mid)"><?=$lbl?></span>
      </div>
      <?php if($key!=='approved'): ?><i class="bi bi-arrow-right" style="color:var(--text-light);font-size:.75rem"></i><?php endif; ?>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<?php if(empty($rows)): ?>
<div class="card fade-up-2 text-center py-5">
  <div class="card-body">
    <i class="bi bi-clipboard-x" style="font-size:3rem;color:var(--text-light)"></i>
    <h5 class="mt-3 fw-700">Chưa có đơn ứng tuyển nào</h5>
    <p class="text-muted">Hãy tìm vị trí thực tập phù hợp và ứng tuyển ngay!</p>
    <a href="../positions/list.php" class="btn btn-primary"><i class="bi bi-search me-1"></i>Tìm vị trí thực tập</a>
  </div>
</div>
<?php else: ?>
<div class="row g-3">
<?php foreach($rows as $i=>$r):
  // Xác định trạng thái hiện tại trong flow
  $school_ok  = ($r['school_status']??'pending') === 'approved';
  $company_ok = ($r['company_status']??'pending') === 'approved';
  $rejected   = $r['status'] === 'rejected' || ($r['school_status']??'') === 'rejected' || ($r['company_status']??'') === 'rejected';
  $cancelled  = $r['status'] === 'cancelled';

  if ($cancelled)      { $badge_color='#5a5a5a'; $badge_bg='rgba(160,160,160,.12)'; $badge='Đã hủy'; $icon='dash-circle'; }
  elseif ($rejected)   { $badge_color='#9a3030'; $badge_bg='rgba(192,96,80,.12)';   $badge='Từ chối'; $icon='x-circle-fill'; }
  elseif ($company_ok) { $badge_color='#2d6a40'; $badge_bg='rgba(74,158,106,.14)';  $badge='✅ Đã nhận'; $icon='check-circle-fill'; }
  elseif ($school_ok)  { $badge_color='#3a8a96'; $badge_bg='rgba(74,138,150,.12)';  $badge='🏢 Chờ công ty'; $icon='building'; }
  else                 { $badge_color='#a07040'; $badge_bg='rgba(196,154,108,.12)';  $badge='🏫 Chờ trường'; $icon='hourglass-split'; }

  $logoUrl = $r['clogo'] ? UPLOAD_URL.'/'.$r['clogo'] : 'https://ui-avatars.com/api/?name='.urlencode($r['cname']).'&background=A4C3A2&color=2A3F38&size=60&bold=true';
?>
<div class="col-md-6" style="animation:fadeInUp .35s <?=$i*.05?>s ease both">
  <div class="card" style="border-left:4px solid <?=$badge_color?>;border-radius:14px">
    <div class="card-body">
      <div class="d-flex justify-content-between align-items-start mb-2">
        <div class="d-flex align-items-center gap-3">
          <img src="<?=$logoUrl?>" style="width:42px;height:42px;border-radius:10px;object-fit:cover">
          <div>
            <h6 class="fw-800 mb-0" style="color:var(--text-dark)"><?=htmlspecialchars($r['title'])?></h6>
            <div class="small text-muted"><?=htmlspecialchars($r['cname'])?><?=$r['cloc']?' · '.htmlspecialchars($r['cloc']):''?></div>
          </div>
        </div>
        <span class="badge" style="background:<?=$badge_bg?>;color:<?=$badge_color?>;padding:6px 12px;font-size:.78rem">
          <?=$badge?>
        </span>
      </div>

      <!-- Progress steps -->
      <div class="d-flex align-items-center gap-1 mb-3 mt-2">
        <?php
        $step_done  = ['Nộp đơn' => true,
                       'Trường duyệt' => $school_ok || $rejected,
                       'Công ty duyệt' => $company_ok || ($rejected && $school_ok),
                       'Bắt đầu TT' => $company_ok];
        $step_active= ['Nộp đơn' => !$school_ok && !$rejected,
                       'Trường duyệt' => !$school_ok && !$rejected && !$cancelled,
                       'Công ty duyệt' => $school_ok && !$company_ok && !$rejected,
                       'Bắt đầu TT' => $company_ok];
        $step_ok_color=['Trường duyệt'=>$school_ok && !$rejected,'Công ty duyệt'=>$company_ok,'Bắt đầu TT'=>$company_ok,'Nộp đơn'=>true];
        foreach(['Nộp đơn','Trường duyệt','Công ty duyệt','Bắt đầu TT'] as $idx=>$step):
          $done_c   = $step_done[$step] && !$rejected && !$cancelled;
          $active_c = !$done_c && ($step_active[$step] ?? false) && !$rejected && !$cancelled;
          $rej_c    = $rejected && in_array($step,['Trường duyệt','Công ty duyệt']) && !($done_c);
          $bg = $rej_c ? '#9a3030' : ($done_c ? '#2d6a40' : ($active_c ? '#a07040' : 'rgba(164,195,162,.3)'));
          $tc = $rej_c || $done_c || $active_c ? '#fff' : 'var(--text-light)';
        ?>
        <div style="flex:1;text-align:center">
          <div style="width:28px;height:28px;border-radius:50%;background:<?=$bg?>;color:<?=$tc?>;font-size:.65rem;display:flex;align-items:center;justify-content:center;margin:0 auto 3px;font-weight:700">
            <?=$rej_c?'✗':($done_c?'✓':($idx+1))?>
          </div>
          <div style="font-size:.62rem;color:var(--text-light)"><?=$step?></div>
        </div>
        <?php if($idx<3): ?><div style="flex:1;height:2px;background:<?=$done_c?'var(--deep-sage)':'rgba(164,195,162,.3)'?>;margin-top:-14px"></div><?php endif; ?>
        <?php endforeach; ?>
      </div>

      <div class="d-flex flex-wrap gap-2 mb-2">
        <?php if($r['work_type']): ?><span class="badge" style="background:rgba(93,123,111,.1);color:var(--deep-sage)"><?=htmlspecialchars($r['work_type'])?></span><?php endif; ?>
        <?php if($r['salary_range']): ?><span class="badge" style="background:rgba(74,158,106,.1);color:#2d6a40"><?=htmlspecialchars($r['salary_range'])?></span><?php endif; ?>
      </div>

      <div class="small text-muted mb-2">
        <i class="bi bi-calendar3 me-1"></i>Nộp: <?=date('d/m/Y H:i',strtotime($r['registered_at']))?>
        <?php if($r['deadline']): ?> · HH: <?=date('d/m/Y',strtotime($r['deadline']))?><?php endif; ?>
      </div>

      <!-- Cover letter snippet -->
      <?php if($r['cover_letter']): ?>
      <div class="small text-muted mb-2" style="background:rgba(234,231,214,.4);padding:8px;border-radius:8px">
        <i class="bi bi-file-text me-1"></i><?=htmlspecialchars(mb_substr($r['cover_letter'],0,100))?>…
      </div>
      <?php endif; ?>

      <!-- Actions -->
      <div class="d-flex gap-2 flex-wrap mt-2">
        <?php if($r['cv_submitted']): ?>
        <a href="<?=UPLOAD_URL.'/'.$r['cv_submitted']?>" target="_blank" class="btn btn-outline-secondary btn-sm">
          <i class="bi bi-file-earmark-pdf me-1"></i>CV đã nộp
        </a>
        <?php endif; ?>
        <?php if($r['status']==='pending' && ($r['school_status']??'pending')==='pending'): ?>
        <a href="?cancel=<?=$r['registration_id']?>" class="btn btn-outline-secondary btn-sm" onclick="return confirm('Hủy đơn ứng tuyển này?')">
          <i class="bi bi-x-circle me-1"></i>Hủy đơn
        </a>
        <?php endif; ?>
        <?php if($company_ok): ?>
        <a href="../messages/inbox.php" class="btn btn-primary btn-sm">
          <i class="bi bi-chat-dots me-1"></i>Nhắn tin với công ty
        </a>
        <?php endif; ?>
        <?php if($company_ok && $has_assignment): ?>
        <a href="../journals/my.php" class="btn btn-success btn-sm">
          <i class="bi bi-journal-text me-1"></i>Nhật ký TT
        </a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>
<?php include '../../includes/footer.php'; ?>
