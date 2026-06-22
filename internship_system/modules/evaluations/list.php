<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';
requireRole(['admin','company','student']);

$uid  = $_SESSION['user_id'];
$role = getRole();

$where = "WHERE 1=1";
if ($role === 'student') {
    $sq = $conn->prepare("SELECT student_id FROM student_profiles WHERE user_id=?");
    if ($sq) { $sq->bind_param('i',$uid); $sq->execute(); $sid = $sq->get_result()->fetch_assoc()['student_id'] ?? 0; }
    else $sid = 0;
    $where = "WHERE ir.student_id=$sid";
} elseif ($role === 'company') {
    $cq = $conn->prepare("SELECT company_id FROM company_profiles WHERE user_id=?");
    if ($cq) { $cq->bind_param('i',$uid); $cq->execute(); $cid = $cq->get_result()->fetch_assoc()['company_id'] ?? 0; }
    else $cid = 0;
    $where = "WHERE ir.company_id=$cid";
}

$evals = safeQuery($conn,"SELECT e.*,sp.full_name,sp.student_code,sp.avatar AS s_av,cp.company_name,i.title
  FROM evaluations e
  JOIN internship_registrations ir ON e.registration_id=ir.registration_id
  JOIN student_profiles sp ON ir.student_id=sp.student_id
  JOIN company_profiles cp ON ir.company_id=cp.company_id
  JOIN internships i ON ir.internship_id=i.internship_id
  $where ORDER BY e.evaluated_at DESC");
?>
<?php include '../../includes/header.php'; ?>
<div class="ph fu">
  <div><h4><i class="bi bi-star-fill me-2"></i>Đánh giá Thực tập</h4><div class="ph-sub">Tổng: <?=count($evals)?></div></div>
  <?php if($role==='company'): ?><a href="add.php" class="btn btn-primary"><i class="bi bi-plus-circle me-1"></i>Thêm đánh giá</a><?php endif; ?>
</div>
<?php showFlash(); ?>

<?php if(empty($evals)): ?>
<div class="card text-center py-5 fu1"><div class="card-body">
  <i class="bi bi-star" style="font-size:3rem;color:var(--tl)"></i>
  <h5 class="mt-3 fw7">Chưa có đánh giá</h5>
  <?php if($role==='company'): ?><p class="text-muted">Đánh giá sinh viên sau khi kỳ thực tập kết thúc.</p><a href="add.php" class="btn btn-primary">Thêm đánh giá</a><?php endif; ?>
</div></div>
<?php else: ?>
<div class="row g-3 fu1">
<?php foreach($evals as $i=>$e):
  $score  = $e['overall_score'] ?? 0;
  $scolor = $score>=8?'#2d6a40':($score>=6?'var(--ds)':($score>=4?'#a07040':'#9a3030'));
  $av     = $e['s_av'] ? UPLOAD_URL.'/'.$e['s_av'] : 'https://ui-avatars.com/api/?name='.urlencode($e['full_name']).'&background=5D7B6F&color=fff&size=60';
?>
<div class="col-md-6" style="animation:fadeUp .32s <?=$i*.04?>s ease both">
  <div class="card h-100" style="border:1.5px solid rgba(164,195,162,.2)">
    <div class="card-body">
      <div class="d-flex align-items-center gap-3 mb-3">
        <img src="<?=$av?>" style="width:46px;height:46px;border-radius:50%;object-fit:cover;flex-shrink:0">
        <div style="flex:1">
          <div class="fw7"><?=htmlspecialchars($e['full_name'])?></div>
          <div class="small text-muted"><?=htmlspecialchars($e['title'])?> @ <?=htmlspecialchars($e['company_name'])?></div>
        </div>
        <div class="text-end">
          <div style="font-size:2.2rem;font-weight:800;color:<?=$scolor?>;line-height:1"><?=number_format($score,1)?></div>
          <div class="small text-muted">/10</div>
        </div>
      </div>
      <div class="row g-2 mb-3">
        <?php foreach(['technical_skill'=>'🔧 Kỹ thuật','teamwork'=>'👥 Nhóm','communication'=>'💬 Giao tiếp','attitude'=>'😊 Thái độ'] as $k=>$l): ?>
        <div class="col-6">
          <div class="small text-muted mb-1"><?=$l?></div>
          <div class="d-flex align-items-center gap-2">
            <div style="flex:1;height:6px;background:rgba(164,195,162,.2);border-radius:3px;overflow:hidden">
              <div style="height:6px;background:linear-gradient(90deg,var(--ds),var(--sg));border-radius:3px;width:<?=($e[$k]??0)*10?>%"></div>
            </div>
            <span class="fw7 small"><?=$e[$k]??0?></span>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php if($e['comment']): ?>
      <div style="background:rgba(164,195,162,.07);border-radius:8px;padding:9px 12px;font-size:.82rem;color:var(--tm)">
        <?=nl2br(htmlspecialchars($e['comment']))?>
      </div>
      <?php endif; ?>
      <div class="small text-muted mt-2"><?=date('d/m/Y',strtotime($e['evaluated_at']))?></div>
    </div>
  </div>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>
<?php include '../../includes/footer.php'; ?>
