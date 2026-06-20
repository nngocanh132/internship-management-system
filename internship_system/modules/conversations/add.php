<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';
requireRole(['admin']);
$errors=[];
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $department_name=sanitize($_POST['department_name']??'');
    $faculty=sanitize($_POST['faculty']??'');
    if (empty($department_name)) $errors[]='Tên khoa không được để trống.';
    if (empty($errors)) {
        $chk=$conn->prepare("SELECT department_id FROM departments WHERE department_name=?");
        $chk->bind_param('s',$department_name); $chk->execute();
        if ($chk->get_result()->num_rows>0) $errors[]='Tên khoa đã tồn tại.';
    }
    if (empty($errors)) {
        $s=$conn->prepare("INSERT INTO departments (department_name,faculty) VALUES (?,?)");
        $s->bind_param('ss',$department_name,$faculty);
        if ($s->execute()) { setFlash('success',"Đã thêm <strong>$department_name</strong>."); redirect('list.php'); }
        else $errors[]='Lỗi: '.$conn->error;
    }
}
?>
<?php include '../../includes/header.php'; ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4><i class="bi bi-diagram-3-fill me-2" style="color:#a78bfa"></i>Thêm Khoa / Bộ môn</h4>
    <a href="list.php" class="btn btn-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Quay lại</a>
</div>
<?php if ($errors): ?><div class="alert alert-danger"><ul class="mb-0"><?php foreach($errors as $e): ?><li><?= $e ?></li><?php endforeach; ?></ul></div><?php endif; ?>
<div class="card"><div class="card-body">
    <form method="POST"><div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Tên Khoa / Bộ môn <span class="text-danger">*</span></label>
            <input type="text" name="department_name" class="form-control" value="<?= htmlspecialchars($_POST['department_name']??'') ?>" placeholder="VD: Khoa CNTT" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">Trường / Khoa trực thuộc</label>
            <input type="text" name="faculty" class="form-control" value="<?= htmlspecialchars($_POST['faculty']??'') ?>" placeholder="VD: Đại học Công nghệ">
        </div>
    </div><hr>
    <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Lưu</button>
    <a href="list.php" class="btn btn-secondary ms-2">Hủy</a>
    </form>
</div></div>
<?php include '../../includes/footer.php'; ?>
