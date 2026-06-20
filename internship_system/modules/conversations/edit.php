<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';
requireRole(['admin']);
$id=(int)($_GET['id']??0);
if ($id<=0) redirect('list.php');
$stmt=$conn->prepare("SELECT * FROM departments WHERE department_id=?");
$stmt->bind_param('i',$id); $stmt->execute();
$dept=$stmt->get_result()->fetch_assoc();
if (!$dept) { setFlash('error','Không tìm thấy.'); redirect('list.php'); }
$errors=[];
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $department_name=sanitize($_POST['department_name']??'');
    $faculty=sanitize($_POST['faculty']??'');
    if (empty($department_name)) $errors[]='Tên khoa không được để trống.';
    if (empty($errors)) {
        $chk=$conn->prepare("SELECT department_id FROM departments WHERE department_name=? AND department_id!=?");
        $chk->bind_param('si',$department_name,$id); $chk->execute();
        if ($chk->get_result()->num_rows>0) $errors[]='Tên khoa đã được sử dụng.';
    }
    if (empty($errors)) {
        $s=$conn->prepare("UPDATE departments SET department_name=?,faculty=? WHERE department_id=?");
        $s->bind_param('ssi',$department_name,$faculty,$id);
        if ($s->execute()) { setFlash('success',"Đã cập nhật <strong>$department_name</strong>."); redirect('list.php'); }
        else $errors[]='Lỗi: '.$conn->error;
    }
    $dept=array_merge($dept,$_POST);
}
?>
<?php include '../../includes/header.php'; ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4><i class="bi bi-pencil-square me-2 text-warning"></i>Chỉnh sửa Khoa / Bộ môn</h4>
    <a href="list.php" class="btn btn-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Quay lại</a>
</div>
<?php if ($errors): ?><div class="alert alert-danger"><ul class="mb-0"><?php foreach($errors as $e): ?><li><?= $e ?></li><?php endforeach; ?></ul></div><?php endif; ?>
<div class="card"><div class="card-body">
    <form method="POST"><div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Tên Khoa / Bộ môn <span class="text-danger">*</span></label>
            <input type="text" name="department_name" class="form-control" value="<?= htmlspecialchars($dept['department_name']) ?>" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">Trường / Khoa trực thuộc</label>
            <input type="text" name="faculty" class="form-control" value="<?= htmlspecialchars($dept['faculty']??'') ?>">
        </div>
    </div><hr>
    <button type="submit" class="btn btn-warning"><i class="bi bi-save me-1"></i>Cập nhật</button>
    <a href="list.php" class="btn btn-secondary ms-2">Hủy</a>
    </form>
</div></div>
<?php include '../../includes/footer.php'; ?>
