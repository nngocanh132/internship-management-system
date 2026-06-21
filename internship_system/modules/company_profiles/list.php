<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';
requireRole('admin');

$companies = safeQuery($conn, "SELECT cp.*,u.email,u.is_profile_completed,u.created_at,
  (SELECT COUNT(*) FROM internships WHERE company_id=cp.company_id) AS job_count,
  (SELECT COUNT(*) FROM applications a JOIN internships i ON a.internship_id=i.internship_id WHERE i.company_id=cp.company_id) AS app_count
  FROM company_profiles cp JOIN users u ON cp.user_id=u.user_id
  ORDER BY cp.company_name");
?>
<?php include '../../includes/header.php'; ?>
<div class="ph fu">
  <div><h4><i class="bi bi-building-fill me-2"></i>Doanh nghiệp</h4><div class="ph-sub">Tổng: <?=count($companies)?></div></div>
</div>
<?php showFlash(); ?>
<?php if(empty($companies)): ?>
<div class="card text-center py-5 fu1"><div class="card-body">
  <i class="bi bi-building" style="font-size:3rem;color:var(--tl)"></i>
  <h5 class="mt-3 fw7">Chưa có doanh nghiệp nào đăng ký</h5>
</div></div>
<?php else: ?>
<div class="row g-3 fu1">
<?php foreach($companies as $i=>$c):
  $logo = $c['logo'] ? UPLOAD_URL.'/'.$c['logo'] : 'https://ui-avatars.com/api/?name='.urlencode($c['company_name']).'&background=5D7B6F&color=fff&size=80&bold=true';
?>
<div class="col-md-6 col-lg-4" style="animation:fadeUp .32s <?=$i*.04?>s ease both">
  <div class="card h-100" style="border:1.5px solid rgba(164,195,162,.2)">
    <div class="card-body">
      <div class="d-flex align-items-center gap-3 mb-3">
        <img src="<?=$logo?>" style="width:48px;height:48px;border-radius:12px;object-fit:cover;flex-shrink:0">
        <div>
          <div class="fw7"><?=htmlspecialchars($c['company_name'])?></div>
          <div class="small text-muted"><?=htmlspecialchars($c['industry']??'—')?></div>
        </div>
      </div>
      <?php if($c['address']): ?><div class="small mb-1"><i class="bi bi-geo-alt me-2 text-muted"></i><?=htmlspecialchars($c['address'])?></div><?php endif; ?>
      <?php if($c['website']): ?><div class="small mb-1"><i class="bi bi-globe me-2 text-muted"></i><a href="<?=htmlspecialchars($c['website'])?>" target="_blank"><?=htmlspecialchars($c['website'])?></a></div><?php endif; ?>
      <div class="small mb-1"><i class="bi bi-envelope me-2 text-muted"></i><?=htmlspecialchars($c['email'])?></div>
      <div class="d-flex gap-2 mt-2 flex-wrap">
        <span class="badge bg-primary"><?=$c['job_count']?> vị trí</span>
        <span class="badge bg-secondary"><?=$c['app_count']?> đơn</span>
        <span class="badge ms-auto" style="<?=$c['is_profile_completed']?'background:rgba(74,158,106,.12);color:#2d6a40':'background:rgba(196,154,108,.12);color:#a07040'?>"><?=$c['is_profile_completed']?'✅ Hồ sơ đầy đủ':'⚠️ Chưa hoàn thiện'?></span>
      </div>
      <?php if($c['business_license_file']): ?>
      <div class="mt-2"><a href="<?=UPLOAD_URL.'/'.$c['business_license_file']?>" target="_blank" class="btn btn-secondary btn-sm w-100"><i class="bi bi-file-earmark-pdf me-1"></i>Xem Giấy phép KD</a></div>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>
<?php include '../../includes/footer.php'; ?>
