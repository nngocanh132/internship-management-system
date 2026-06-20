<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';
requireRole(['admin','student']);

$errors=[];
if($_SERVER['REQUEST_METHOD']==='POST'){
    $student_id=(int)($_POST['student_id']??$_SESSION['user_id']);
    $pos_id=(int)($_POST['position_id']??0);
    if(!$pos_id) $errors[]='Chọn vị trí thực tập.';
    if(empty($errors)){
        $chk=$conn->prepare("SELECT registration_id FROM internship_registrations WHERE student_id=? AND position_id=?");
        $chk->bind_param('ii',$student_id,$pos_id); $chk->execute();
        if($chk->get_result()->num_rows>0) $errors[]='Bạn đã đăng ký vị trí này rồi.';
    }
    if(empty($errors)){
        $stmt=$conn->prepare("INSERT INTO internship_registrations (student_id,position_id) VALUES (?,?)");
        $stmt->bind_param('ii',$student_id,$pos_id);
        if($stmt->execute()){setFlash('success','Đã đăng ký thực tập.');redirect('list.php');}
        else $errors[]='Lỗi: '.$conn->error;
    }
}
$positions=$conn->query("SELECT p.position_id,p.title,p.quota,c.name AS cname,
  (SELECT COUNT(*) FROM internship_registrations WHERE position_id=p.position_id AND status='approved') AS filled
  FROM internship_positions p JOIN companies c ON p.company_id=c.company_id
  WHERE p.status='open' ORDER BY p.title")->fetch_all(MYSQLI_ASSOC);
$students=isAdmin()?$conn->query("SELECT user_id,full_name,student_code FROM users WHERE role='student' AND status='active' ORDER BY full_name")->fetch_all(MYSQLI_ASSOC):[];
?>
<?php include '../../includes/header.php';?>
<div class="page-header fade-up"><div><h4><i class="bi bi-clipboard-plus me-2"></i>Đăng ký Thực tập</h4></div><a href="list.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Quay lại</a></div>
<?php if($errors): ?><div class="alert alert-danger"><ul class="mb-0"><?php foreach($errors as $e): ?><li><?=$e?></li><?php endforeach;?></ul></div><?php endif;?>
<div class="card fade-up-1"><div class="card-body">
<form method="POST"><div class="row g-3">
  <?php if(isAdmin()): ?>
  <div class="col-md-6"><label class="form-label">Sinh viên *</label>
    <select name="student_id" class="form-select" required>
      <option value="">— Chọn sinh viên —</option>
      <?php foreach($students as $s): ?><option value="<?=$s['user_id']?>"><?=htmlspecialchars($s['full_name'].' ('.$s['student_code'].')')?></option><?php endforeach;?>
    </select>
  </div>
  <?php endif;?>
  <div class="col-md-<?=isAdmin()?'6':'12'?>"><label class="form-label">Vị trí thực tập *</label>
    <select name="position_id" class="form-select" required>
      <option value="">— Chọn vị trí —</option>
      <?php foreach($positions as $p): ?>
      <option value="<?=$p['position_id']?>" <?=$p['filled']>=$p['quota']?'disabled':''?>>
        <?=htmlspecialchars($p['title'].' — '.$p['cname'].' ('.$p['filled'].'/'.$p['quota'].' chỗ)')?>
      </option>
      <?php endforeach;?>
    </select>
  </div>
</div>
<hr class="my-4">
<button type="submit" class="btn btn-primary"><i class="bi bi-send me-1"></i>Đăng ký</button>
<a href="list.php" class="btn btn-outline-secondary ms-2">Hủy</a>
</form></div></div>
<?php include '../../includes/footer.php';?>
