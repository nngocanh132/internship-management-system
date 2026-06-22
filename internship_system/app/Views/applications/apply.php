<?php // View: applications/apply — nhận $job, $student, $existing, $errors, $iid, $logoUrl từ controller ?>
<?php include BASE_PATH_FS . 'includes/header.php'; ?>
<div class="ph fu">
  <div><h4><i class="bi bi-send me-2"></i>Nộp đơn ứng tuyển</h4></div>
  <a href="../internships/browse.php" class="btn btn-secondary"><i class="bi bi-arrow-left me-1"></i>Quay lại</a>
</div>
<?php if(!empty($errors)): ?><div class="alert alert-danger fu"><ul class="mb-0"><?php foreach($errors as $e):?><li><?=htmlspecialchars($e)?></li><?php endforeach;?></ul></div><?php endif; ?>

<div class="row g-4">
  <div class="col-md-4">
    <div class="card fu" style="border:1.5px solid rgba(164,195,162,.25)">
      <div class="card-body">
        <div class="d-flex align-items-center gap-3 mb-3">
          <img src="<?=$logoUrl?>" style="width:54px;height:54px;border-radius:12px;object-fit:cover">
          <div><div class="fw7" style="font-size:.88rem"><?=htmlspecialchars($job['company_name'])?></div>
          <?php if($job['location']): ?><div class="small text-muted"><i class="bi bi-geo-alt me-1"></i><?=htmlspecialchars($job['location'])?></div><?php endif; ?></div>
        </div>
        <h5 class="fw8" style="font-size:1rem;color:var(--td)"><?=htmlspecialchars($job['title'])?></h5>
        <?php if($job['description']): ?><p class="small text-muted"><?=nl2br(htmlspecialchars($job['description']))?></p><?php endif; ?>
        <?php if($job['requirements']): ?>
        <div style="background:var(--wc);border-radius:9px;padding:10px 12px;font-size:.8rem">
          <div class="fw7 mb-1">Yêu cầu:</div>
          <div class="text-muted"><?=nl2br(htmlspecialchars($job['requirements']))?></div>
        </div>
        <?php endif; ?>
        <div class="mt-3 small text-muted">
          <?php if($job['start_date']): ?><div><i class="bi bi-calendar me-1"></i><?=date('d/m/Y',strtotime($job['start_date']))?> – <?=date('d/m/Y',strtotime($job['end_date']??$job['start_date']))?></div><?php endif; ?>
          <div><i class="bi bi-people me-1"></i>Số lượng: <?=$job['quantity']?> sinh viên</div>
        </div>
      </div>
    </div>
    <div class="card mt-3 fu1" style="background:rgba(234,231,214,.3)"><div class="card-body">
      <div class="small fw7 mb-2" style="color:var(--ds)">Quy trình xét duyệt:</div>
      <?php $steps=[['Bạn nộp đơn','send-fill'],['Trường xét duyệt','building'],['Công ty xét duyệt','building-fill'],['Phỏng vấn','camera-video'],['Bắt đầu thực tập','briefcase-fill']]; ?>
      <?php foreach($steps as $k=>[$lbl,$ico]): ?>
      <div class="d-flex align-items-center gap-2 mb-2">
        <div style="width:26px;height:26px;border-radius:50%;background:var(--ds);display:flex;align-items:center;justify-content:center;color:#fff;font-size:.65rem;flex-shrink:0"><?=$k+1?></div>
        <div class="small"><?=$lbl?></div>
      </div>
      <?php endforeach; ?>
    </div></div>
  </div>

  <div class="col-md-8">
    <?php if($existing): ?>
    <div class="alert alert-info fu1">
      <i class="bi bi-info-circle-fill me-2"></i>Bạn đã nộp đơn vị trí này.
      <?php [$elbl,$ebg,$ec]=appStatusLabel($existing['status']); ?>
      Trạng thái: <span class="badge" style="background:<?=$ebg?>;color:<?=$ec?>"><?=$elbl?></span>
      <a href="my_applications.php" class="btn btn-sm btn-primary ms-2">Xem đơn của tôi</a>
    </div>
    <?php else: ?>
    <div class="card fu1"><div class="card-body">
      <h6 class="fw7 mb-3" style="color:var(--ds)"><i class="bi bi-file-earmark-person me-2"></i>Hồ sơ ứng tuyển</h6>
      <div style="background:rgba(164,195,162,.08);border-radius:11px;padding:14px;margin-bottom:20px">
        <div class="row g-2 small">
          <div class="col-6"><div class="text-muted">Họ tên</div><div class="fw7"><?=htmlspecialchars($student['full_name'])?></div></div>
          <div class="col-6"><div class="text-muted">Mã SV</div><div class="fw7"><?=htmlspecialchars($student['student_code']??'—')?></div></div>
          <div class="col-6"><div class="text-muted">GPA</div><div class="fw7" style="color:var(--ds)"><?=$student['gpa']??'—'?></div></div>
          <div class="col-6"><div class="text-muted">Chuyên ngành</div><div class="fw7"><?=htmlspecialchars($student['major']??'—')?></div></div>
        </div>
      </div>
      <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="internship_id" value="<?=$iid?>">
        <div class="mb-4">
          <label class="form-label fw7">Upload CV <span class="text-danger">*</span></label>
          <input type="file" name="cv" class="form-control" accept=".pdf,.doc,.docx" required>
          <div class="small text-muted mt-1">PDF/DOC/DOCX, tối đa 5MB</div>
        </div>
        <div class="d-flex gap-2">
          <button type="submit" class="btn btn-primary"><i class="bi bi-send me-1"></i>Nộp đơn ứng tuyển</button>
          <a href="../internships/browse.php" class="btn btn-secondary">Hủy</a>
        </div>
      </form>
    </div></div>
    <?php endif; ?>
  </div>
</div>
<?php include BASE_PATH_FS . 'includes/footer.php'; ?>
