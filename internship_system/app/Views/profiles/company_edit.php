<?php // View: profiles/company_edit — nhận $p, $errors, $logoUrl ?>
<?php include BASE_PATH_FS . 'includes/header.php'; ?>
<?php if(!($p['is_profile_completed']??0)): ?>
<div class="alert alert-warning fu"><i class="bi bi-exclamation-triangle-fill me-2"></i><strong>Hồ sơ chưa hoàn thiện!</strong> Cần có: Tên, MST, Địa chỉ và Giấy phép KD để đăng vị trí thực tập.</div>
<?php endif; ?>
<div class="ph fu1">
  <div><h4><i class="bi bi-building-fill me-2"></i>Hồ sơ Doanh nghiệp</h4><div class="ph-sub">Hoàn thiện để đăng vị trí thực tập</div></div>
  <a href="<?=getDashboardUrl()?>" class="btn btn-secondary"><i class="bi bi-arrow-left me-1"></i>Quay lại</a>
</div>
<?php if(!empty($errors)): ?><div class="alert alert-danger fu"><ul class="mb-0"><?php foreach($errors as $e):?><li><?=htmlspecialchars($e)?></li><?php endforeach;?></ul></div><?php endif; ?>
<?php showFlash(); ?>
<form method="POST" enctype="multipart/form-data">
<div class="row g-4">
  <div class="col-md-3">
    <div class="card fu text-center"><div class="card-body">
      <img src="<?=$logoUrl?>" id="logo_prev" style="width:100px;height:100px;border-radius:14px;object-fit:cover;border:2px solid var(--sg);margin-bottom:12px">
      <div class="mb-3"><label class="form-label">Logo công ty</label>
        <input type="file" name="logo" class="form-control form-control-sm" accept="image/*" onchange="prevImg(this,'logo_prev')">
        <div class="small text-muted mt-1">JPG/PNG, tối đa 2MB</div>
      </div>
      <hr>
      <label class="form-label fw7" style="color:#9a3030;font-size:.8rem"><i class="bi bi-file-earmark-check-fill me-1"></i>Giấy phép KD *</label>
      <?php if($p['business_license_file']??''): ?>
      <a href="<?=UPLOAD_URL.'/'.$p['business_license_file']?>" target="_blank" class="btn btn-success btn-sm w-100 mb-2"><i class="bi bi-check-circle-fill me-1"></i>Đã tải lên — Xem</a>
      <?php else: ?>
      <div class="alert alert-danger p-2 mb-2" style="font-size:.75rem;border-radius:8px">⚠️ Chưa có — Bắt buộc!</div>
      <?php endif; ?>
      <input type="file" name="license" class="form-control form-control-sm" accept=".pdf,.jpg,.jpeg,.png">
      <div class="small text-muted mt-1">PDF/JPG, tối đa 10MB</div>
    </div></div>
  </div>
  <div class="col-md-9">
    <div class="card fu1"><div class="card-body">
      <h6 class="fw7 mb-3" style="color:var(--ds)"><i class="bi bi-building me-2"></i>Thông tin cơ bản</h6>
      <div class="row g-3">
        <div class="col-md-8"><label class="form-label">Tên doanh nghiệp *</label><input type="text" name="company_name" class="form-control" value="<?=htmlspecialchars($p['company_name']??'')?>" required></div>
        <div class="col-md-4"><label class="form-label">Mã số thuế *</label><input type="text" name="tax_code" class="form-control" value="<?=htmlspecialchars($p['tax_code']??'')?>" required placeholder="0123456789"></div>
        <div class="col-md-6"><label class="form-label">Lĩnh vực</label>
          <select name="industry" class="form-select">
            <option value="">— Chọn —</option>
            <?php foreach(['Công nghệ thông tin','Tài chính - Ngân hàng','Thương mại điện tử','Sản xuất','Giáo dục','Y tế','Marketing','Logistics','Bất động sản','Khác'] as $ind): ?>
            <option value="<?=$ind?>" <?=($p['industry']??'')==$ind?'selected':''?>><?=$ind?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-3"><label class="form-label">Quy mô nhân sự</label>
          <select name="company_size" class="form-select">
            <option value="">— Chọn —</option>
            <?php foreach(['1-10','11-50','51-200','201-500','500+'] as $sz): ?>
            <option value="<?=$sz?>" <?=($p['company_size']??'')==$sz?'selected':''?>><?=$sz?> người</option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-3"><label class="form-label">Số điện thoại</label><input type="text" name="phone" class="form-control" value="<?=htmlspecialchars($p['phone']??'')?>"></div>
        <div class="col-md-6"><label class="form-label">Website</label><input type="url" name="website" class="form-control" value="<?=htmlspecialchars($p['website']??'')?>" placeholder="https://..."></div>
        <div class="col-12"><label class="form-label">Địa chỉ trụ sở *</label><input type="text" name="address" class="form-control" value="<?=htmlspecialchars($p['address']??'')?>" required placeholder="Số nhà, đường, quận, thành phố"></div>
        <div class="col-12"><label class="form-label">Giới thiệu công ty</label><textarea name="description" class="form-control" rows="3" placeholder="Mô tả về công ty, văn hóa làm việc..."><?=htmlspecialchars($p['description']??'')?></textarea></div>
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
