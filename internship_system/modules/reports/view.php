<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';
requireRole(['admin','lecturer']);

$id = (int)($_GET['id'] ?? 0);
if (!$id) redirect('list.php');

$stmt = $conn->prepare("SELECT rp.*,u.full_name AS student_name,u.student_code,u.gpa,u.avatar AS student_avatar,p.title AS position_title,c.name AS company_name,a.start_date,a.end_date,lec.full_name AS lecturer_name
  FROM internship_reports rp
  JOIN users u ON rp.student_id=u.user_id
  JOIN internship_assignments a ON rp.assignment_id=a.assignment_id
  JOIN internship_registrations r ON a.registration_id=r.registration_id
  JOIN internship_positions p ON r.position_id=p.position_id
  JOIN companies c ON p.company_id=c.company_id
  JOIN users lec ON a.lecturer_id=lec.user_id
  WHERE rp.report_id=?");
$stmt->bind_param('i',$id); $stmt->execute();
$rp = $stmt->get_result()->fetch_assoc();
if (!$rp) { setFlash('error','Không tìm thấy báo cáo.'); redirect('list.php'); }

// Save comment
$errors = [];
if ($_SERVER['REQUEST_METHOD']==='POST' && isAdmin()) {
    $comment = sanitize($_POST['admin_comment']??'');
    $u = $conn->prepare("UPDATE internship_reports SET admin_comment=?,status='reviewed',reviewed_at=NOW() WHERE report_id=?");
    $u->bind_param('si',$comment,$id); $u->execute();
    setFlash('success','Đã lưu nhận xét.'); redirect("view.php?id=$id");
}

$av = $rp['student_avatar'] ? UPLOAD_URL.'/'.$rp['student_avatar'] : 'https://ui-avatars.com/api/?name='.urlencode($rp['student_name']).'&background=5D7B6F&color=fff&size=80';
?>
<?php include '../../includes/header.php'; ?>
<div class="page-header fade-up">
  <div><h4><i class="bi bi-file-earmark-text me-2"></i>Chi tiết Báo cáo Thực tập</h4></div>
  <a href="list.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Quay lại</a>
</div>

<div class="row g-4">
  <div class="col-md-4">
    <!-- Student info -->
    <div class="card fade-up text-center">
      <div class="card-body">
        <img src="<?=$av?>" style="width:80px;height:80px;border-radius:50%;object-fit:cover;border:3px solid var(--sage-green);margin-bottom:12px">
        <h5 class="fw-800 mb-1"><?=htmlspecialchars($rp['student_name'])?></h5>
        <div class="small text-muted mb-3"><?=htmlspecialchars($rp['student_code']??'')?></div>
        <?php if($rp['gpa']): ?>
        <div class="mb-2">
          <span class="badge bg-primary" style="font-size:.82rem;padding:6px 12px">GPA: <?=$rp['gpa']?></span>
        </div>
        <?php endif; ?>
        <hr>
        <div class="text-start">
          <div class="small text-muted mb-1">Vị trí thực tập</div>
          <div class="fw-700 small mb-2"><?=htmlspecialchars($rp['position_title'])?></div>
          <div class="small text-muted mb-1">Doanh nghiệp</div>
          <div class="fw-600 small mb-2"><?=htmlspecialchars($rp['company_name'])?></div>
          <div class="small text-muted mb-1">GVHD</div>
          <div class="fw-600 small mb-2"><?=htmlspecialchars($rp['lecturer_name'])?></div>
          <div class="small text-muted mb-1">Thời gian TT</div>
          <div class="fw-600 small"><?=$rp['start_date']??'?'?> → <?=$rp['end_date']??'?'?></div>
        </div>
        <hr>
        <?php if($rp['report_file']): ?>
        <a href="<?=UPLOAD_URL.'/'.$rp['report_file']?>" target="_blank" class="btn btn-primary w-100">
          <i class="bi bi-download me-1"></i>Tải xuống báo cáo
        </a>
        <?php endif; ?>
        <?php
        $sc=match($rp['status']){'approved'=>['rgba(74,158,106,.12)','#2d6a40','✅ Đã duyệt'],'reviewed'=>['rgba(74,138,150,.12)','#3a8a96','Đã xem'],default=>['rgba(196,154,108,.12)','#a07040','⏳ Chờ duyệt']};
        ?>
        <div class="mt-2"><span class="badge" style="background:<?=$sc[0]?>;color:<?=$sc[1]?>;padding:6px 14px"><?=$sc[2]?></span></div>
      </div>
    </div>

    <!-- Admin comment form -->
    <?php if(isAdmin() && $rp['status']!=='approved'): ?>
    <div class="card mt-3 fade-up-1">
      <div class="card-body">
        <h6 class="fw-700 mb-2"><i class="bi bi-chat-text me-2"></i>Nhận xét của Admin</h6>
        <form method="POST">
          <textarea name="admin_comment" class="form-control mb-2" rows="3" placeholder="Nhận xét về báo cáo..."><?=htmlspecialchars($rp['admin_comment']??'')?></textarea>
          <div class="d-grid gap-2">
            <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-save me-1"></i>Lưu nhận xét</button>
            <a href="list.php?approve=<?=$rp['report_id']?>" class="btn btn-success btn-sm" onclick="return confirm('Duyệt báo cáo này?')"><i class="bi bi-check-lg me-1"></i>Duyệt báo cáo</a>
          </div>
        </form>
      </div>
    </div>
    <?php elseif($rp['admin_comment']): ?>
    <div class="card mt-3 fade-up-1" style="border-left:3px solid var(--deep-sage)">
      <div class="card-body">
        <h6 class="fw-700 mb-2" style="color:var(--deep-sage)"><i class="bi bi-chat-text me-2"></i>Nhận xét</h6>
        <p class="small mb-0"><?=nl2br(htmlspecialchars($rp['admin_comment']))?></p>
      </div>
    </div>
    <?php endif; ?>
  </div>

  <div class="col-md-8">
    <div class="card fade-up-1">
      <div class="card-body">
        <h5 class="fw-800 mb-1">Nội dung Báo cáo</h5>
        <div class="small text-muted mb-3">Nộp lúc: <?=date('d/m/Y H:i',strtotime($rp['submitted_at']))?></div>
        <div style="background:var(--warm-cream);border-radius:12px;padding:20px;line-height:1.8;font-size:.9rem;white-space:pre-wrap">
          <?=htmlspecialchars($rp['report_content'])?>
        </div>
      </div>
    </div>
  </div>
</div>
<?php include '../../includes/footer.php'; ?>
