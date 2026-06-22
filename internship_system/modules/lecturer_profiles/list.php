<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';
requireRole('admin');

$lecturers=$conn->query("SELECT lp.*,u.email,u.created_at,
  (SELECT COUNT(*) FROM internship_registrations WHERE lecturer_id=lp.lecturer_id AND status='active') AS active_students,
  (SELECT COUNT(*) FROM internship_registrations WHERE lecturer_id=lp.lecturer_id) AS total_students
  FROM lecturer_profiles lp JOIN users u ON lp.user_id=u.user_id
  ORDER BY lp.full_name")->fetch_all(MYSQLI_ASSOC);
?>
<?php include '../../includes/header.php'; ?>
<div class="ph fu">
  <div><h4><i class="bi bi-person-workspace me-2"></i>Giảng viên</h4><div class="ph-sub">Tổng: <?=count($lecturers)?></div></div>
  <a href="<?=BASE_PATH?>/modules/users/create_lecturer.php" class="btn btn-primary"><i class="bi bi-person-plus me-1"></i>Thêm giảng viên</a>
</div>
<?php showFlash(); ?>

<?php if(empty($lecturers)): ?>
<div class="card text-center py-5 fu1"><div class="card-body">
  <i class="bi bi-person-workspace" style="font-size:3rem;color:var(--tl)"></i>
  <h5 class="mt-3 fw7">Chưa có giảng viên</h5>
  <p class="text-muted">Admin tạo tài khoản giảng viên để phân công hướng dẫn sinh viên.</p>
  <a href="<?=BASE_PATH?>/modules/users/create_lecturer.php" class="btn btn-primary">Thêm giảng viên</a>
</div></div>
<?php else: ?>
<div class="row g-3 fu1">
<?php foreach($lecturers as $i=>$l): ?>
<div class="col-md-6 col-lg-4" style="animation:fadeUp .32s <?=$i*.04?>s ease both">
  <div class="card h-100" style="border:1.5px solid rgba(164,195,162,.2)">
    <div class="card-body">
      <div class="d-flex align-items-center gap-3 mb-3">
        <div class="av" style="width:46px;height:46px;font-size:1rem;background:linear-gradient(135deg,#4ab8c4,#2a95a2)">
          <?=strtoupper(mb_substr($l['full_name'],0,1))?>
        </div>
        <div>
          <div class="fw7"><?=htmlspecialchars($l['full_name'])?></div>
          <div class="small text-muted"><?=htmlspecialchars($l['department']??'—')?></div>
        </div>
      </div>
      <div class="small mb-1"><i class="bi bi-envelope me-2 text-muted"></i><?=htmlspecialchars($l['email']??$l['email'])?></div>
      <?php if($l['phone']): ?><div class="small mb-1"><i class="bi bi-phone me-2 text-muted"></i><?=htmlspecialchars($l['phone'])?></div><?php endif; ?>
      <div class="d-flex gap-2 mt-2">
        <span class="badge" style="background:rgba(74,158,106,.12);color:#2d6a40"><?=$l['active_students']?> đang TT</span>
        <span class="badge bg-secondary"><?=$l['total_students']?> tổng cộng</span>
      </div>
    </div>
  </div>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>
<?php include '../../includes/footer.php'; ?>
