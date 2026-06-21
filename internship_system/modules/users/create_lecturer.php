<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';
requireRole('admin');

$errors=[];
if($_SERVER['REQUEST_METHOD']==='POST'){
    $email    =sanitize($_POST['email']??'');
    $pw       =sanitize($_POST['password']??'');
    $fullname =sanitize($_POST['full_name']??'');
    $dept     =sanitize($_POST['department']??'');
    $phone    =sanitize($_POST['phone']??'');
    $lemail   =sanitize($_POST['lecturer_email']??$email);

    if(empty($email)||!filter_var($email,FILTER_VALIDATE_EMAIL)) $errors[]='Email không hợp lệ.';
    if(strlen($pw)<6) $errors[]='Mật khẩu tối thiểu 6 ký tự.';
    if(empty($fullname)) $errors[]='Họ tên bắt buộc.';

    if(empty($errors)){
        $chk=$conn->prepare("SELECT user_id FROM users WHERE email=?"); $chk->bind_param('s',$email); $chk->execute();
        if($chk->get_result()->num_rows>0) $errors[]='Email đã tồn tại.';
    }
    if(empty($errors)){
        $hash=md5($pw);
        $ins=$conn->prepare("INSERT INTO users (email,password,role,is_profile_completed) VALUES (?,?,'lecturer',1)");
        $ins->bind_param('ss',$email,$hash);
        if($ins->execute()){
            $uid=$conn->insert_id;
            $lins=$conn->prepare("INSERT INTO lecturer_profiles (user_id,full_name,department,phone,email) VALUES (?,?,?,?,?)");
            $lins->bind_param('issss',$uid,$fullname,$dept,$phone,$lemail); $lins->execute();
            setFlash('success',"✅ Đã tạo tài khoản giảng viên cho $fullname.");
            redirect('list.php?role=lecturer');
        } else $errors[]='Lỗi: '.$conn->error;
    }
}
?>
<?php include '../../includes/header.php'; ?>
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
      <hr>
      <button type="submit" class="btn btn-primary"><i class="bi bi-person-plus me-1"></i>Tạo tài khoản</button>
      <a href="list.php" class="btn btn-secondary ms-2">Hủy</a>
      </form>
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
<?php include '../../includes/footer.php'; ?>
