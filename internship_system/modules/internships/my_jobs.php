<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';
requireRole('company');

$uid = $_SESSION['user_id'];
$cq  = $conn->prepare("SELECT company_id FROM company_profiles WHERE user_id=?");
$cid = 0;
if ($cq) { $cq->bind_param('i',$uid); $cq->execute(); $cid=$cq->get_result()->fetch_assoc()['company_id']??0; }

// Toggle status
if (isset($_GET['toggle']) && $cid) {
    $id  = (int)$_GET['toggle'];
    $cur = safeRow($conn,"SELECT status FROM internships WHERE internship_id=$id AND company_id=$cid");
    if ($cur) {
        $ns = $cur['status']==='open'?'closed':'open';
        $conn->query("UPDATE internships SET status='$ns' WHERE internship_id=$id");
        setFlash('success','Đã đổi trạng thái.');
    }
    redirect('my_jobs.php');
}

// Delete
if (isset($_GET['delete']) && $cid) {
    $id  = (int)$_GET['delete'];
    $cnt = safeCount($conn,"SELECT COUNT(*) c FROM applications WHERE internship_id=$id");
    if ($cnt > 0) { setFlash('error','Không thể xóa: đã có ứng viên.'); }
    else { $conn->query("DELETE FROM internships WHERE internship_id=$id AND company_id=$cid"); setFlash('success','Đã xóa.'); }
    redirect('my_jobs.php');
}

// POST: thêm vị trí mới
$errors = [];
$show_form = isset($_GET['new']) || !empty($errors);
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['create_job'])) {
    $title = sanitize($_POST['title']??'');
    $desc  = sanitize($_POST['description']??'');
    $req   = sanitize($_POST['requirements']??'');
    $qty   = max(1,(int)($_POST['quantity']??1));
    $loc   = sanitize($_POST['location']??'');
    $sd    = sanitize($_POST['start_date']??'');
    $ed    = sanitize($_POST['end_date']??'');
    if (empty($title)) $errors[]='Tiêu đề là bắt buộc.';
    if (!$cid)         $errors[]='Chưa có hồ sơ doanh nghiệp.';
    if (empty($errors)) {
        $ins=$conn->prepare("INSERT INTO internships (company_id,title,description,requirements,quantity,location,start_date,end_date,status) VALUES (?,?,?,?,?,?,?,?,'open')");
        $sdd=!empty($sd)?$sd:null; $edd=!empty($ed)?$ed:null;
        if ($ins) { $ins->bind_param('isssssss',$cid,$title,$desc,$req,$qty,$loc,$sdd,$edd); $ins->execute(); setFlash('success','✅ Đã đăng vị trí thực tập!'); redirect('my_jobs.php'); }
        else $errors[]='Lỗi: '.$conn->error;
    }
    $show_form = true;
}

$jobs = $cid ? safeQuery($conn,"SELECT i.*,(SELECT COUNT(*) FROM applications WHERE internship_id=i.internship_id) AS app_cnt FROM internships i WHERE company_id=$cid ORDER BY created_at DESC") : [];
?>
<?php include '../../includes/header.php'; ?>
<div class="ph fu">
  <div><h4><i class="bi bi-briefcase-fill me-2"></i>Vị trí Thực tập</h4><div class="ph-sub">Tổng: <?=count($jobs)?> vị trí</div></div>
  <a href="?new=1" class="btn btn-primary"><i class="bi bi-plus-circle me-1"></i>Đăng vị trí mới</a>
</div>
<?php showFlash(); ?>

<?php if($show_form): ?>
<!-- Form đăng vị trí mới -->
<div class="card mb-4 fu" style="border:2px solid var(--ds)">
  <div class="card-body">
    <h5 class="fw8 mb-3" style="color:var(--ds)"><i class="bi bi-plus-circle-fill me-2"></i>Đăng vị trí thực tập mới</h5>
    <?php if(!empty($errors)): ?><div class="alert alert-danger mb-3"><ul class="mb-0"><?php foreach($errors as $e):?><li><?=htmlspecialchars($e)?></li><?php endforeach;?></ul></div><?php endif; ?>
    <form method="POST"><input type="hidden" name="create_job" value="1">
      <div class="row g-3">
        <div class="col-12">
          <label class="form-label">Tiêu đề vị trí *</label>
          <input type="text" name="title" class="form-control" value="<?=htmlspecialchars($_POST['title']??'')?>" placeholder="VD: Backend Developer Intern" required>
        </div>
        <div class="col-12">
          <label class="form-label">Mô tả công việc</label>
          <textarea name="description" class="form-control" rows="4" placeholder="Mô tả chi tiết công việc, nhiệm vụ..."><?=htmlspecialchars($_POST['description']??'')?></textarea>
        </div>
        <div class="col-12">
          <label class="form-label">Yêu cầu ứng viên</label>
          <textarea name="requirements" class="form-control" rows="3" placeholder="GPA tối thiểu, kỹ năng cần có..."><?=htmlspecialchars($_POST['requirements']??'')?></textarea>
        </div>
        <div class="col-md-3"><label class="form-label">Số lượng *</label><input type="number" name="quantity" class="form-control" min="1" value="<?=htmlspecialchars($_POST['quantity']??'1')?>" required></div>
        <div class="col-md-5"><label class="form-label">Địa điểm</label><input type="text" name="location" class="form-control" value="<?=htmlspecialchars($_POST['location']??'')?>" placeholder="Hà Nội, TP.HCM..."></div>
        <div class="col-md-2"><label class="form-label">Ngày bắt đầu</label><input type="date" name="start_date" class="form-control" value="<?=htmlspecialchars($_POST['start_date']??'')?>"></div>
        <div class="col-md-2"><label class="form-label">Ngày kết thúc</label><input type="date" name="end_date" class="form-control" value="<?=htmlspecialchars($_POST['end_date']??'')?>"></div>
      </div>
      <hr class="my-3">
      <button type="submit" class="btn btn-primary"><i class="bi bi-send me-1"></i>Đăng ngay</button>
      <a href="my_jobs.php" class="btn btn-secondary ms-2">Hủy</a>
    </form>
  </div>
</div>
<?php endif; ?>

<?php if(empty($jobs)&&!$show_form): ?>
<div class="card text-center py-5 fu1"><div class="card-body">
  <i class="bi bi-briefcase" style="font-size:3rem;color:var(--tl)"></i>
  <h5 class="mt-3 fw7">Chưa có vị trí nào</h5>
  <p class="text-muted">Đăng vị trí thực tập để nhận hồ sơ từ sinh viên.</p>
  <a href="?new=1" class="btn btn-primary"><i class="bi bi-plus-circle me-1"></i>Đăng ngay</a>
</div></div>
<?php elseif(!empty($jobs)): ?>
<div class="card tc fu2"><div class="card-body p-0">
  <table class="table mb-0">
    <thead><tr><th>#</th><th>Vị trí</th><th>Địa điểm</th><th class="text-center">SL</th><th class="text-center">Ứng viên</th><th>Thời gian</th><th>Trạng thái</th><th>Thao tác</th></tr></thead>
    <tbody>
    <?php foreach($jobs as $i=>$j): ?>
    <tr>
      <td class="small text-muted"><?=$i+1?></td>
      <td>
        <div class="fw7 small"><?=htmlspecialchars($j['title'])?></div>
        <?php if($j['description']): ?><div class="small text-muted" style="max-width:200px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?=htmlspecialchars($j['description'])?></div><?php endif; ?>
      </td>
      <td class="small text-muted"><?=htmlspecialchars($j['location']??'—')?></td>
      <td class="text-center fw7"><?=$j['quantity']?></td>
      <td class="text-center">
        <a href="<?=BASE_PATH?>/modules/applications/company_candidates.php?job=<?=$j['internship_id']?>" class="badge bg-primary" style="text-decoration:none"><?=$j['app_cnt']?> ứng viên</a>
      </td>
      <td class="small text-muted"><?=$j['start_date']?date('d/m/Y',strtotime($j['start_date'])):''?><?php if($j['end_date']): ?><br>→ <?=date('d/m/Y',strtotime($j['end_date']))?><?php endif; ?></td>
      <td><span class="badge" style="<?=$j['status']==='open'?'background:rgba(74,158,106,.12);color:#2d6a40':'background:rgba(160,160,160,.12);color:#5a5a5a'?>"><?=$j['status']==='open'?'🟢 Đang mở':'⚫ Đã đóng'?></span></td>
      <td>
        <div class="d-flex gap-1">
          <a href="edit.php?id=<?=$j['internship_id']?>" class="btn btn-warning btn-sm" title="Sửa"><i class="bi bi-pencil"></i></a>
          <a href="?toggle=<?=$j['internship_id']?>" class="btn btn-secondary btn-sm" title="<?=$j['status']==='open'?'Đóng':'Mở'?>"><i class="bi bi-<?=$j['status']==='open'?'lock':'unlock'?>"></i></a>
          <a href="?delete=<?=$j['internship_id']?>" class="btn btn-danger btn-sm" onclick="return confirm('Xóa vị trí này?')" title="Xóa"><i class="bi bi-trash"></i></a>
        </div>
      </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div></div>
<?php endif; ?>
<?php include '../../includes/footer.php'; ?>
