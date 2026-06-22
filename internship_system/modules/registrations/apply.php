<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';
requireRole('student');

$uid = $_SESSION['user_id'];
$pos_id = (int)($_GET['position_id'] ?? $_POST['position_id'] ?? 0);
if (!$pos_id) { setFlash('error','Vị trí không hợp lệ.'); redirect(BASE_PATH.'/modules/positions/list.php'); }

// Check profile completed
$pchk = $conn->prepare("SELECT profile_completed,cv_file,gpa,student_code FROM users WHERE user_id=?");
$pchk->bind_param('i',$uid); $pchk->execute();
$prow = $pchk->get_result()->fetch_assoc();
if (!$prow['profile_completed']) {
    setFlash('warning','⚠️ Vui lòng hoàn thiện hồ sơ sinh viên trước khi ứng tuyển!');
    redirect(BASE_PATH.'/modules/profile/student.php');
}

// Get position info
$pstmt = $conn->prepare("SELECT p.*,c.name AS cname,c.logo AS clogo,c.location AS cloc FROM internship_positions p JOIN companies c ON p.company_id=c.company_id WHERE p.position_id=? AND p.status='open'");
$pstmt->bind_param('i',$pos_id); $pstmt->execute();
$position = $pstmt->get_result()->fetch_assoc();
if (!$position) { setFlash('error','Vị trí không còn nhận đơn.'); redirect(BASE_PATH.'/modules/positions/list.php'); }

// Already applied?
$achk = $conn->prepare("SELECT registration_id,status FROM internship_registrations WHERE student_id=? AND position_id=?");
$achk->bind_param('ii',$uid,$pos_id); $achk->execute();
$existing = $achk->get_result()->fetch_assoc();

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($existing) { setFlash('error','Bạn đã ứng tuyển vị trí này rồi!'); redirect('my.php'); }

    $cover = sanitize($_POST['cover_letter'] ?? '');

    // Upload CV (sử dụng CV profile hoặc upload mới)
    $cv_path = $prow['cv_file']; // default: CV từ profile
    if (!empty($_FILES['cv_file']['tmp_name'])) {
        $up = uploadFile($_FILES['cv_file'], 'cvs', ['pdf','doc','docx'], 5);
        if ($up['ok']) $cv_path = $up['path'];
        else $errors[] = 'CV: '.$up['error'];
    }
    if (!$cv_path) $errors[] = 'Vui lòng tải lên CV (hoặc cập nhật CV trong hồ sơ của bạn).';

    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO internship_registrations (student_id,position_id,status,school_status,company_status,cv_submitted,cover_letter) VALUES (?,?,'pending','pending','pending',?,?)");
        $stmt->bind_param('iiss',$uid,$pos_id,$cv_path,$cover);
        if ($stmt->execute()) {
            setFlash('success','✅ Đã nộp đơn ứng tuyển! Vui lòng chờ trường duyệt.');
            redirect('my.php');
        } else {
            if (strpos($conn->error,'Duplicate') !== false) $errors[] = 'Bạn đã ứng tuyển vị trí này rồi.';
            else $errors[] = 'Lỗi: '.$conn->error;
        }
    }
}

$logoUrl = $position['clogo'] ? UPLOAD_URL.'/'.$position['clogo'] : 'https://ui-avatars.com/api/?name='.urlencode($position['cname']).'&background=A4C3A2&color=2A3F38&size=80&bold=true';
?>
<?php include '../../includes/header.php'; ?>
<div class="page-header fade-up">
  <div><h4><i class="bi bi-send me-2"></i>Nộp đơn ứng tuyển</h4></div>
  <a href="../positions/list.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Quay lại</a>
</div>

<?php if(!empty($errors)): ?>
<div class="alert alert-danger fade-up"><ul class="mb-0"><?php foreach($errors as $e): ?><li><?=htmlspecialchars($e)?></li><?php endforeach; ?></ul></div>
<?php endif; ?>

<?php if($existing): ?>
<div class="alert alert-info fade-up">
  <i class="bi bi-info-circle-fill me-2"></i>
  Bạn đã nộp đơn vị trí này. Trạng thái: <strong><?=htmlspecialchars($existing['status'])?></strong>
  <a href="my.php" class="ms-2 btn btn-sm btn-primary">Xem đơn của tôi</a>
</div>
<?php else: ?>

<div class="row g-4">
  <!-- Position info -->
  <div class="col-md-4">
    <div class="card fade-up" style="border:1.5px solid rgba(164,195,162,.3)">
      <div class="card-body">
        <div class="d-flex align-items-center gap-3 mb-3">
          <img src="<?=$logoUrl?>" style="width:56px;height:56px;border-radius:12px;object-fit:cover">
          <div>
            <div class="fw-700" style="font-size:.9rem"><?=htmlspecialchars($position['cname'])?></div>
            <?php if($position['cloc']): ?>
            <div class="small text-muted"><i class="bi bi-geo-alt me-1"></i><?=htmlspecialchars($position['cloc'])?></div>
            <?php endif; ?>
          </div>
        </div>
        <h5 class="fw-800 mb-3" style="color:var(--text-dark)"><?=htmlspecialchars($position['title'])?></h5>
        <div class="mb-2">
          <?php if($position['work_type']): ?><span class="badge bg-primary me-1"><?=htmlspecialchars($position['work_type'])?></span><?php endif; ?>
          <?php if($position['required_major']): ?><span class="badge bg-warning"><?=htmlspecialchars($position['required_major'])?></span><?php endif; ?>
        </div>
        <?php if($position['salary_range']): ?>
        <div class="small mb-1"><i class="bi bi-cash me-2 text-success"></i><?=htmlspecialchars($position['salary_range'])?></div>
        <?php endif; ?>
        <?php if($position['deadline']): ?>
        <div class="small text-muted"><i class="bi bi-calendar me-2"></i>Hạn: <?=date('d/m/Y',strtotime($position['deadline']))?></div>
        <?php endif; ?>
        <?php if($position['description']): ?>
        <hr>
        <div class="small text-muted"><?=nl2br(htmlspecialchars($position['description']))?></div>
        <?php endif; ?>
        <?php if($position['benefits']): ?>
        <hr>
        <div class="small fw-600 mb-1">Phúc lợi:</div>
        <div class="small text-muted"><?=htmlspecialchars($position['benefits'])?></div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Apply form -->
  <div class="col-md-8">
    <div class="card fade-up-1">
      <div class="card-body">
        <h6 class="fw-700 mb-3" style="color:var(--deep-sage)"><i class="bi bi-file-earmark-person me-2"></i>Hồ sơ ứng tuyển</h6>

        <form method="POST" enctype="multipart/form-data">
          <input type="hidden" name="position_id" value="<?=$pos_id?>">

          <!-- CV -->
          <div class="mb-3">
            <label class="form-label fw-700">CV của bạn *</label>
            <?php if($prow['cv_file']): ?>
            <div class="alert" style="background:rgba(74,158,106,.08);border-left:3px solid #4a9e6a;border-radius:8px;padding:10px 14px;font-size:.85rem">
              <i class="bi bi-check-circle-fill me-2 text-success"></i>
              CV từ hồ sơ:
              <a href="<?=UPLOAD_URL.'/'.$prow['cv_file']?>" target="_blank" style="color:var(--deep-sage);font-weight:600">
                Xem CV hiện tại
              </a>
              — hoặc tải lên CV khác bên dưới
            </div>
            <?php endif; ?>
            <input type="file" name="cv_file" class="form-control mt-2" accept=".pdf,.doc,.docx"
                   <?=$prow['cv_file']?'':'required'?>>
            <div class="small text-muted mt-1">PDF/DOC, tối đa 5MB<?=$prow['cv_file']?' (tùy chọn — để trống để dùng CV hồ sơ)':' (bắt buộc)'?></div>
          </div>

          <!-- Cover letter -->
          <div class="mb-4">
            <label class="form-label fw-700">Thư giới thiệu (Cover Letter)</label>
            <textarea name="cover_letter" class="form-control" rows="6"
                      placeholder="Kính gửi nhà tuyển dụng,

Tôi tên là... đang học ngành... với GPA...
Tôi rất quan tâm đến vị trí... vì...
Tôi có thể đóng góp cho công ty bằng cách...

Tôi mong được có cơ hội phỏng vấn.
Trân trọng,
<?=htmlspecialchars($_SESSION['full_name'])?>"><?=htmlspecialchars($_POST['cover_letter']??'')?></textarea>
            <div class="small text-muted mt-1">Không bắt buộc nhưng sẽ tăng cơ hội được chọn</div>
          </div>

          <!-- GPA display -->
          <?php if($prow['gpa']): ?>
          <div class="mb-3 p-3" style="background:var(--warm-cream);border-radius:10px">
            <div class="small fw-700 mb-1">Thông tin đính kèm từ hồ sơ:</div>
            <div class="small text-muted">GPA: <strong><?=$prow['gpa']?></strong> / 4.0</div>
            <div class="small text-muted">Mã SV: <strong><?=$prow['student_code']?></strong></div>
          </div>
          <?php endif; ?>

          <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">
              <i class="bi bi-send me-1"></i>Nộp đơn ứng tuyển
            </button>
            <a href="../positions/list.php" class="btn btn-outline-secondary">Hủy</a>
          </div>
        </form>

        <!-- Flow explanation -->
        <hr class="my-4">
        <h6 class="fw-700 mb-3" style="color:var(--text-mid)"><i class="bi bi-diagram-3 me-2"></i>Quy trình xét duyệt</h6>
        <div class="d-flex gap-0">
          <?php
          $steps = [
            ['bi-send-fill','Bạn nộp đơn','Gửi CV và thư giới thiệu','var(--deep-sage)'],
            ['bi-building','Trường duyệt','Admin xem xét và duyệt đơn','#3a8a96'],
            ['bi-building-fill','Công ty duyệt','Doanh nghiệp quyết định nhận SV','#4a9e6a'],
            ['bi-calendar-check','Hẹn phỏng vấn','Công ty gửi tin nhắn hẹn lịch','#a07040'],
            ['bi-briefcase-fill','Bắt đầu TT','Thời gian thực tập bắt đầu','#2d6a40'],
          ];
          foreach($steps as $idx=>[$icon,$title,$desc,$color]):
          ?>
          <div style="flex:1;text-align:center;position:relative">
            <div style="width:36px;height:36px;border-radius:50%;background:<?=$color?>;display:flex;align-items:center;justify-content:center;color:#fff;font-size:.85rem;margin:0 auto 6px">
              <i class="bi <?=$icon?>"></i>
            </div>
            <div style="font-size:.72rem;font-weight:700;color:var(--text-dark)"><?=$title?></div>
            <div style="font-size:.65rem;color:var(--text-light)"><?=$desc?></div>
            <?php if($idx < count($steps)-1): ?>
            <div style="position:absolute;top:18px;left:60%;width:80%;height:2px;background:rgba(164,195,162,.4)"></div>
            <?php endif; ?>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>
<?php include '../../includes/footer.php'; ?>
