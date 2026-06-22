<?php // View: applications/company_review — nhận $rows từ controller ?>
<?php include BASE_PATH_FS . 'includes/header.php'; ?>
<div class="ph fu">
  <div><h4><i class="bi bi-people-fill me-2"></i>Hồ sơ ứng viên</h4><div class="ph-sub"><?=count($rows)?> ứng viên — Trường đã duyệt, chờ bạn quyết định</div></div>
</div>
<?php showFlash(); ?>

<?php if(empty($rows)): ?>
<div class="card text-center py-5 fu1"><div class="card-body">
  <i class="bi bi-people" style="font-size:3rem;color:var(--tl)"></i>
  <h5 class="mt-3 fw7">Chưa có ứng viên</h5>
  <p class="text-muted">Khi trường duyệt đơn, ứng viên sẽ xuất hiện ở đây.</p>
</div></div>
<?php else: ?>
<div class="row g-3 fu1">
<?php foreach($rows as $i=>$a):
  $av=$a['s_avatar']?UPLOAD_URL.'/'.$a['s_avatar']:'https://ui-avatars.com/api/?name='.urlencode($a['full_name']).'&background=5D7B6F&color=fff&size=80';
?>
<div class="col-md-6" style="animation:fadeUp .32s <?=$i*.04?>s ease both">
  <div class="card h-100" style="border:1.5px solid rgba(164,195,162,.25)">
    <div class="card-body">
      <div class="d-flex align-items-center gap-3 mb-3">
        <img src="<?=$av?>" style="width:56px;height:56px;border-radius:50%;object-fit:cover;flex-shrink:0">
        <div>
          <h6 class="fw8 mb-0"><?=htmlspecialchars($a['full_name'])?></h6>
          <div class="small text-muted"><?=htmlspecialchars($a['email'])?></div>
          <div class="d-flex gap-1 mt-1">
            <span class="badge bg-primary" style="font-size:.7rem">GPA: <?=$a['gpa']?></span>
            <span class="badge bg-secondary" style="font-size:.7rem"><?=htmlspecialchars($a['student_code']??'')?></span>
          </div>
        </div>
      </div>
      <div class="small mb-1"><i class="bi bi-briefcase me-2 text-muted"></i><strong>Vị trí:</strong> <?=htmlspecialchars($a['title'])?></div>
      <?php if($a['major']): ?><div class="small mb-2"><i class="bi bi-mortarboard me-2 text-muted"></i><?=htmlspecialchars($a['major'])?></div><?php endif; ?>
      <?php if($a['cv_file']): ?>
      <a href="<?=UPLOAD_URL.'/'.$a['cv_file']?>" target="_blank" class="btn btn-secondary btn-sm mb-2 w-100">
        <i class="bi bi-file-earmark-pdf me-1"></i>Xem CV
      </a>
      <?php endif; ?>
      <div class="d-flex gap-2 mt-2">
        <a href="?approve=<?=$a['application_id']?>" class="btn btn-success flex-fill" onclick="return confirm('Chấp nhận ứng viên này?')">
          <i class="bi bi-person-check me-1"></i>Chấp nhận
        </a>
        <a href="?reject=<?=$a['application_id']?>" class="btn btn-danger flex-fill" onclick="return confirm('Từ chối ứng viên?')">
          <i class="bi bi-x-lg me-1"></i>Từ chối
        </a>
      </div>
      <div class="text-center mt-2">
        <a href="<?=BASE_PATH?>/messages/chat.php?student_id=<?=$a['student_id']?>&app_id=<?=$a['application_id']?>" class="btn btn-primary w-100 btn-sm">
          <i class="bi bi-chat-dots-fill me-1"></i>Nhắn tin hẹn phỏng vấn
        </a>
      </div>
    </div>
  </div>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>
<?php include BASE_PATH_FS . 'includes/footer.php'; ?>
