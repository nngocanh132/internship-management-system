<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';
requireRole('admin');

// Kiểm tra DB đã migrate chưa
$db_ok = $conn->query("SHOW TABLES LIKE 'applications'") && $conn->query("SHOW TABLES LIKE 'applications'")->num_rows > 0;

if(!$db_ok){
    echo '<!DOCTYPE html><html lang="vi"><head><meta charset="UTF-8"><title>Setup cần thiết</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>body{background:#eef5f2;font-family:Inter,sans-serif;display:flex;align-items:center;justify-content:center;min-height:100vh}</style></head><body>
    <div style="background:#fff;border-radius:20px;padding:48px;text-align:center;max-width:480px;box-shadow:0 8px 32px rgba(93,123,111,.12)">
      <div style="font-size:3rem;margin-bottom:14px">🛠️</div>
      <h4 style="font-weight:800;color:#1A2E28;margin-bottom:8px">Database chưa được cài đặt</h4>
      <p class="text-muted mb-4">Cần chạy setup để tạo các bảng dữ liệu.</p>
      <a href="/internship-management-system/setup.php" class="btn btn-success btn-lg px-5">
        <i class="bi bi-database me-2"></i>Chạy Setup ngay
      </a>
    </div></body></html>';
    exit();
}

$stats=[
  'students'  => safeCount($conn,"SELECT COUNT(*) c FROM users WHERE role='student'"),
  'companies' => safeCount($conn,"SELECT COUNT(*) c FROM users WHERE role='company'"),
  'jobs'      => safeCount($conn,"SELECT COUNT(*) c FROM internships WHERE status='open'"),
  'pending'   => safeCount($conn,"SELECT COUNT(*) c FROM applications WHERE status='pending_admin'"),
  'active'    => safeCount($conn,"SELECT COUNT(*) c FROM internship_registrations WHERE status='active'"),
  'reports'   => safeCount($conn,"SELECT COUNT(*) c FROM internship_reports WHERE status='pending'"),
];

$pending_apps = safeQuery($conn,"SELECT a.*,sp.full_name,sp.student_code,sp.gpa,sp.major,i.title,cp.company_name
  FROM applications a JOIN student_profiles sp ON a.student_id=sp.student_id
  JOIN internships i ON a.internship_id=i.internship_id JOIN company_profiles cp ON i.company_id=cp.company_id
  WHERE a.status='pending_admin' ORDER BY a.applied_at DESC LIMIT 8");

$iv_passed = safeQuery($conn,"SELECT a.*,sp.full_name,sp.student_code,i.title,cp.company_name
  FROM applications a JOIN student_profiles sp ON a.student_id=sp.student_id
  JOIN internships i ON a.internship_id=i.internship_id JOIN company_profiles cp ON i.company_id=cp.company_id
  WHERE a.status='interview_passed'
  AND NOT EXISTS (SELECT 1 FROM internship_registrations ir WHERE ir.student_id=sp.student_id AND ir.internship_id=i.internship_id)
  ORDER BY a.applied_at DESC LIMIT 5");

$need_assign = safeQuery($conn,"SELECT ir.*,sp.full_name,sp.student_code,i.title,cp.company_name
  FROM internship_registrations ir JOIN student_profiles sp ON ir.student_id=sp.student_id
  JOIN internships i ON ir.internship_id=i.internship_id JOIN company_profiles cp ON ir.company_id=cp.company_id
  WHERE ir.lecturer_id IS NULL AND ir.status='active'
  ORDER BY ir.created_at DESC LIMIT 5");
?>
<?php include '../includes/header.php'; ?>
<div class="fu mb-4">
  <h3 style="font-family:'Plus Jakarta Sans',sans-serif;font-weight:800;font-size:1.5rem;color:var(--td)">Tổng quan Quản trị 🎓</h3>
  <p class="small" style="color:var(--tl)"><?=date('d/m/Y H:i')?></p>
</div>

<div class="row g-3 mb-4">
  <?php $cards=[
    ['Sinh viên',$stats['students'],'bi-people-fill','sc-green',BASE_PATH.'/modules/users/list.php?role=student'],
    ['Doanh nghiệp',$stats['companies'],'bi-building-fill','sc-mint',BASE_PATH.'/modules/company_profiles/list.php'],
    ['Vị trí mở',$stats['jobs'],'bi-briefcase-fill','sc-sage',BASE_PATH.'/modules/internships/list.php'],
    ['Chờ xét duyệt',$stats['pending'],'bi-hourglass-split','sc-warm',BASE_PATH.'/modules/applications/list.php?status=pending_admin'],
    ['Đang thực tập',$stats['active'],'bi-person-check-fill','sc-teal',BASE_PATH.'/modules/registrations/list.php'],
    ['Báo cáo chờ',$stats['reports'],'bi-file-earmark-text-fill','sc-red',BASE_PATH.'/modules/reports/list.php'],
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

<!-- SV cần phân công GVHD -->
<?php if(!empty($iv_passed)||!empty($need_assign)): ?>
<div class="row g-3 mb-4 fu">
  <?php if(!empty($iv_passed)): ?>
  <div class="col-md-6">
    <div class="card" style="border-left:4px solid #2d6a40"><div class="card-body">
      <h6 class="fw7 mb-2" style="color:#2d6a40"><i class="bi bi-trophy-fill me-2"></i>Đậu phỏng vấn — Cần phân công GVHD (<?=count($iv_passed)?>)</h6>
      <?php foreach($iv_passed as $iv): ?>
      <div class="d-flex justify-content-between align-items-center mb-2">
        <div class="small"><div class="fw7"><?=htmlspecialchars($iv['full_name'])?> (<?=htmlspecialchars($iv['student_code']??'')?>)</div><div class="text-muted"><?=htmlspecialchars($iv['title'])?> @ <?=htmlspecialchars($iv['company_name'])?></div></div>
        <a href="<?=BASE_PATH?>/modules/registrations/assign.php" class="btn btn-primary btn-sm"><i class="bi bi-person-check me-1"></i>Phân công GV</a>
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
        <a href="<?=BASE_PATH?>/modules/registrations/assign.php" class="btn btn-primary btn-sm"><i class="bi bi-person-plus me-1"></i>Phân công</a>
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
        <a href="<?=BASE_PATH?>/modules/applications/list.php" class="btn btn-primary btn-sm">Xem tất cả</a>
      </div>
      <div class="card-body p-0">
        <table class="table mb-0">
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
            <td><a href="<?=BASE_PATH?>/modules/applications/review.php?id=<?=$a['application_id']?>" class="btn btn-primary btn-sm">Xét duyệt</a></td>
          </tr>
          <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <div class="col-lg-4">
    <div class="card fu1"><div class="card-body">
      <h6 class="fw7 mb-3"><i class="bi bi-lightning-fill me-2" style="color:#a07040"></i>Thao tác nhanh</h6>
      <div class="d-grid gap-2">
        <a href="<?=BASE_PATH?>/modules/applications/list.php?status=pending_admin" class="qa">
          <div class="qa-icon" style="background:rgba(196,154,108,.12)"><i class="bi bi-clipboard-check-fill" style="color:#a07040"></i></div>
          <div><div class="qa-title">Xét duyệt đơn</div><div class="qa-sub"><?=$stats['pending']?> đơn chờ</div></div>
        </a>
        <a href="<?=BASE_PATH?>/modules/registrations/list.php" class="qa">
          <div class="qa-icon" style="background:rgba(93,123,111,.12)"><i class="bi bi-journal-richtext" style="color:var(--ds)"></i></div>
          <div><div class="qa-title">Quản lý TT</div><div class="qa-sub"><?=$stats['active']?> đang thực tập</div></div>
        </a>
        <a href="<?=BASE_PATH?>/modules/reports/list.php" class="qa">
          <div class="qa-icon" style="background:rgba(74,158,106,.12)"><i class="bi bi-file-earmark-check-fill" style="color:#2d6a40"></i></div>
          <div><div class="qa-title">Duyệt báo cáo</div><div class="qa-sub"><?=$stats['reports']?> chờ duyệt</div></div>
        </a>
        <a href="<?=BASE_PATH?>/modules/users/create_lecturer.php" class="qa">
          <div class="qa-icon" style="background:rgba(74,138,150,.12)"><i class="bi bi-person-plus-fill" style="color:#3a8a96"></i></div>
          <div><div class="qa-title">Thêm Giảng viên</div><div class="qa-sub">Tạo tài khoản GV</div></div>
        </a>
      </div>
    </div></div>
  </div>
</div>
<?php include '../includes/footer.php'; ?>
