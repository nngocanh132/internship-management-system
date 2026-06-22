<?php // View: internships/create — nhận $errors từ controller ?>
<?php include BASE_PATH_FS . 'includes/header.php'; ?>
<div class="ph fu">
  <div><h4><i class="bi bi-plus-circle me-2"></i>Đăng Vị trí Thực tập</h4></div>
  <a href="my_jobs.php" class="btn btn-secondary"><i class="bi bi-arrow-left me-1"></i>Quay lại</a>
</div>
<?php if(!empty($errors)): ?><div class="alert alert-danger fu"><ul class="mb-0"><?php foreach($errors as $e):?><li><?=htmlspecialchars($e)?></li><?php endforeach;?></ul></div><?php endif; ?>
<div class="row g-4">
  <div class="col-lg-8">
    <div class="card fu1"><div class="card-body">
      <form method="POST"><div class="row g-3">
        <div class="col-12"><label class="form-label">Tiêu đề vị trí *</label><input type="text" name="title" class="form-control" value="<?=htmlspecialchars($_POST['title']??'')?>" required placeholder="VD: Backend Intern, Marketing Intern..."></div>
        <div class="col-12"><label class="form-label">Mô tả công việc</label><textarea name="description" class="form-control" rows="5" placeholder="Mô tả chi tiết công việc..."><?=htmlspecialchars($_POST['description']??'')?></textarea></div>
        <div class="col-12"><label class="form-label">Yêu cầu ứng viên</label><textarea name="requirements" class="form-control" rows="3" placeholder="GPA tối thiểu, kỹ năng cần có..."><?=htmlspecialchars($_POST['requirements']??'')?></textarea></div>
        <div class="col-md-4"><label class="form-label">Số lượng tuyển *</label><input type="number" name="quantity" class="form-control" min="1" value="<?=htmlspecialchars($_POST['quantity']??'1')?>" required></div>
        <div class="col-md-8"><label class="form-label">Địa điểm làm việc</label><input type="text" name="location" class="form-control" value="<?=htmlspecialchars($_POST['location']??'')?>" placeholder="Hà Nội, TP.HCM..."></div>
        <div class="col-md-6"><label class="form-label">Ngày bắt đầu</label><input type="date" name="start_date" class="form-control" value="<?=htmlspecialchars($_POST['start_date']??'')?>"></div>
        <div class="col-md-6"><label class="form-label">Ngày kết thúc</label><input type="date" name="end_date" class="form-control" value="<?=htmlspecialchars($_POST['end_date']??'')?>"></div>
      </div>
      <hr>
      <button type="submit" class="btn btn-primary"><i class="bi bi-send me-1"></i>Đăng vị trí</button>
      <a href="my_jobs.php" class="btn btn-secondary ms-2">Hủy</a>
      </form>
    </div></div>
  </div>
  <div class="col-lg-4">
    <div class="card fu2" style="background:rgba(234,231,214,.3)"><div class="card-body">
      <h6 class="fw7 mb-2" style="color:var(--ds)"><i class="bi bi-info-circle me-2"></i>Quy trình tuyển dụng</h6>
      <ol class="small text-muted ps-3">
        <li class="mb-1">Bạn đăng vị trí → hiển thị cho sinh viên</li>
        <li class="mb-1">Sinh viên nộp đơn + CV</li>
        <li class="mb-1">Trường xét duyệt</li>
        <li class="mb-1">Bạn quyết định nhận/từ chối</li>
        <li class="mb-1">Nhắn tin hẹn lịch phỏng vấn</li>
        <li>Sinh viên đậu → bắt đầu thực tập</li>
      </ol>
    </div></div>
  </div>
</div>
<?php include BASE_PATH_FS . 'includes/footer.php'; ?>
