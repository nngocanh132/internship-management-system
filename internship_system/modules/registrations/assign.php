<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';
requireRole('admin');

// Xử lý phân công GVHD cho SV đã có internship_registration
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['registration_id'])) {
    $reg_id = (int)$_POST['registration_id'];
    $lec_id = (int)$_POST['lecturer_id'];
    $sd     = sanitize($_POST['start_date']??'');
    $ed     = sanitize($_POST['end_date']??'');
    if ($reg_id && $lec_id) {
        $u = $conn->prepare("UPDATE internship_registrations SET lecturer_id=?,start_date=?,end_date=? WHERE registration_id=?");
        if ($u) {
            $sdd=!empty($sd)?$sd:null; $edd=!empty($ed)?$ed:null;
            $u->bind_param('issi',$lec_id,$sdd,$edd,$reg_id); $u->execute();
            setFlash('success','✅ Đã phân công GVHD thành công!');
        }
    }
    redirect('assign.php');
}

// Xử lý phân công GVHD từ application (SV đậu PV nhưng chưa có registration)
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['application_id'])) {
    $app_id = (int)$_POST['application_id'];
    $lec_id = (int)$_POST['lecturer_id'];
    $sd     = sanitize($_POST['start_date']??'');
    $ed     = sanitize($_POST['end_date']??'');
    if ($app_id && $lec_id) {
        // Lấy thông tin application
        $aq = $conn->prepare("SELECT a.*,sp.student_id,i.company_id FROM applications a JOIN student_profiles sp ON a.student_id=sp.student_id JOIN internships i ON a.internship_id=i.internship_id WHERE a.application_id=?");
        if ($aq) {
            $aq->bind_param('i',$app_id); $aq->execute();
            $app = $aq->get_result()->fetch_assoc();
            if ($app) {
                $sdd=!empty($sd)?$sd:null; $edd=!empty($ed)?$ed:null;
                // Tạo registration mới với lecturer
                $ins = $conn->prepare("INSERT IGNORE INTO internship_registrations (student_id,company_id,internship_id,lecturer_id,start_date,end_date,status) VALUES (?,?,?,?,?,?,'active')");
                if ($ins) {
                    $ins->bind_param('iiiiss',$app['student_id'],$app['company_id'],$app['internship_id'],$lec_id,$sdd,$edd);
                    $ins->execute();
                }
                // Update application status
                $conn->query("UPDATE applications SET status='internship_active' WHERE application_id=$app_id");
                setFlash('success','✅ Đã phân công GVHD và bắt đầu kỳ thực tập!');
            }
        }
    }
    redirect('assign.php');
}

// Lấy danh sách giảng viên — KHÔNG dùng 'load' (reserved word)
$lecturers = safeQuery($conn,"SELECT lp.*,
    (SELECT COUNT(*) FROM internship_registrations ir WHERE ir.lecturer_id=lp.lecturer_id AND ir.status='active') AS sv_count
    FROM lecturer_profiles lp
    ORDER BY sv_count ASC, lp.full_name");

// SV đậu PV chưa có registration → cần tạo registration + phân công GV
$need_create = safeQuery($conn,"SELECT a.*,sp.full_name,sp.student_code,sp.gpa,sp.avatar AS s_av,
    i.title,i.start_date AS j_start,i.end_date AS j_end,
    cp.company_name
    FROM applications a
    JOIN student_profiles sp ON a.student_id=sp.student_id
    JOIN internships i ON a.internship_id=i.internship_id
    JOIN company_profiles cp ON i.company_id=cp.company_id
    WHERE a.status='interview_passed'
    AND NOT EXISTS (
        SELECT 1 FROM internship_registrations ir
        WHERE ir.student_id=sp.student_id AND ir.internship_id=i.internship_id
    )
    ORDER BY a.applied_at DESC");

// SV đang TT nhưng chưa có GVHD
$no_lecturer = safeQuery($conn,"SELECT ir.*,sp.full_name,sp.student_code,sp.gpa,sp.avatar AS s_av,
    cp.company_name,i.title
    FROM internship_registrations ir
    JOIN student_profiles sp ON ir.student_id=sp.student_id
    JOIN internships i ON ir.internship_id=i.internship_id
    JOIN company_profiles cp ON ir.company_id=cp.company_id
    WHERE ir.lecturer_id IS NULL AND ir.status='active'
    ORDER BY ir.created_at DESC");

// SV đã có GVHD
$has_lecturer = safeQuery($conn,"SELECT ir.*,sp.full_name,sp.student_code,sp.gpa,
    cp.company_name,i.title,lp.full_name AS lname,lp.department
    FROM internship_registrations ir
    JOIN student_profiles sp ON ir.student_id=sp.student_id
    JOIN internships i ON ir.internship_id=i.internship_id
    JOIN company_profiles cp ON ir.company_id=cp.company_id
    LEFT JOIN lecturer_profiles lp ON ir.lecturer_id=lp.lecturer_id
    WHERE ir.lecturer_id IS NOT NULL
    ORDER BY ir.created_at DESC");

$total_need = count($need_create) + count($no_lecturer);
?>
<?php include '../../includes/header.php'; ?>
<div class="ph fu">
  <div>
    <h4><i class="bi bi-person-check-fill me-2"></i>Phân công Giảng viên Hướng dẫn</h4>
    <div class="ph-sub"><?=$total_need?> SV cần phân công · <?=count($has_lecturer)?> đã có GVHD</div>
  </div>
</div>
<?php showFlash(); ?>

<?php if(empty($lecturers)): ?>
<div class="alert alert-warning fu">
  <i class="bi bi-exclamation-triangle-fill me-2"></i>
  <strong>Chưa có giảng viên nào trong hệ thống!</strong>
  <a href="<?=BASE_PATH?>/modules/users/create_lecturer.php" class="btn btn-warning btn-sm ms-2">Thêm giảng viên ngay</a>
</div>
<?php endif; ?>

<?php
// Helper render form phân công
function renderAssignForm($r, $lecturers, $is_app=false) {
    $id_name  = $is_app ? 'application_id' : 'registration_id';
    $id_val   = $is_app ? $r['application_id'] : $r['registration_id'];
    $default_sd = $r['j_start'] ?? $r['start_date'] ?? '';
    $default_ed = $r['j_end']   ?? $r['end_date']   ?? '';
    $av = ($r['s_av']??'') ? UPLOAD_URL.'/'.$r['s_av'] : 'https://ui-avatars.com/api/?name='.urlencode($r['full_name']).'&background=5D7B6F&color=fff&size=60';
    ?>
    <div class="card h-100" style="border:1.5px solid rgba(196,154,108,.35)">
      <div class="card-body">
        <div class="d-flex align-items-center gap-3 mb-3">
          <img src="<?=$av?>" style="width:44px;height:44px;border-radius:50%;object-fit:cover;flex-shrink:0">
          <div>
            <div class="fw7"><?=htmlspecialchars($r['full_name'])?></div>
            <div class="small text-muted"><?=htmlspecialchars($r['student_code']??'')?> · GPA: <?=$r['gpa']??'—'?></div>
            <div class="small"><i class="bi bi-briefcase me-1 text-muted"></i><?=htmlspecialchars($r['title'])?> @ <?=htmlspecialchars($r['company_name'])?></div>
          </div>
        </div>
        <?php if($is_app): ?>
        <div class="alert alert-success p-2 mb-2" style="font-size:.78rem;border-radius:8px"><i class="bi bi-trophy-fill me-1"></i>Đậu phỏng vấn — Cần phân công GVHD để bắt đầu TT</div>
        <?php endif; ?>
        <form method="POST"><div class="row g-2">
          <input type="hidden" name="<?=$id_name?>" value="<?=$id_val?>">
          <div class="col-12">
            <label class="form-label fw7">Giảng viên hướng dẫn *</label>
            <select name="lecturer_id" class="form-select form-select-sm" required>
              <option value="">— Chọn GVHD —</option>
              <?php foreach($lecturers as $l): ?>
              <option value="<?=$l['lecturer_id']?>"><?=htmlspecialchars($l['full_name'])?> — <?=htmlspecialchars($l['department']??'Chưa có khoa')?> (<?=$l['sv_count']?> SV đang TT)</option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-6">
            <label class="form-label">Ngày bắt đầu</label>
            <input type="date" name="start_date" class="form-control form-control-sm" value="<?=htmlspecialchars($default_sd)?>">
          </div>
          <div class="col-6">
            <label class="form-label">Ngày kết thúc</label>
            <input type="date" name="end_date" class="form-control form-control-sm" value="<?=htmlspecialchars($default_ed)?>">
          </div>
          <div class="col-12">
            <button type="submit" class="btn btn-primary btn-sm w-100">
              <i class="bi bi-person-check me-1"></i>Phân công GVHD<?=$is_app?' & Bắt đầu TT':''?>
            </button>
          </div>
        </div></form>
      </div>
    </div>
    <?php
}
?>

<?php if(!empty($need_create)): ?>
<div class="card mb-4 fu1" style="border-left:4px solid #2d6a40">
  <div class="card-body">
    <h5 class="fw8 mb-3" style="color:#2d6a40"><i class="bi bi-trophy-fill me-2"></i>Đậu phỏng vấn — Phân công GVHD & Bắt đầu TT (<?=count($need_create)?>)</h5>
    <div class="row g-3">
      <?php foreach($need_create as $r): ?>
      <div class="col-md-6"><?php renderAssignForm($r,$lecturers,true); ?></div>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<?php endif; ?>

<?php if(!empty($no_lecturer)): ?>
<div class="card mb-4 fu2" style="border-left:4px solid #a07040">
  <div class="card-body">
    <h5 class="fw8 mb-3" style="color:#a07040"><i class="bi bi-exclamation-triangle me-2"></i>Đang TT chưa có GVHD (<?=count($no_lecturer)?>)</h5>
    <div class="row g-3">
      <?php foreach($no_lecturer as $r): ?>
      <div class="col-md-6"><?php renderAssignForm($r,$lecturers,false); ?></div>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<?php endif; ?>

<?php if($total_need===0): ?>
<div class="card mb-3 fu2" style="border-left:4px solid #2d6a40"><div class="card-body">
  <i class="bi bi-check-circle-fill me-2 text-success"></i>
  <span class="fw7">Không có sinh viên nào cần phân công GVHD!</span>
</div></div>
<?php endif; ?>

<!-- Đã phân công -->
<?php if(!empty($has_lecturer)): ?>
<div class="card tc fu3">
  <div class="card-header d-flex justify-content-between align-items-center">
    <span><i class="bi bi-table me-2"></i>Đã phân công (<?=count($has_lecturer)?>)</span>
  </div>
  <div class="card-body p-0"><table class="table mb-0">
    <thead><tr><th>#</th><th>Sinh viên</th><th>Vị trí / DN</th><th>GVHD</th><th>Thời gian</th><th>TT</th><th>Đổi GVHD</th></tr></thead>
    <tbody>
    <?php foreach($has_lecturer as $i=>$r): ?>
    <tr>
      <td class="small text-muted"><?=$i+1?></td>
      <td>
        <div class="fw7 small"><?=htmlspecialchars($r['full_name'])?></div>
        <div class="small text-muted"><?=htmlspecialchars($r['student_code']??'')?></div>
      </td>
      <td>
        <div class="fw7 small"><?=htmlspecialchars($r['title'])?></div>
        <div class="small text-muted"><?=htmlspecialchars($r['company_name'])?></div>
      </td>
      <td>
        <div class="fw7 small"><?=htmlspecialchars($r['lname']??'—')?></div>
        <?php if($r['department']??''): ?><div class="small text-muted"><?=htmlspecialchars($r['department'])?></div><?php endif; ?>
      </td>
      <td class="small text-muted">
        <?=$r['start_date']?date('d/m/Y',strtotime($r['start_date'])):''?>
        <?php if($r['end_date']): ?><br>→ <?=date('d/m/Y',strtotime($r['end_date']))?><?php endif; ?>
      </td>
      <td><span class="badge" style="<?=$r['status']==='active'?'background:rgba(74,158,106,.12);color:#2d6a40':'background:rgba(93,123,111,.12);color:var(--ds)'?>"><?=$r['status']==='active'?'🚀 Đang TT':'🏆 Xong'?></span></td>
      <td>
        <form method="POST" class="d-flex gap-1">
          <input type="hidden" name="registration_id" value="<?=$r['registration_id']?>">
          <select name="lecturer_id" class="form-select form-select-sm" style="min-width:140px">
            <?php foreach($lecturers as $l): ?>
            <option value="<?=$l['lecturer_id']?>" <?=$r['lecturer_id']==$l['lecturer_id']?'selected':''?>><?=htmlspecialchars($l['full_name'])?></option>
            <?php endforeach; ?>
          </select>
          <button type="submit" class="btn btn-warning btn-sm" title="Cập nhật"><i class="bi bi-check"></i></button>
        </form>
      </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table></div>
</div>
<?php endif; ?>
<?php include '../../includes/footer.php'; ?>
