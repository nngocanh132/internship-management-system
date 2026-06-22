<?php // View: applications/company_candidates — nhận $candidates, $my_jobs, $job_filter, $cnt từ controller ?>
<?php include BASE_PATH_FS . 'includes/header.php'; ?>
<div class="ph fu">
  <div>
    <h4><i class="bi bi-people-fill me-2"></i>Danh sách Ứng viên</h4>
    <div class="ph-sub">Tổng: <?=count($candidates)?> ứng viên</div>
  </div>
  <a href="../internships/my_jobs.php" class="btn btn-secondary"><i class="bi bi-briefcase me-1"></i>Vị trí TT</a>
</div>
<?php showFlash(); ?>

<div class="card mb-3 fu1"><div class="card-body py-2">
  <form method="GET" class="d-flex gap-2 align-items-center flex-wrap">
    <label class="form-label mb-0 fw7" style="white-space:nowrap">Lọc theo vị trí:</label>
    <select name="job" class="form-select" style="max-width:300px" onchange="this.form.submit()">
      <option value="">— Tất cả vị trí —</option>
      <?php foreach($my_jobs as $j): ?>
      <option value="<?=$j['internship_id']?>" <?=$job_filter==$j['internship_id']?'selected':''?>><?=htmlspecialchars($j['title'])?></option>
      <?php endforeach; ?>
    </select>
    <?php if($job_filter): ?><a href="company_candidates.php" class="btn btn-secondary btn-sm">Xóa lọc</a><?php endif; ?>
  </form>
</div></div>

<div class="row g-2 mb-3 fu2">
  <?php $summary=[
    ['Chờ trường duyệt',$cnt['pending_admin']??0,'rgba(196,154,108,.12)','#a07040'],
    ['Trường đã duyệt',$cnt['approved_admin']??0,'rgba(74,138,150,.12)','#3a8a96'],
    ['Bạn đã chấp nhận',($cnt['approved_company']??0)+($cnt['interview_passed']??0),'rgba(74,158,106,.12)','#2d6a40'],
    ['Đang thực tập',$cnt['internship_active']??0,'rgba(93,123,111,.15)','var(--ds)'],
  ]; foreach($summary as [$lbl,$n,$bg,$c]): ?>
  <div class="col-6 col-md-3"><div class="card p-2 text-center" style="background:<?=$bg?>;border:none">
    <div style="font-size:1.5rem;font-weight:800;color:<?=$c?>"><?=$n?></div>
    <div class="small" style="color:<?=$c?>"><?=$lbl?></div>
  </div></div>
  <?php endforeach; ?>
</div>

<?php if(empty($candidates)): ?>
<div class="card text-center py-5 fu3"><div class="card-body">
  <i class="bi bi-people" style="font-size:3rem;color:var(--tl)"></i>
  <h5 class="mt-3 fw7">Chưa có ứng viên</h5>
  <p class="text-muted">Ứng viên sẽ xuất hiện khi sinh viên nộp đơn và trường duyệt.</p>
</div></div>
<?php else: ?>
<div class="row g-3 fu3">
<?php foreach($candidates as $i=>$a):
  [$lbl,$bg,$c] = appStatusLabel($a['status']);
  $av = ($a['s_av']??'') ? UPLOAD_URL.'/'.$a['s_av'] : 'https://ui-avatars.com/api/?name='.urlencode($a['full_name']).'&background=5D7B6F&color=fff&size=60';
  $can_approve   = ($a['status']==='approved_admin');
  $can_message   = in_array($a['status'],['approved_company','interview_passed','internship_active']);
  $can_pass_fail = ($a['status']==='approved_company');
  $can_start     = ($a['status']==='interview_passed' && !$a['registration_id']);
  $can_delete    = !in_array($a['status'],['internship_active','internship_completed']);
?>
<div class="col-md-6" style="animation:fadeUp .32s <?=$i*.04?>s ease both">
  <div class="card h-100" style="border:1.5px solid rgba(164,195,162,.25)">
    <div class="card-body">
      <div class="d-flex align-items-center gap-3 mb-3">
        <img src="<?=$av?>" style="width:50px;height:50px;border-radius:50%;object-fit:cover;flex-shrink:0">
        <div style="flex:1">
          <div class="fw7"><?=htmlspecialchars($a['full_name'])?></div>
          <div class="small text-muted"><?=htmlspecialchars($a['email'])?></div>
          <div class="d-flex gap-1 mt-1 flex-wrap">
            <span class="badge bg-primary" style="font-size:.7rem">GPA: <?=$a['gpa']??'—'?></span>
            <?php if($a['student_code']??''): ?><span class="badge bg-secondary" style="font-size:.7rem"><?=htmlspecialchars($a['student_code'])?></span><?php endif; ?>
            <?php if($a['major']??''): ?><span class="badge" style="background:rgba(93,123,111,.1);color:var(--ds);font-size:.7rem"><?=htmlspecialchars($a['major'])?></span><?php endif; ?>
          </div>
        </div>
        <span class="badge" style="background:<?=$bg?>;color:<?=$c?>;white-space:nowrap;padding:5px 10px"><?=$lbl?></span>
      </div>
      <div class="small text-muted mb-2">
        <i class="bi bi-briefcase me-1"></i><?=htmlspecialchars($a['job_title'])?>
        <span class="ms-2"><i class="bi bi-calendar3 me-1"></i><?=date('d/m/Y',strtotime($a['applied_at']))?></span>
      </div>
      <?php if($a['interview_date']): ?>
      <div class="mb-2 p-2" style="background:rgba(74,138,150,.08);border-radius:8px;font-size:.8rem">
        <i class="bi bi-camera-video me-2" style="color:#3a8a96"></i>
        <strong>Phỏng vấn:</strong> <?=date('d/m/Y H:i',strtotime($a['interview_date']))?>
        <?php if($a['iv_result']==='passed'): ?><span class="badge ms-1" style="background:rgba(74,158,106,.2);color:#2d6a40">🎉 Đậu</span>
        <?php elseif($a['iv_result']==='failed'): ?><span class="badge ms-1" style="background:rgba(192,96,80,.15);color:#9a3030">Rớt</span><?php endif; ?>
      </div>
      <?php endif; ?>
      <?php if($a['cv_file']??''): ?>
      <div class="mb-2"><a href="<?=UPLOAD_URL.'/'.$a['cv_file']?>" target="_blank" class="btn btn-secondary btn-sm w-100"><i class="bi bi-file-earmark-pdf me-1"></i>Xem CV</a></div>
      <?php endif; ?>
      <div class="d-flex gap-2 flex-wrap mt-2">
        <?php if($can_approve): ?>
        <a href="?approve=<?=$a['application_id']?><?=$job_filter?"&job=$job_filter":''?>" class="btn btn-success btn-sm flex-fill" onclick="return confirm('Chấp nhận ứng viên này?')"><i class="bi bi-person-check me-1"></i>Chấp nhận</a>
        <a href="?reject=<?=$a['application_id']?><?=$job_filter?"&job=$job_filter":''?>" class="btn btn-danger btn-sm" onclick="return confirm('Từ chối ứng viên?')"><i class="bi bi-x-lg"></i></a>
        <?php endif; ?>
        <?php if($can_pass_fail): ?>
        <a href="?pass_interview=<?=$a['application_id']?><?=$job_filter?"&job=$job_filter":''?>" class="btn btn-success btn-sm flex-fill" onclick="return confirm('Xác nhận sinh viên ĐẬU phỏng vấn?')"><i class="bi bi-check-circle-fill me-1"></i>Đậu PV</a>
        <a href="?fail_interview=<?=$a['application_id']?><?=$job_filter?"&job=$job_filter":''?>" class="btn btn-danger btn-sm" onclick="return confirm('Xác nhận sinh viên RỚT phỏng vấn?')"><i class="bi bi-x-circle-fill me-1"></i>Rớt PV</a>
        <?php endif; ?>
        <?php if($can_message): ?>
        <a href="<?=BASE_PATH?>/messages/chat.php?student_id=<?=$a['sp_sid']?>&app_id=<?=$a['application_id']?>" class="btn btn-primary btn-sm <?=!$can_approve?'flex-fill':''?>">
          <i class="bi bi-chat-dots-fill me-1"></i>Nhắn tin<?=$a['status']==='approved_company'?' / Hẹn PV':''?>
        </a>
        <?php endif; ?>
        <?php if($can_start): ?>
        <a href="?start_internship=<?=$a['application_id']?><?=$job_filter?"&job=$job_filter":''?>" class="btn btn-success btn-sm flex-fill" onclick="return confirm('Xác nhận sinh viên bắt đầu thực tập?')" style="background:linear-gradient(135deg,#2d7a50,#1a4a2a)">
          <i class="bi bi-play-circle-fill me-1"></i>Bắt đầu Thực tập
        </a>
        <?php endif; ?>
        <?php if($a['status']==='internship_active'||$a['registration_id']): ?>
        <span class="btn btn-sm" style="background:rgba(74,158,106,.12);color:#2d6a40;flex:1;text-align:center;cursor:default">
          <i class="bi bi-check-circle-fill me-1"></i>Đang thực tập
        </span>
        <?php endif; ?>
        <?php if($can_delete): ?>
        <a href="?delete=<?=$a['application_id']?><?=$job_filter?"&job=$job_filter":''?>" class="btn btn-danger btn-sm" onclick="return confirm('Xóa đơn này?')" title="Xóa"><i class="bi bi-trash"></i></a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>
<?php include BASE_PATH_FS . 'includes/footer.php'; ?>
