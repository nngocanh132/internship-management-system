<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';
requireRole('admin');

$id=(int)($_GET['id']??0); if(!$id) redirect('list.php');
$stmt=$conn->prepare("SELECT * FROM internship_registrations WHERE registration_id=?"); $stmt->bind_param('i',$id); $stmt->execute();
$row=$stmt->get_result()->fetch_assoc();
if(!$row){setFlash('error','Không tìm thấy.');redirect('list.php');}

$errors=[];
if($_SERVER['REQUEST_METHOD']==='POST'){
    $status=sanitize($_POST['status']??$row['status']);
    $stmt=$conn->prepare("UPDATE internship_registrations SET status=? WHERE registration_id=?");
    $stmt->bind_param('si',$status,$id);
    if($stmt->execute()){setFlash('success','Đã cập nhật trạng thái.');redirect('list.php');}
    else $errors[]='Lỗi: '.$conn->error;
}
?>
<?php include '../../includes/header.php';?>
<div class="page-header fade-up"><div><h4><i class="bi bi-pencil-square me-2"></i>Sửa Đăng ký</h4></div><a href="list.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Quay lại</a></div>
<?php if($errors): ?><div class="alert alert-danger"><ul class="mb-0"><?php foreach($errors as $e): ?><li><?=$e?></li><?php endforeach;?></ul></div><?php endif;?>
<div class="card fade-up-1"><div class="card-body">
<form method="POST"><div class="row g-3">
  <div class="col-md-4"><label class="form-label">Trạng thái</label>
    <select name="status" class="form-select">
      <option value="pending"   <?=$row['status']==='pending'  ?'selected':''?>>Chờ duyệt</option>
      <option value="approved"  <?=$row['status']==='approved' ?'selected':''?>>Đã duyệt</option>
      <option value="rejected"  <?=$row['status']==='rejected' ?'selected':''?>>Từ chối</option>
      <option value="cancelled" <?=$row['status']==='cancelled'?'selected':''?>>Đã hủy</option>
    </select>
  </div>
</div>
<hr class="my-4">
<button type="submit" class="btn btn-warning"><i class="bi bi-save me-1"></i>Cập nhật</button>
<a href="list.php" class="btn btn-outline-secondary ms-2">Hủy</a>
</form></div></div>
<?php include '../../includes/footer.php';?>
