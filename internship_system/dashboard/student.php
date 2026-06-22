<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';
requireRole('student');

$uid = $_SESSION['user_id'];

// Lấy profile sinh viên — không crash nếu bảng chưa có
$sv  = null;
$sid = 0;
$sq  = $conn->prepare("SELECT sp.*,u.is_profile_completed FROM student_profiles sp JOIN users u ON sp.user_id=u.user_id WHERE sp.user_id=?");
if ($sq) {
    $sq->bind_param('i',$uid); $sq->execute();
    $sv = $sq->get_result()->fetch_assoc();
}
// Tạo profile trống nếu chưa có
if (!$sv) {
    $ins = $conn->prepare("INSERT IGNORE INTO student_profiles (user_id,full_name) VALUES (?,?)");
    if ($ins) { $ins->bind_param('is',$uid,$_SESSION['full_name']??''); $ins->execute(); }
    if ($sq) { $sq->bind_param('i',$uid); $sq->execute(); $sv=$sq->get_result()->fetch_assoc(); }
}
if ($sv) $sid = $sv['student_id'] ?? 0;

// Stats
$open_jobs = safeCount($conn,"SELECT COUNT(*) c FROM internships WHERE status='open'");
$app_cnt   = 0; $pend_cnt = 0;
if ($sid) {
    $app_cnt  = safeCount($conn,"SELECT COUNT(*) c FROM applications WHERE student_id=$sid");
    $pend_cnt = safeCount($conn,"SELECT COUNT(*) c FROM applications WHERE student_id=$sid AND status='pending_admin'");
}
$unread = getUnreadCount($conn,$uid);

// Internship hiện tại
$internship = null;
if ($sid) {
    $rq = $conn->prepare("SELECT ir.*,i.title,cp.company_name,cp.logo,lp.full_name AS lname,ev.overall_score,rp.status AS rep_status
      FROM internship_registrations ir
      JOIN internships i ON ir.internship_id=i.internship_id
      JOIN company_profiles cp ON ir.company_id=cp.company_id
      LEFT JOIN lecturer_profiles lp ON ir.lecturer_id=lp.lecturer_id
      LEFT JOIN evaluations ev ON ir.registration_id=ev.registration_id
      LEFT JOIN internship_reports rp ON ir.registration_id=rp.registration_id
      WHERE ir.student_id=? ORDER BY ir.created_at DESC LIMIT 1");
    if ($rq) { $rq->bind_param('i',$sid); $rq->execute(); $internship=$rq->get_result()->fetch_assoc(); }
}

// Đơn gần đây
$rapps = [];
if ($sid) {
    $ra = $conn->prepare("SELECT a.*,i.title,cp.company_name,cp.logo FROM applications a JOIN internships i ON a.internship_id=i.internship_id JOIN company_profiles cp ON i.company_id=cp.company_id WHERE a.student_id=? ORDER BY a.applied_at DESC LIMIT 5");
    if ($ra) { $ra->bind_param('i',$sid); $ra->execute(); $rapps=$ra->get_result()->fetch_all(MYSQLI_ASSOC); }
}

$av = ($sv['avatar']??'') ? UPLOAD_URL.'/'.$sv['avatar'] : 'https://ui-avatars.com/api/?name='.urlencode($sv['full_name']??$_SESSION['full_name']??'SV').'&background=5D7B6F&color=fff&size=100';
?>
<?php include '../includes/header.php'; ?>

<?php if(!($sv['is_profile_completed']??0)): ?>
<div class="alert alert-warning fu">
  <i class="bi bi-exclamation-triangle-fill me-2"></i>
  <strong>Hồ sơ chưa hoàn thiện!</strong> Điền đủ thông tin để ứng tuyển thực tập.
  <a href="<?=BASE_PATH?>/modules/student_profiles/edit.php" class="btn btn-warning btn-sm ms-2">Hoàn thiện ngay</a>
</div>
<?php endif; ?>

<div class="row g-4 mb-4">
  <!-- Profile card -->
  <div class="col-md-3 fu">
    <div class="card h-100 text-center" style="background:linear-gradient(175deg,var(--ds3),var(--ds2));border:none">
      <div class="card-body text-white py-4">
        <img src="<?=$av?>" style="width:80px;height:80px;border-radius:50%;object-fit:cover;border:3px solid rgba(255,255,255,.3);margin-bottom:10px">
        <h5 class="fw8 mb-0"><?=htmlspecialchars($sv['full_name']??$_SESSION['full_name']??'')?></h5>
        <div style="opacity:.8;font-size:.82rem;margin-top:3px"><?=htmlspecialchars($sv['student_code']??'—')?></div>
        <div style="opacity:.75;font-size:.8rem"><?=htmlspecialchars($sv['major']??'—')?></div>
        <?php if($sv['gpa']??0): ?><div class="mt-2"><span style="background:rgba(255,255,255,.18);padding:4px 12px;border-radius:20px;font-size:.82rem;font-weight:700">GPA: <?=$sv['gpa']?>/4.0</span></div><?php endif; ?>
        <div class="mt-3 d-flex gap-2 justify-content-center flex-wrap">
          <a href="<?=BASE_PATH?>/modules/student_profiles/edit.php" class="btn btn-sm" style="background:rgba(255,255,255,.18);color:#fff;border:1px solid rgba(255,255,255,.25)"><i class="bi bi-pencil me-1"></i>Hồ sơ</a>
          <?php if($unread>0): ?><a href="<?=BASE_PATH?>/modules/messages/inbox.php" class="btn btn-sm" style="background:rgba(255,255,255,.18);color:#fff;border:1px solid rgba(255,255,255,.25)"><i class="bi bi-chat-dots me-1"></i><?=$unread?></a><?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <div class="col-md-9">
    <div class="row g-3 mb-3">
      <div class="col-6 col-md-3 fu"><div class="stat-card sc-green"><div class="s-bg"><i class="bi bi-briefcase"></i></div><div class="s-icon"><i class="bi bi-briefcase-fill"></i></div><div class="s-num"><?=$open_jobs?></div><div class="s-lbl">Vị trí mở</div><a href="<?=BASE_PATH?>/modules/internships/browse.php" class="s-link">Tìm ngay <i class="bi bi-arrow-right"></i></a></div></div>
      <div class="col-6 col-md-3 fu1"><div class="stat-card sc-sage"><div class="s-bg"><i class="bi bi-clipboard"></i></div><div class="s-icon"><i class="bi bi-clipboard-check-fill"></i></div><div class="s-num"><?=$app_cnt?></div><div class="s-lbl">Đã ứng tuyển</div><a href="<?=BASE_PATH?>/modules/applications/my_applications.php" class="s-link">Xem <i class="bi bi-arrow-right"></i></a></div></div>
      <div class="col-6 col-md-3 fu2"><div class="stat-card sc-warm"><div class="s-bg"><i class="bi bi-hourglass"></i></div><div class="s-icon"><i class="bi bi-hourglass-split"></i></div><div class="s-num"><?=$pend_cnt?></div><div class="s-lbl">Chờ duyệt</div></div></div>
      <div class="col-6 col-md-3 fu3"><div class="stat-card sc-teal"><div class="s-bg"><i class="bi bi-chat"></i></div><div class="s-icon"><i class="bi bi-chat-dots-fill"></i></div><div class="s-num"><?=$unread?></div><div class="s-lbl">Tin nhắn mới</div><a href="<?=BASE_PATH?>/modules/messages/inbox.php" class="s-link">Xem <i class="bi bi-arrow-right"></i></a></div></div>
    </div>
    <!-- Quick actions -->
    <div class="card fu4"><div class="card-body">
      <h6 class="fw7 mb-2" style="color:var(--ds)">Thao tác nhanh</h6>
      <div class="row g-2">
        <div class="col-6"><a href="<?=BASE_PATH?>/modules/internships/browse.php" class="qa"><div class="qa-icon" style="background:rgba(93,123,111,.1)"><i class="bi bi-search" style="color:var(--ds)"></i></div><div><div class="qa-title">Tìm việc thực tập</div><div class="qa-sub"><?=$open_jobs?> vị trí đang mở</div></div></a></div>
        <div class="col-6"><a href="<?=BASE_PATH?>/modules/applications/my_applications.php" class="qa"><div class="qa-icon" style="background:rgba(196,154,108,.1)"><i class="bi bi-clipboard-check-fill" style="color:#a07040"></i></div><div><div class="qa-title">Đơn ứng tuyển</div><div class="qa-sub"><?=$app_cnt?> đơn đã nộp</div></div></a></div>
        <?php if($internship): ?>
        <div class="col-6"><a href="<?=BASE_PATH?>/modules/registrations/my_internship.php" class="qa"><div class="qa-icon" style="background:rgba(74,158,106,.1)"><i class="bi bi-briefcase-fill" style="color:#2d6a40"></i></div><div><div class="qa-title">Thực tập của tôi</div><div class="qa-sub"><?=$internship['status']==='active'?'Đang thực tập':'Hoàn thành'?></div></div></a></div>
        <div class="col-6"><a href="<?=BASE_PATH?>/modules/reports/submit.php" class="qa"><div class="qa-icon" style="background:rgba(74,138,150,.1)"><i class="bi bi-file-earmark-arrow-up" style="color:#3a8a96"></i></div><div><div class="qa-title">Nộp báo cáo</div><div class="qa-sub"><?=$internship['rep_status']?htmlspecialchars($internship['rep_status']):'Chưa nộp'?></div></div></a></div>
        <?php endif; ?>
      </div>
    </div></div>
  </div>
</div>

<!-- Thực tập hiện tại -->
<?php if($internship): ?>
<div class="card mb-4 fu2" style="border-left:4px solid var(--ds)"><div class="card-body">
  <h6 class="fw7 mb-3"><i class="bi bi-briefcase-fill me-2" style="color:var(--ds)"></i>Kỳ thực tập hiện tại</h6>
  <?php $clogo = ($internship['logo']??'') ? UPLOAD_URL.'/'.$internship['logo'] : 'https://ui-avatars.com/api/?name='.urlencode($internship['company_name']).'&background=A4C3A2&color=2A3F38&size=60'; ?>
  <div class="d-flex align-items-center gap-3 mb-3">
    <img src="<?=$clogo?>" style="width:44px;height:44px;border-radius:11px;object-fit:cover">
    <div><div class="fw7"><?=htmlspecialchars($internship['title'])?></div><div class="small text-muted"><?=htmlspecialchars($internship['company_name'])?></div></div>
    <div class="ms-auto">
      <?php if($internship['overall_score']): ?><div class="text-center"><div style="font-size:1.8rem;font-weight:800;color:var(--ds);line-height:1"><?=$internship['overall_score']?></div><div class="small text-muted">/10</div></div><?php endif; ?>
    </div>
  </div>
  <div class="row g-2 small">
    <?php if($internship['lname']): ?><div class="col-md-4"><div class="text-muted">GVHD</div><div class="fw7"><?=htmlspecialchars($internship['lname'])?></div></div><?php endif; ?>
    <div class="col-md-4"><div class="text-muted">Trạng thái</div><span class="badge" style="<?=$internship['status']==='completed'?'background:rgba(74,158,106,.12);color:#2d6a40':'background:rgba(93,123,111,.12);color:var(--ds)'?>"><?=$internship['status']==='completed'?'🏆 Hoàn thành':'🚀 Đang thực tập'?></span></div>
    <?php if($internship['start_date']): ?><div class="col-md-4"><div class="text-muted">Thời gian</div><div class="fw7"><?=date('d/m/Y',strtotime($internship['start_date']))?> → <?=$internship['end_date']?date('d/m/Y',strtotime($internship['end_date'])):'?'?></div></div><?php endif; ?>
  </div>
  <div class="d-flex gap-2 mt-3 flex-wrap">
    <a href="<?=BASE_PATH?>/modules/registrations/my_internship.php" class="btn btn-primary btn-sm"><i class="bi bi-eye me-1"></i>Chi tiết</a>
    <a href="<?=BASE_PATH?>/modules/messages/inbox.php" class="btn btn-secondary btn-sm"><i class="bi bi-chat-dots me-1"></i>Nhắn tin</a>
    <?php if($internship['status']==='active'): ?><a href="<?=BASE_PATH?>/modules/reports/submit.php" class="btn btn-secondary btn-sm"><i class="bi bi-file-earmark-arrow-up me-1"></i>Nộp báo cáo</a><?php endif; ?>
  </div>
</div></div>
<?php endif; ?>

<!-- Đơn ứng tuyển gần đây -->
<?php if(!empty($rapps)): ?>
<div class="card fu3 tc"><div class="card-header d-flex justify-content-between">
  <span><i class="bi bi-clock-history me-2"></i>Đơn ứng tuyển gần đây</span>
  <a href="<?=BASE_PATH?>/modules/applications/my_applications.php" class="btn btn-primary btn-sm">Tất cả</a>
</div><div class="card-body p-0"><table class="table mb-0">
  <thead><tr><th>Vị trí</th><th>Doanh nghiệp</th><th>Trạng thái</th><th>Ngày nộp</th></tr></thead>
  <tbody>
  <?php foreach($rapps as $a):
    [$al,$ab,$ac] = appStatusLabel($a['status']);
  ?>
  <tr>
    <td class="fw7 small"><?=htmlspecialchars($a['title'])?></td>
    <td class="small text-muted"><?=htmlspecialchars($a['company_name'])?></td>
    <td><span class="badge" style="background:<?=$ab?>;color:<?=$ac?>;font-size:.7rem"><?=$al?></span></td>
    <td class="small text-muted"><?=date('d/m/Y',strtotime($a['applied_at']))?></td>
  </tr>
  <?php endforeach; ?>
  </tbody>
</table></div></div>
<?php endif; ?>
<?php include '../includes/footer.php'; ?>
