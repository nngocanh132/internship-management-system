<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';
requireRole('company');

$uid = $_SESSION['user_id'];
$cp  = null; $cid = 0;

// Lấy company profile — không crash
$cq = $conn->prepare("SELECT cp.*,u.is_profile_completed FROM company_profiles cp JOIN users u ON cp.user_id=u.user_id WHERE cp.user_id=?");
if ($cq) {
    $cq->bind_param('i',$uid); $cq->execute();
    $cp = $cq->get_result()->fetch_assoc();
}
// Tạo profile trống nếu chưa có
if (!$cp) {
    $ins = $conn->prepare("INSERT IGNORE INTO company_profiles (user_id,company_name) VALUES (?,?)");
    if ($ins) { $ins->bind_param('is',$uid,$_SESSION['full_name']??'Công ty'); $ins->execute(); }
    if ($cq) { $cq->bind_param('i',$uid); $cq->execute(); $cp=$cq->get_result()->fetch_assoc(); }
}
if ($cp) $cid = $cp['company_id'] ?? 0;

// Stats — dùng safeCount để không crash
$stats = [
    'jobs'    => $cid ? safeCount($conn,"SELECT COUNT(*) c FROM internships WHERE company_id=$cid AND status='open'") : 0,
    'pending' => $cid ? safeCount($conn,"SELECT COUNT(*) c FROM applications a JOIN internships i ON a.internship_id=i.internship_id WHERE i.company_id=$cid AND a.status='approved_admin'") : 0,
    'active'  => $cid ? safeCount($conn,"SELECT COUNT(*) c FROM internship_registrations WHERE company_id=$cid AND status='active'") : 0,
    'eval'    => $cid ? safeCount($conn,"SELECT COUNT(*) c FROM evaluations e JOIN internship_registrations ir ON e.registration_id=ir.registration_id WHERE ir.company_id=$cid") : 0,
];
$unread = getUnreadCount($conn,$uid);

// Ứng viên chờ công ty duyệt
$pending_apps = $cid ? safeQuery($conn,"SELECT a.*,sp.full_name,sp.student_code,sp.gpa,sp.avatar AS s_av,sp.student_id,i.title FROM applications a JOIN internships i ON a.internship_id=i.internship_id JOIN student_profiles sp ON a.student_id=sp.student_id WHERE i.company_id=$cid AND a.status='approved_admin' ORDER BY sp.gpa DESC LIMIT 5") : [];

// Vị trí gần đây
$my_jobs = $cid ? safeQuery($conn,"SELECT i.*,(SELECT COUNT(*) FROM applications WHERE internship_id=i.internship_id AND status NOT IN ('rejected_admin','rejected_company')) AS app_cnt FROM internships i WHERE company_id=$cid ORDER BY created_at DESC LIMIT 5") : [];

$logo = ($cp['logo']??'') ? UPLOAD_URL.'/'.$cp['logo'] : 'https://ui-avatars.com/api/?name='.urlencode($cp['company_name']??'DN').'&background=5D7B6F&color=fff&size=80&bold=true';
?>
<?php include '../includes/header.php'; ?>

<?php if (!($cp['is_profile_completed']??0)): ?>
<div class="alert alert-warning fu">
  <i class="bi bi-exclamation-triangle-fill me-2"></i>
  <strong>Hồ sơ chưa hoàn thiện!</strong> Cần Tên DN, MST, Địa chỉ và Giấy phép KD để đăng vị trí.
  <a href="<?=BASE_PATH?>/modules/company_profiles/edit.php" class="btn btn-warning btn-sm ms-2">Hoàn thiện ngay</a>
</div>
<?php endif; ?>

<div class="row g-4 mb-4">
  <!-- Company card -->
  <div class="col-md-3 fu">
    <div class="card h-100 text-center" style="background:linear-gradient(175deg,var(--ds3),var(--ds2));border:none">
      <div class="card-body text-white py-4">
        <img src="<?=$logo?>" style="width:80px;height:80px;border-radius:16px;object-fit:cover;border:2px solid rgba(255,255,255,.3);margin-bottom:10px">
        <h5 class="fw8 mb-0"><?=htmlspecialchars($cp['company_name']??$_SESSION['full_name']??'')?></h5>
        <?php if($cp['industry']??''): ?><div style="opacity:.8;font-size:.82rem"><?=htmlspecialchars($cp['industry'])?></div><?php endif; ?>
        <?php if($cp['address']??''): ?><div style="opacity:.7;font-size:.78rem"><i class="bi bi-geo-alt me-1"></i><?=htmlspecialchars(mb_substr($cp['address'],0,40))?></div><?php endif; ?>
        <div class="mt-3 d-flex gap-2 justify-content-center flex-wrap">
          <a href="<?=BASE_PATH?>/modules/company_profiles/edit.php" class="btn btn-sm" style="background:rgba(255,255,255,.18);color:#fff;border:1px solid rgba(255,255,255,.25)"><i class="bi bi-pencil me-1"></i>Hồ sơ</a>
          <a href="<?=BASE_PATH?>/modules/internships/create.php" class="btn btn-sm" style="background:rgba(255,255,255,.18);color:#fff;border:1px solid rgba(255,255,255,.25)"><i class="bi bi-plus me-1"></i>Đăng việc</a>
        </div>
      </div>
    </div>
  </div>

  <div class="col-md-9">
    <div class="row g-3 mb-3">
      <div class="col-6 col-md-3 fu"><div class="stat-card sc-green"><div class="s-bg"><i class="bi bi-briefcase"></i></div><div class="s-icon"><i class="bi bi-briefcase-fill"></i></div><div class="s-num"><?=$stats['jobs']?></div><div class="s-lbl">Vị trí mở</div><a href="<?=BASE_PATH?>/modules/internships/my_jobs.php" class="s-link">Xem <i class="bi bi-arrow-right"></i></a></div></div>
      <div class="col-6 col-md-3 fu1"><div class="stat-card sc-warm"><div class="s-bg"><i class="bi bi-people"></i></div><div class="s-icon"><i class="bi bi-people-fill"></i></div><div class="s-num"><?=$stats['pending']?></div><div class="s-lbl">Chờ bạn duyệt</div><a href="<?=BASE_PATH?>/modules/applications/company_review.php" class="s-link">Duyệt ngay <i class="bi bi-arrow-right"></i></a></div></div>
      <div class="col-6 col-md-3 fu2"><div class="stat-card sc-mint"><div class="s-bg"><i class="bi bi-person-check"></i></div><div class="s-icon"><i class="bi bi-person-check-fill"></i></div><div class="s-num"><?=$stats['active']?></div><div class="s-lbl">Đang thực tập</div></div></div>
      <div class="col-6 col-md-3 fu3"><div class="stat-card sc-teal"><div class="s-bg"><i class="bi bi-chat"></i></div><div class="s-icon"><i class="bi bi-chat-dots-fill"></i></div><div class="s-num"><?=$unread?></div><div class="s-lbl">Tin chưa đọc</div><a href="<?=BASE_PATH?>/modules/messages/inbox.php" class="s-link">Xem <i class="bi bi-arrow-right"></i></a></div></div>
    </div>
    <div class="card fu4"><div class="card-body">
      <h6 class="fw7 mb-2" style="color:var(--ds)">Thao tác nhanh</h6>
      <div class="row g-2">
        <div class="col-6"><a href="<?=BASE_PATH?>/modules/internships/create.php" class="qa"><div class="qa-icon" style="background:rgba(93,123,111,.1)"><i class="bi bi-plus-circle-fill" style="color:var(--ds)"></i></div><div><div class="qa-title">Đăng vị trí mới</div><div class="qa-sub">Tuyển thực tập sinh</div></div></a></div>
        <div class="col-6"><a href="<?=BASE_PATH?>/modules/applications/company_review.php" class="qa"><div class="qa-icon" style="background:rgba(196,154,108,.1)"><i class="bi bi-people-fill" style="color:#a07040"></i></div><div><div class="qa-title">Xét duyệt hồ sơ</div><div class="qa-sub"><?=$stats['pending']?> chờ duyệt</div></div></a></div>
        <div class="col-6"><a href="<?=BASE_PATH?>/modules/evaluations/add.php" class="qa"><div class="qa-icon" style="background:rgba(74,158,106,.1)"><i class="bi bi-star-fill" style="color:#2d6a40"></i></div><div><div class="qa-title">Đánh giá SV</div><div class="qa-sub">Chấm điểm thực tập</div></div></a></div>
        <div class="col-6"><a href="<?=BASE_PATH?>/modules/messages/inbox.php" class="qa"><div class="qa-icon" style="background:rgba(74,138,150,.1)"><i class="bi bi-chat-dots-fill" style="color:#3a8a96"></i></div><div><div class="qa-title">Hộp thư</div><div class="qa-sub"><?=$unread?> chưa đọc</div></div></a></div>
      </div>
    </div></div>
  </div>
</div>

<div class="row g-4">
  <!-- Ứng viên chờ duyệt -->
  <?php if(!empty($pending_apps)): ?>
  <div class="col-lg-6">
    <div class="card tc fu1"><div class="card-header d-flex justify-content-between">
      <span><i class="bi bi-person-check me-2"></i>Hồ sơ chờ bạn duyệt (<?=count($pending_apps)?>)</span>
      <a href="<?=BASE_PATH?>/modules/applications/company_review.php" class="btn btn-warning btn-sm">Xem tất cả</a>
    </div><div class="card-body p-0"><table class="table mb-0">
      <thead><tr><th>Sinh viên</th><th>Vị trí</th><th>GPA</th><th>CV</th><th>Thao tác</th></tr></thead>
      <tbody>
      <?php foreach($pending_apps as $a):
        $sav = ($a['s_av']??'') ? UPLOAD_URL.'/'.$a['s_av'] : null;
      ?>
      <tr>
        <td>
          <div class="d-flex align-items-center gap-2">
            <?php if($sav): ?><img src="<?=$sav?>" style="width:28px;height:28px;border-radius:7px;object-fit:cover">
            <?php else: ?><div class="av" style="width:28px;height:28px;font-size:.7rem"><?=strtoupper(mb_substr($a['full_name'],0,1))?></div><?php endif; ?>
            <div><div class="fw7 small"><?=htmlspecialchars($a['full_name'])?></div><div class="small text-muted"><?=htmlspecialchars($a['student_code']??'')?></div></div>
          </div>
        </td>
        <td class="small"><?=htmlspecialchars($a['title'])?></td>
        <td><span class="badge bg-primary"><?=$a['gpa']?></span></td>
        <td><?php if($a['cv_file']??''): ?><a href="<?=UPLOAD_URL.'/'.$a['cv_file']?>" target="_blank" class="btn btn-secondary btn-sm"><i class="bi bi-file-earmark-pdf"></i></a><?php else: ?>—<?php endif; ?></td>
        <td>
          <div class="d-flex gap-1">
            <a href="<?=BASE_PATH?>/modules/applications/company_review.php?approve=<?=$a['application_id']?>" class="btn btn-success btn-sm" onclick="return confirm('Chấp nhận?')"><i class="bi bi-check-lg"></i></a>
            <a href="<?=BASE_PATH?>/modules/messages/chat.php?student_id=<?=$a['student_id']?>&app_id=<?=$a['application_id']?>" class="btn btn-primary btn-sm"><i class="bi bi-chat-dots"></i></a>
          </div>
        </td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table></div></div>
  </div>
  <?php endif; ?>

  <!-- Vị trí của tôi -->
  <div class="col-lg-<?=!empty($pending_apps)?'6':'12'?>">
    <div class="card tc fu2"><div class="card-header d-flex justify-content-between">
      <span><i class="bi bi-briefcase-fill me-2"></i>Vị trí của tôi</span>
      <div class="d-flex gap-1">
        <a href="<?=BASE_PATH?>/modules/internships/create.php" class="btn btn-primary btn-sm"><i class="bi bi-plus"></i></a>
        <a href="<?=BASE_PATH?>/modules/internships/my_jobs.php" class="btn btn-secondary btn-sm">Tất cả</a>
      </div>
    </div><div class="card-body p-0"><table class="table mb-0">
      <thead><tr><th>Vị trí</th><th class="text-center">Đơn</th><th>Trạng thái</th><th>Sửa</th></tr></thead>
      <tbody>
      <?php if(empty($my_jobs)): ?>
        <tr><td colspan="4" class="text-center py-4 text-muted">Chưa có vị trí. <a href="<?=BASE_PATH?>/modules/internships/create.php">Đăng ngay?</a></td></tr>
      <?php else: foreach($my_jobs as $j): ?>
      <tr>
        <td><div class="fw7 small"><?=htmlspecialchars($j['title'])?></div><?php if($j['location']??''): ?><div class="small text-muted"><?=htmlspecialchars($j['location'])?></div><?php endif; ?></td>
        <td class="text-center"><span class="badge bg-primary"><?=$j['app_cnt']?></span></td>
        <td><span class="badge" style="<?=$j['status']==='open'?'background:rgba(74,158,106,.12);color:#2d6a40':'background:rgba(160,160,160,.12);color:#5a5a5a'?>"><?=$j['status']==='open'?'🟢 Mở':'⚫ Đóng'?></span></td>
        <td><a href="<?=BASE_PATH?>/modules/internships/edit.php?id=<?=$j['internship_id']?>" class="btn btn-warning btn-sm"><i class="bi bi-pencil"></i></a></td>
      </tr>
      <?php endforeach; endif; ?>
      </tbody>
    </table></div></div>
  </div>
</div>
<?php include '../includes/footer.php'; ?>
