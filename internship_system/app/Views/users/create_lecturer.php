<?php // View: users/create_lecturer — nhận $errors ?>
<?php include BASE_PATH_FS . 'includes/header.php'; ?>
<div class="ph fu"><div><h4><i class="bi bi-person-plus-fill me-2"></i>Tạo tài khoản Giảng viên</h4></div><a href="list.php" class="btn btn-secondary"><i class="bi bi-arrow-left me-1"></i>Quay lại</a></div>
<?php if(!empty($errors)): ?><div class="alert alert-danger fu"><ul class="mb-0"><?php foreach($errors as $e):?><li><?=htmlspecialchars($e)?></li><?php endforeach;?></ul></div><?php endif; ?>
<div class="row g-4">
  <div class="col-md-6">
    <div class="card fu1"><div class="card-body">
      <h6 class="fw7 mb-3" style="color:var(--ds)"><i class="bi bi-key me-2"></i>Thông tin đăng nhập</h6>
      <form method="POST"><div class="row g-3">
        <div class="col-12"><label class="form-label">Email đăng nhập *</label><input type="email" name="email" class="form-control" value="<?=htmlspecialchars($_POST['email']??'')?>" required placeholder="lecturer@ischool.edu.vn"></div>
        <div class="col-12"><label class="form-label">Mật khẩu *</label><input type="password" name="password" class="form-control" minlength="6" required placeholder="Tối thiểu 6 ký tự"></div>
        <div class="col-12"><hr><h6 class="fw7" style="color:var(--ds)"><i class="bi bi-person-workspace me-2"></i>Thông tin giảng viên</h6></div>
        <div class="col-12"><label class="form-label">Họ và tên *</label><input type="text" name="full_name" class="form-control" value="<?=htmlspecialchars($_POST['full_name']??'')?>" required placeholder="TS. Nguyễn Văn A"></div>
        <div class="col-md-6"><label class="form-label">Khoa / Bộ môn</label><input type="text" name="department" class="form-control" value="<?=htmlspecialchars($_POST['department']??'')?>" placeholder="Khoa CNTT"></div>
        <div class="col-md-6"><label class="form-label">Số điện thoại</label><input type="text" name="phone" class="form-control" value="<?=htmlspecialchars($_POST['phone']??'')?>"></div>
      </div>
      <hr><button type="submit" class="btn btn-primary"><i class="bi bi-person-plus me-1"></i>Tạo tài khoản</button>
      <a href="list.php" class="btn btn-secondary ms-2">Hủy</a></form>
    </div></div>
  </div>
  <div class="col-md-6">
    <div class="card fu2" style="background:rgba(234,231,214,.3)"><div class="card-body">
      <h6 class="fw7 mb-2" style="color:var(--ds)"><i class="bi bi-info-circle me-2"></i>Lưu ý</h6>
      <ul class="small text-muted ps-3">
        <li>Chỉ Admin mới được tạo tài khoản Giảng viên</li>
        <li>Giảng viên không thể tự đăng ký</li>
        <li>Sau khi tạo, Admin phân công GV hướng dẫn sinh viên thực tập</li>
        <li>GV có thể xem tiến độ và duyệt báo cáo của SV</li>
      </ul>
    </div></div>
  </div>
</div>
<?php include BASE_PATH_FS . 'includes/footer.php'; ?>
