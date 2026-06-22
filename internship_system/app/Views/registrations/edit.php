<?php // View: registrations/edit — nhận $row, $errors ?>
<?php include BASE_PATH_FS . 'includes/header.php'; ?>
<div class="ph fu"><div><h4><i class="bi bi-pencil-square me-2"></i>Sửa trạng thái Đăng ký</h4></div><a href="list.php" class="btn btn-secondary"><i class="bi bi-arrow-left me-1"></i>Quay lại</a></div>
<?php if(!empty($errors)): ?><div class="alert alert-danger"><ul class="mb-0"><?php foreach($errors as $e):?><li><?=htmlspecialchars($e)?></li><?php endforeach;?></ul></div><?php endif; ?>
<div class="card fu1" style="max-width:400px"><div class="card-body">
  <form method="POST"><div class="mb-3">
    <label class="form-label">Trạng thái</label>
    <select name="status" class="form-select">
      <option value="active"    <?=$row['status']==='active'   ?'selected':''?>>🚀 Đang thực tập</option>
      <option value="completed" <?=$row['status']==='completed'?'selected':''?>>🏆 Hoàn thành</option>
    </select>
  </div>
  <button type="submit" class="btn btn-warning"><i class="bi bi-save me-1"></i>Cập nhật</button>
  <a href="list.php" class="btn btn-secondary ms-2">Hủy</a>
  </form>
</div></div>
<?php include BASE_PATH_FS . 'includes/footer.php'; ?>
