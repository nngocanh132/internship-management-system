<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';
requireRole(['company','admin']);

$uid=$_SESSION['user_id']; $role=getRole();
$id=(int)($_GET['id']??0); if(!$id) redirect('my_jobs.php');

$stmt=$conn->prepare("SELECT * FROM internships WHERE internship_id=?");
$stmt->bind_param('i',$id); $stmt->execute();
$j=$stmt->get_result()->fetch_assoc();
if(!$j){ setFlash('error','Không tìm thấy.'); redirect('my_jobs.php'); }

if($role==='company'){
    $cq=$conn->prepare("SELECT company_id FROM company_profiles WHERE user_id=?");
    $cq->bind_param('i',$uid); $cq->execute();
    $cid=$cq->get_result()->fetch_assoc()['company_id']??0;
    if($j['company_id']!=$cid){ setFlash('error','Không có quyền.'); redirect('my_jobs.php'); }
}

$errors=[];
if($_SERVER['REQUEST_METHOD']==='POST'){
    $title=sanitize($_POST['title']??''); $desc=sanitize($_POST['description']??'');
    $req=sanitize($_POST['requirements']??''); $qty=(int)($_POST['quantity']??1);
    $loc=sanitize($_POST['location']??''); $start=sanitize($_POST['start_date']??'');
    $end=sanitize($_POST['end_date']??''); $status=sanitize($_POST['status']??'open');
    if(empty($title)) $errors[]='Tiêu đề bắt buộc.';
    if(empty($errors)){
        $u=$conn->prepare("UPDATE internships SET title=?,description=?,requirements=?,quantity=?,location=?,start_date=?,end_date=?,status=? WHERE internship_id=?");
        $sd=!empty($start)?$start:null; $ed=!empty($end)?$end:null;
        $u->bind_param('ssssisssi',$title,$desc,$req,$qty,$loc,$sd,$ed,$status,$id);
        if($u->execute()){ setFlash('success','Đã cập nhật.'); redirect($role==='company'?'my_jobs.php':'list.php'); }
        else $errors[]='Lỗi: '.$conn->error;
    }
    $j=array_merge($j,$_POST);
}
?>
<?php include '../../includes/header.php'; ?>
<div class="ph fu"><div><h4><i class="bi bi-pencil-square me-2"></i>Sửa Vị trí</h4></div><a href="<?=$role==='company'?'my_jobs.php':'list.php'?>" class="btn btn-secondary"><i class="bi bi-arrow-left me-1"></i>Quay lại</a></div>
<?php if(!empty($errors)): ?><div class="alert alert-danger"><ul class="mb-0"><?php foreach($errors as $e):?><li><?=htmlspecialchars($e)?></li><?php endforeach;?></ul></div><?php endif; ?>
<div class="card fu1"><div class="card-body">
<form method="POST"><div class="row g-3">
  <div class="col-12"><label class="form-label">Tiêu đề *</label><input type="text" name="title" class="form-control" value="<?=htmlspecialchars($j['title'])?>" required></div>
  <div class="col-12"><label class="form-label">Mô tả</label><textarea name="description" class="form-control" rows="4"><?=htmlspecialchars($j['description']??'')?></textarea></div>
  <div class="col-12"><label class="form-label">Yêu cầu</label><textarea name="requirements" class="form-control" rows="3"><?=htmlspecialchars($j['requirements']??'')?></textarea></div>
  <div class="col-md-3"><label class="form-label">Số lượng *</label><input type="number" name="quantity" class="form-control" min="1" value="<?=$j['quantity']?>" required></div>
  <div class="col-md-5"><label class="form-label">Địa điểm</label><input type="text" name="location" class="form-control" value="<?=htmlspecialchars($j['location']??'')?>"></div>
  <div class="col-md-2"><label class="form-label">Trạng thái</label>
    <select name="status" class="form-select">
      <option value="open" <?=$j['status']==='open'?'selected':''?>>Đang mở</option>
      <option value="closed" <?=$j['status']==='closed'?'selected':''?>>Đóng</option>
    </select>
  </div>
  <div class="col-md-3"><label class="form-label">Ngày bắt đầu</label><input type="date" name="start_date" class="form-control" value="<?=htmlspecialchars($j['start_date']??'')?>"></div>
  <div class="col-md-3"><label class="form-label">Ngày kết thúc</label><input type="date" name="end_date" class="form-control" value="<?=htmlspecialchars($j['end_date']??'')?>"></div>
</div>
<hr>
<button type="submit" class="btn btn-warning"><i class="bi bi-save me-1"></i>Cập nhật</button>
<a href="<?=$role==='company'?'my_jobs.php':'list.php'?>" class="btn btn-secondary ms-2">Hủy</a>
</form></div></div>
<?php include '../../includes/footer.php'; ?>
