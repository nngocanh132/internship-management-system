<?php // View: dashboard/admin — nhận $stats, $pending_apps, $iv_passed, $need_assign ?>
<?php include BASE_PATH_FS . 'includes/header.php'; ?>
<div class="fu mb-4">
  <h3 style="font-family:'Plus Jakarta Sans',sans-serif;font-weight:800;font-size:1.5rem;color:var(--td)">Tổng quan Quản trị 🎓</h3>
  <p class="small" style="color:var(--tl)"><?=date('d/m/Y H:i')?></p>
</div>

<div class="row g-3 mb-4">
  <?php $cards=[
    ['Sinh viên',$stats['students'],'bi-people-fill','sc-green',BASE_PATH.'/users/list.php?role=student'],
    ['Doanh nghiệp',$stats['companies'],'bi-building-fill','sc-mint',BASE_PATH.'/company_profiles/list.php'],
    ['Vị trí mở',$stats['jobs'],'bi-briefcase-fill','sc-sage',BASE_PATH.'/internships/list.php'],
    ['Chờ xét duyệt',$stats['pending'],'bi-hourglass-split','sc-warm',BASE_PATH.'/applications/list.php?status=pending_admin'],
    ['Đang thực tập',$stats['active'],'bi-person-check-fill','sc-teal',BASE_PATH.'/registrations/list.php'],
    ['Báo cáo chờ',$stats['reports'],'bi-file-earmark-text-fill','sc-red',BASE_PATH.'/reports/list.php'],
  ]; foreach($cards as $i=>[$lbl,$val,$ico,$cls,$url]): ?>
  <div class="col-6 col-md-2" style="animation:fadeUp .32s <?=$i*.05?>s ease both">
    <div class="stat-card <?=$cls?>">
      <div class="s-bg"><i class="bi <?=$ico?>"></i></div>
      <div class="s-icon"><i class="bi <?=$ico?>"></i></div>
      <div class="s-num"><?=$val?></div>
      <div class="s-lbl"><?=$lbl?></div>
      <a href="<?=$url?>" class="s-link">Xem <i class="bi bi-arrow-right"></i></a>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<?php if(!empty($iv_passed)||!empty($need_assign)): ?>
<div class="row g-3 mb-4 fu">
  <?php if(!empty($iv_passed)): ?>
  <div class="col-md-6">
    <div class="card" style="border-left:4px solid #2d6a40"><div class="card-body">
      <h6 class="fw7 mb-2" style="color:#2d6a40"><i class="bi bi-trophy-fill me-2"></i>Đậu phỏng vấn — Cần phân công GVHD (<?=count($iv_passed)?>)</h6>
      <?php foreach($iv_passed as $iv): ?>
      <div class="d-flex justify-content-between align-items-center mb-2">
        <div class="small"><div class="fw7"><?=htmlspecialchars($iv['full_name'])?> (<?=htmlspecialchars($iv['student_code']??'')?>)</div><div class="text-muted"><?=htmlspecialchars($iv['title'])?> @ <?=htmlspecialchars($iv['company_name'])?></div></div>
        <a href="<?=BASE_PATH?>/registrations/assign.php" class="btn btn-primary btn-sm"><i class="bi bi-person-check me-1"></i>Phân công GV</a>
      </div>
      <?php endforeach; ?>
    </div></div>
  </div>
  <?php endif; ?>
  <?php if(!empty($need_assign)): ?>
  <div class="col-md-6">
    <div class="card" style="border-left:4px solid #3a8a96"><div class="card-body">
      <h6 class="fw7 mb-2" style="color:#3a8a96"><i class="bi bi-person-workspace me-2"></i>Đang TT chưa có GVHD (<?=count($need_assign)?>)</h6>
      <?php foreach($need_assign as $n): ?>
      <div class="d-flex justify-content-between align-items-center mb-2">
        <div class="small"><div class="fw7"><?=htmlspecialchars($n['full_name'])?></div><div class="text-muted"><?=htmlspecialchars($n['title'])?></div></div>
        <a href="<?=BASE_PATH?>/registrations/assign.php" class="btn btn-primary btn-sm"><i class="bi bi-person-plus me-1"></i>Phân công</a>
      </div>
      <?php endforeach; ?>
    </div></div>
  </div>
  <?php endif; ?>
</div>
<?php endif; ?>

<div class="row g-4">
  <div class="col-lg-8">
    <div class="card tc fu">
      <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-hourglass-split me-2" style="color:var(--ds)"></i>Đơn chờ xét duyệt</span>
        <a href="<?=BASE_PATH?>/applications/list.php" class="btn btn-primary btn-sm">Xem tất cả</a>
      </div>
      <div class="card-body p-0"><table class="table mb-0">
        <thead><tr><th>Sinh viên</th><th>Vị trí / DN</th><th>GPA</th><th>Ngành</th><th>Ngày nộp</th><th>Thao tác</th></tr></thead>
        <tbody>
        <?php if(empty($pending_apps)): ?>
          <tr><td colspan="6" class="text-center py-5 text-muted"><i class="bi bi-inbox fs-1 d-block mb-2 opacity-25"></i>Không có đơn nào chờ duyệt</td></tr>
        <?php else: foreach($pending_apps as $a): ?>
        <tr>
          <td><div class="fw7 small"><?=htmlspecialchars($a['full_name'])?></div><div class="small text-muted"><?=htmlspecialchars($a['student_code']??'')?></div></td>
          <td><div class="fw7 small"><?=htmlspecialchars($a['title'])?></div><div class="small text-muted"><?=htmlspecialchars($a['company_name'])?></div></td>
          <td><span class="badge bg-primary"><?=$a['gpa']?></span></td>
          <td class="small text-muted"><?=htmlspecialchars($a['major']??'—')?></td>
          <td class="small text-muted"><?=date('d/m/Y',strtotime($a['applied_at']))?></td>
          <td><a href="<?=BASE_PATH?>/applications/review.php?id=<?=$a['application_id']?>" class="btn btn-primary btn-sm">Xét duyệt</a></td>
        </tr>
        <?php endforeach; endif; ?>
        </tbody>
      </table></div>
    </div>
  </div>
  <div class="col-lg-4">
    <div class="card fu1"><div class="card-body">
      <h6 class="fw7 mb-3"><i class="bi bi-lightning-fill me-2" style="color:#a07040"></i>Thao tác nhanh</h6>
      <div class="d-grid gap-2">
        <a href="<?=BASE_PATH?>/applications/list.php?status=pending_admin" class="qa"><div class="qa-icon" style="background:rgba(196,154,108,.12)"><i class="bi bi-clipboard-check-fill" style="color:#a07040"></i></div><div><div class="qa-title">Xét duyệt đơn</div><div class="qa-sub"><?=$stats['pending']?> đơn chờ</div></div></a>
        <a href="<?=BASE_PATH?>/registrations/list.php" class="qa"><div class="qa-icon" style="background:rgba(93,123,111,.12)"><i class="bi bi-journal-richtext" style="color:var(--ds)"></i></div><div><div class="qa-title">Quản lý TT</div><div class="qa-sub"><?=$stats['active']?> đang thực tập</div></div></a>
        <a href="<?=BASE_PATH?>/reports/list.php" class="qa"><div class="qa-icon" style="background:rgba(74,158,106,.12)"><i class="bi bi-file-earmark-check-fill" style="color:#2d6a40"></i></div><div><div class="qa-title">Duyệt báo cáo</div><div class="qa-sub"><?=$stats['reports']?> chờ duyệt</div></div></a>
        <a href="<?=BASE_PATH?>/users/create_lecturer.php" class="qa"><div class="qa-icon" style="background:rgba(74,138,150,.12)"><i class="bi bi-person-plus-fill" style="color:#3a8a96"></i></div><div><div class="qa-title">Thêm Giảng viên</div><div class="qa-sub">Tạo tài khoản GV</div></div></a>
      </div>
    </div></div>
  </div>
</div>
<?php include BASE_PATH_FS . 'includes/footer.php'; ?>
