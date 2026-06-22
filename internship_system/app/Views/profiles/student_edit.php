<?php // View: profiles/student_edit — nhận $p, $errors, $pct, $av ?>
<?php include BASE_PATH_FS . 'includes/header.php'; ?>
<?php if(!($p['is_profile_completed']??0)): ?>
<div class="alert alert-warning fu"><i class="bi bi-exclamation-triangle-fill me-2"></i><strong>Hồ sơ chưa hoàn thiện!</strong> Điền đủ thông tin bắt buộc (*) để có thể ứng tuyển thực tập.</div>
<?php endif; ?>
<div class="ph fu1">
  <div><h4><i class="bi bi-person-circle me-2"></i>Hồ sơ Sinh viên</h4><div class="ph-sub">Hoàn thiện thông tin để ứng tuyển</div></div>
  <a href="<?=getDashboardUrl()?>" class="btn btn-secondary"><i class="bi bi-arrow-left me-1"></i>Quay lại</a>
</div>
<?php if(!empty($errors)): ?><div class="alert alert-danger fu"><ul class="mb-0"><?php foreach($errors as $e):?><li><?=htmlspecialchars($e)?></li><?php endforeach;?></ul></div><?php endif; ?>
<?php showFlash(); ?>
<form method="POST" enctype="multipart/form-data">
<div class="row g-4">
  <div class="col-md-3">
    <div class="card fu text-center"><div class="card-body">
      <img src="<?=$av?>" id="av_prev" style="width:110px;height:110px;border-radius:50%;object-fit:cover;border:3px solid var(--sg);margin-bottom:14px">
      <div class="mb-3"><label class="form-label">Ảnh đại diện</label>
        <input type="file" name="avatar" class="form-control form-control-sm" accept="image/*" onchange="prevImg(this,'av_prev')">
        <div class="small text-muted mt-1">JPG/PNG, tối đa 2MB</div>
      </div>
      <hr>
      <div class="small fw7 mb-1" style="color:var(--tm)">Mức hoàn thiện</div>
      <div style="height:8px;background:rgba(164,195,162,.22);border-radius:4px;overflow:hidden">
        <div style="height:8px;background:linear-gradient(90deg,var(--ds),var(--sg));border-radius:4px;width:<?=$pct?>%"></div>
      </div>
      <div class="small mt-1 fw7" style="color:var(--ds)"><?=$pct?>%</div>
      <?php if($pct<100): ?><div class="small text-muted mt-1">Cần điền đủ để ứng tuyển</div>
      <?php else: ?><div class="small mt-1" style="color:#2d6a40;font-weight:600">✅ Sẵn sàng ứng tuyển!</div><?php endif; ?>
    </div></div>
  </div>
  <div class="col-md-9">
    <div class="card fu1"><div class="card-body">
      <h6 class="fw7 mb-3" style="color:var(--ds)"><i class="bi bi-person-fill me-2"></i>Thông tin cá nhân</h6>
      <div class="row g-3">
        <div class="col-md-6"><label class="form-label">Họ và tên *</label><input type="text" name="full_name" class="form-control" value="<?=htmlspecialchars($p['full_name']??'')?>" required></div>
        <div class="col-md-6"><label class="form-label">Email</label><input type="text" class="form-control" value="<?=htmlspecialchars($p['email']??'')?>" disabled style="background:var(--wc)"></div>
        <div class="col-md-6"><label class="form-label">Số điện thoại *</label><input type="text" name="phone" class="form-control" value="<?=htmlspecialchars($p['phone']??'')?>" required placeholder="0901 234 567"></div>
        <div class="col-md-6"><label class="form-label">LinkedIn</label><input type="url" name="linkedin_url" class="form-control" value="<?=htmlspecialchars($p['linkedin_url']??'')?>" placeholder="https://linkedin.com/in/..."></div>
      </div>
      <hr class="my-3">
      <h6 class="fw7 mb-3" style="color:var(--ds)"><i class="bi bi-mortarboard-fill me-2"></i>Học vấn</h6>
      <div class="row g-3">
        <div class="col-md-4"><label class="form-label">Mã sinh viên *</label><input type="text" name="student_code" class="form-control" value="<?=htmlspecialchars($p['student_code']??'')?>" required placeholder="SV2024001"></div>
        <div class="col-md-5"><label class="form-label">Chuyên ngành *</label><input type="text" name="major" class="form-control" value="<?=htmlspecialchars($p['major']??'')?>" required placeholder="Công nghệ thông tin"></div>
        <div class="col-md-3"><label class="form-label">GPA (0–4) *</label><input type="number" name="gpa" class="form-control" min="0" max="4" step="0.01" value="<?=htmlspecialchars($p['gpa']??'')?>" required placeholder="3.50"></div>
        <div class="col-12"><label class="form-label">Giới thiệu bản thân</label><textarea name="about_me" class="form-control" rows="4" placeholder="Kỹ năng, kinh nghiệm, mục tiêu thực tập..."><?=htmlspecialchars($p['about_me']??'')?></textarea></div>
      </div>
      <hr class="my-3">
      <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Lưu hồ sơ</button>
      <a href="<?=getDashboardUrl()?>" class="btn btn-secondary ms-2">Bỏ qua</a>
    </div></div>
  </div>
</div>
</form>
<script>function prevImg(i,id){if(i.files[0]){const r=new FileReader();r.onload=e=>document.getElementById(id).src=e.target.result;r.readAsDataURL(i.files[0]);}}</script>
<?php include BASE_PATH_FS . 'includes/footer.php'; ?>
