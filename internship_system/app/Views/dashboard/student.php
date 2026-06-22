<?php // View: dashboard/student — nhận $sv,$sid,$open_jobs,$app_cnt,$pend_cnt,$unread,$internship,$rapps,$av ?>
<?php include BASE_PATH_FS . 'includes/header.php'; ?>

<?php if(!($sv['is_profile_completed']??0)): ?>
<div class="alert alert-warning fu">
  <i class="bi bi-exclamation-triangle-fill me-2"></i>
  <strong>Hồ sơ chưa hoàn thiện!</strong> Điền đủ thông tin để ứng tuyển thực tập.
  <a href="<?=BASE_PATH?>/student_profiles/edit.php" class="btn btn-warning btn-sm ms-2">Hoàn thiện ngay</a>
</div>
<?php endif; ?>

<div class="row g-4 mb-4">
  <div class="col-md-3 fu">
    <div class="card h-100 text-center" style="background:linear-gradient(175deg,var(--ds3),var(--ds2));border:none">
      <div class="card-body text-white py-4">
        <img src="<?=$av?>" style="width:80px;height:80px;border-radius:50%;object-fit:cover;border:3px solid rgba(255,255,255,.3);margin-bottom:10px">
        <h5 class="fw8 mb-0"><?=htmlspecialchars($sv['full_name']??$_SESSION['full_name']??'')?></h5>
        <div style="opacity:.8;font-size:.82rem;margin-top:3px"><?=htmlspecialchars($sv['student_code']??'—')?></div>
        <div style="opacity:.75;font-size:.8rem"><?=htmlspecialchars($sv['major']??'—')?></div>
        <?php if($sv['gpa']??0): ?><div class="mt-2"><span style="background:rgba(255,255,255,.18);padding:4px 12px;border-radius:20px;font-size:.82rem;font-weight:700">GPA: <?=$sv['gpa']?>/4.0</span></div><?php endif; ?>
        <div class="mt-3 d-flex gap-2 justify-content-center flex-wrap">
          <a href="<?=BASE_PATH?>/student_profiles/edit.php" class="btn btn-sm" style="background:rgba(255,255,255,.18);color:#fff;border:1px solid rgba(255,255,255,.25)"><i class="bi bi-pencil me-1"></i>Hồ sơ</a>
          <?php if($unread>0): ?><a href="<?=BASE_PATH?>/messages/inbox.php" class="btn btn-sm" style="background:rgba(255,255,255,.18);color:#fff;border:1px solid rgba(255,255,255,.25)"><i class="bi bi-chat-dots me-1"></i><?=$unread?></a><?php endif; ?>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-9">
    <div class="row g-3 mb-3">
      <div class="col-6 col-md-3 fu"><div class="stat-card sc-green"><div class="s-bg"><i class="bi bi-briefcase"></i></div><div class="s-icon"><i class="bi bi-briefcase-fill"></i></div><div class="s-num"><?=$open_jobs?></div><div class="s-lbl">Vị trí mở</div><a href="<?=BASE_PATH?>/internships/browse.php" class="s-link">Tìm ngay <i class="bi bi-arrow-right"></i></a></div></div>
      <div class="col-6 col-md-3 fu1"><div class="stat-card sc-sage"><div class="s-bg"><i class="bi bi-clipboard"></i></div><div class="s-icon"><i class="bi bi-clipboard-check-fill"></i></div><div class="s-num"><?=$app_cnt?></div><div class="s-lbl">Đã ứng tuyển</div><a href="<?=BASE_PATH?>/applications/my_applications.php" class="s-link">Xem <i class="bi bi-arrow-right"></i></a></div></div>
      <div class="col-6 col-md-3 fu2"><div class="stat-card sc-warm"><div class="s-bg"><i class="bi bi-hourglass"></i></div><div class="s-icon"><i class="bi bi-hourglass-split"></i></div><div class="s-num"><?=$pend_cnt?></div><div class="s-lbl">Chờ duyệt</div></div></div>
      <div class="col-6 col-md-3 fu3"><div class="stat-card sc-teal"><div class="s-bg"><i class="bi bi-chat"></i></div><div class="s-icon"><i class="bi bi-chat-dots-fill"></i></div><div class="s-num"><?=$unread?></div><div class="s-lbl">Tin nhắn mới</div><a href="<?=BASE_PATH?>/messages/inbox.php" class="s-link">Xem <i class="bi bi-arrow-right"></i></a></div></div>
    </div>
    <div class="card fu4"><div class="card-body">
      <h6 class="fw7 mb-2" style="color:var(--ds)">Thao tác nhanh</h6>
      <div class="row g-2">
        <?php $is_completed=($internship['status']??'')==='completed'; ?>
        <?php if(!$is_completed): ?>
        <div class="col-6"><a href="<?=BASE_PATH?>/internships/browse.php" class="qa"><div class="qa-icon" style="background:rgba(93,123,111,.1)"><i class="bi bi-search" style="color:var(--ds)"></i></div><div><div class="qa-title">Tìm việc thực tập</div><div class="qa-sub"><?=$open_jobs?> vị trí đang mở</div></div></a></div>
        <?php endif; ?>
        <div class="col-6"><a href="<?=BASE_PATH?>/applications/my_applications.php" class="qa"><div class="qa-icon" style="background:rgba(196,154,108,.1)"><i class="bi bi-clipboard-check-fill" style="color:#a07040"></i></div><div><div class="qa-title">Đơn ứng tuyển</div><div class="qa-sub"><?=$app_cnt?> đơn đã nộp</div></div></a></div>
        <?php if($internship): ?>
        <div class="col-6"><a href="<?=BASE_PATH?>/registrations/my_internship.php" class="qa"><div class="qa-icon" style="background:rgba(74,158,106,.1)"><i class="bi bi-briefcase-fill" style="color:#2d6a40"></i></div><div><div class="qa-title"><?=$is_completed?'Kết quả thực tập':'Thực tập của tôi'?></div><div class="qa-sub"><?=$is_completed?'🏆 Đã hoàn thành':'🚀 Đang thực tập'?></div></div></a></div>
        <?php if(!$is_completed): ?>
        <div class="col-6"><a href="<?=BASE_PATH?>/reports/submit.php" class="qa"><div class="qa-icon" style="background:rgba(74,138,150,.1)"><i class="bi bi-file-earmark-arrow-up" style="color:#3a8a96"></i></div><div><div class="qa-title">Nộp báo cáo</div><div class="qa-sub"><?=$internship['rep_status']?htmlspecialchars($internship['rep_status']):'Chưa nộp'?></div></div></a></div>
        <?php endif; ?>
        <?php endif; ?>
      </div>
    </div></div>
  </div>
</div>

<?php if($internship): ?>
<div class="card mb-4 fu2" style="border-left:4px solid <?=$internship['status']==='completed'?'#3d8a58':'var(--ds)'?>"><div class="card-body">
  <h6 class="fw7 mb-3"><i class="bi bi-<?=$internship['status']==='completed'?'trophy-fill':'briefcase-fill'?> me-2" style="color:<?=$internship['status']==='completed'?'#3d8a58':'var(--ds)'?>"></i><?=$internship['status']==='completed'?'Kỳ thực tập đã hoàn thành':'Kỳ thực tập hiện tại'?></h6>
  <?php $clogo=($internship['logo']??'')?UPLOAD_URL.'/'.$internship['logo']:'https://ui-avatars.com/api/?name='.urlencode($internship['company_name']).'&background=A4C3A2&color=2A3F38&size=60'; ?>
  <div class="d-flex align-items-center gap-3 mb-3">
    <img src="<?=$clogo?>" style="width:44px;height:44px;border-radius:11px;object-fit:cover">
    <div><div class="fw7"><?=htmlspecialchars($internship['title'])?></div><div class="small text-muted"><?=htmlspecialchars($internship['company_name'])?></div></div>
    <div class="ms-auto"><?php if($internship['overall_score']): ?><div class="text-center"><div style="font-size:1.8rem;font-weight:800;color:var(--ds);line-height:1"><?=$internship['overall_score']?></div><div class="small text-muted">/10</div></div><?php endif; ?></div>
  </div>
  <div class="row g-2 small">
    <?php if($internship['lname']): ?><div class="col-md-4"><div class="text-muted">GVHD</div><div class="fw7"><?=htmlspecialchars($internship['lname'])?></div></div><?php endif; ?>
    <div class="col-md-4"><div class="text-muted">Trạng thái</div><span class="badge" style="<?=$internship['status']==='completed'?'background:rgba(74,158,106,.12);color:#2d6a40':'background:rgba(93,123,111,.12);color:var(--ds)'?>"><?=$internship['status']==='completed'?'🏆 Hoàn thành':'🚀 Đang thực tập'?></span></div>
    <?php if($internship['start_date']): ?><div class="col-md-4"><div class="text-muted">Thời gian</div><div class="fw7"><?=date('d/m/Y',strtotime($internship['start_date']))?> → <?=$internship['end_date']?date('d/m/Y',strtotime($internship['end_date'])):'?'?></div></div><?php endif; ?>
  </div>
  <div class="d-flex gap-2 mt-3 flex-wrap">
    <a href="<?=BASE_PATH?>/registrations/my_internship.php" class="btn btn-primary btn-sm"><i class="bi bi-eye me-1"></i>Chi tiết</a>
    <a href="<?=BASE_PATH?>/messages/inbox.php" class="btn btn-secondary btn-sm"><i class="bi bi-chat-dots me-1"></i>Nhắn tin</a>
    <?php if($internship['status']==='active'): ?><a href="<?=BASE_PATH?>/reports/submit.php" class="btn btn-secondary btn-sm"><i class="bi bi-file-earmark-arrow-up me-1"></i>Nộp báo cáo</a><?php endif; ?>
  </div>
</div></div>
<?php endif; ?>

<?php if(!empty($rapps)): ?>
<div class="card fu3 tc"><div class="card-header d-flex justify-content-between">
  <span><i class="bi bi-clock-history me-2"></i>Đơn ứng tuyển gần đây</span>
  <a href="<?=BASE_PATH?>/applications/my_applications.php" class="btn btn-primary btn-sm">Tất cả</a>
</div><div class="card-body p-0"><table class="table mb-0">
  <thead><tr><th>Vị trí</th><th>Doanh nghiệp</th><th>Trạng thái</th><th>Ngày nộp</th></tr></thead>
  <tbody>
  <?php foreach($rapps as $a): [$al,$ab,$ac]=appStatusLabel($a['status']); ?>
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
<?php include BASE_PATH_FS . 'includes/footer.php'; ?>
