<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';
requireRole('student');

$uid=$_SESSION['user_id'];
$sq=$conn->prepare("SELECT student_id FROM student_profiles WHERE user_id=?");
$sq->bind_param('i',$uid); $sq->execute();
$sid=$sq->get_result()->fetch_assoc()['student_id']??0;

$rq=$conn->prepare("SELECT ir.*,i.title,i.description,i.location,i.start_date AS job_start,i.end_date AS job_end,
  cp.company_name,cp.logo,cp.address AS c_addr,cp.website,cp.company_id,
  lp.full_name AS lecturer_name,lp.phone AS lec_phone,lp.email AS lec_email,lp.department,
  ev.overall_score,ev.technical_skill,ev.teamwork,ev.communication,ev.attitude,ev.comment AS ev_comment,
  rp.report_file,rp.status AS report_status,rp.lecturer_comment,rp.submitted_at AS report_submitted
  FROM internship_registrations ir
  JOIN internships i ON ir.internship_id=i.internship_id
  JOIN company_profiles cp ON ir.company_id=cp.company_id
  LEFT JOIN lecturer_profiles lp ON ir.lecturer_id=lp.lecturer_id
  LEFT JOIN evaluations ev ON ir.registration_id=ev.registration_id
  LEFT JOIN internship_reports rp ON ir.registration_id=rp.registration_id
  WHERE ir.student_id=?
  ORDER BY ir.created_at DESC LIMIT 1");
$rq->bind_param('i',$sid); $rq->execute();
$reg=$rq->get_result()->fetch_assoc();
?>
<?php include '../../includes/header.php'; ?>
<div class="ph fu">
  <div><h4><i class="bi bi-briefcase-fill me-2"></i>Thực tập của tôi</h4></div>
</div>
<?php showFlash(); ?>

<?php if(!$reg): ?>
<div class="card text-center py-5 fu1"><div class="card-body">
  <i class="bi bi-briefcase" style="font-size:3rem;color:var(--tl)"></i>
  <h5 class="mt-3 fw7">Chưa có kỳ thực tập</h5>
  <p class="text-muted">Bạn chưa được nhận vào thực tập. Hãy ứng tuyển để bắt đầu!</p>
  <a href="<?=BASE_PATH?>/modules/internships/browse.php" class="btn btn-primary"><i class="bi bi-search me-1"></i>Tìm việc thực tập</a>
</div></div>
<?php else:
  $logo=$reg['logo']?UPLOAD_URL.'/'.$reg['logo']:'https://ui-avatars.com/api/?name='.urlencode($reg['company_name']).'&background=5D7B6F&color=fff&size=80';
?>

<!-- Status banner -->
<div class="card mb-4 fu" style="background:linear-gradient(135deg,<?=$reg['status']==='completed'?'#2d7a50,#1a4a2a':'var(--ds),var(--ds2)'?>);border:none">
  <div class="card-body text-white">
    <div class="d-flex align-items-center gap-3">
      <img src="<?=$logo?>" style="width:60px;height:60px;border-radius:14px;object-fit:cover;border:2px solid rgba(255,255,255,.3)">
      <div>
        <h4 class="fw8 mb-1"><?=htmlspecialchars($reg['title'])?></h4>
        <div style="opacity:.85"><?=htmlspecialchars($reg['company_name'])?></div>
        <?php if($reg['location']): ?><div style="opacity:.7;font-size:.82rem"><i class="bi bi-geo-alt me-1"></i><?=htmlspecialchars($reg['location'])?></div><?php endif; ?>
      </div>
      <div class="ms-auto text-end">
        <div style="font-size:1.5rem;font-weight:800"><?=$reg['status']==='completed'?'🏆':'🚀'?></div>
        <div style="opacity:.85;font-size:.85rem"><?=$reg['status']==='completed'?'Hoàn thành':'Đang thực tập'?></div>
        <?php if($reg['start_date']||$reg['end_date']): ?>
        <div style="opacity:.7;font-size:.75rem"><?=$reg['start_date']?date('d/m/Y',strtotime($reg['start_date'])):''?> → <?=$reg['end_date']?date('d/m/Y',strtotime($reg['end_date'])):''?></div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<div class="row g-4">
  <!-- Left: info -->
  <div class="col-md-4">
    <!-- Lecturer -->
    <?php if($reg['lecturer_name']): ?>
    <div class="card mb-3 fu1" style="border-left:4px solid #4ab8c4">
      <div class="card-body">
        <h6 class="fw7 mb-2" style="color:#3a8a96"><i class="bi bi-person-workspace me-2"></i>Giảng viên hướng dẫn</h6>
        <div class="fw7 mb-1"><?=htmlspecialchars($reg['lecturer_name'])?></div>
        <?php if($reg['department']): ?><div class="small text-muted mb-2"><i class="bi bi-building me-1"></i><?=htmlspecialchars($reg['department'])?></div><?php endif; ?>
        <?php if($reg['lec_email']): ?>
        <div class="small mb-1">
          <i class="bi bi-envelope me-2" style="color:#3a8a96"></i>
          <a href="mailto:<?=htmlspecialchars($reg['lec_email'])?>" style="color:#3a8a96;font-weight:600"><?=htmlspecialchars($reg['lec_email'])?></a>
        </div>
        <?php endif; ?>
        <?php if($reg['lec_phone']): ?>
        <div class="small mb-2">
          <i class="bi bi-telephone me-2" style="color:#3a8a96"></i>
          <a href="tel:<?=htmlspecialchars($reg['lec_phone'])?>" style="color:#3a8a96;font-weight:600"><?=htmlspecialchars($reg['lec_phone'])?></a>
        </div>
        <?php endif; ?>
        <div class="d-flex gap-2 mt-2">
          <?php if($reg['lec_email']): ?>
          <a href="mailto:<?=htmlspecialchars($reg['lec_email'])?>" class="btn btn-sm w-100" style="background:rgba(74,138,150,.12);color:#3a8a96;border:1px solid rgba(74,138,150,.3)">
            <i class="bi bi-envelope-fill me-1"></i>Gửi email GV
          </a>
          <?php endif; ?>
        </div>
      </div>
    </div>
    <?php else: ?>
    <div class="card mb-3 fu1" style="border:1.5px dashed rgba(164,195,162,.4)"><div class="card-body text-center py-3">
      <i class="bi bi-person-workspace" style="font-size:1.8rem;color:var(--tl)"></i>
      <div class="mt-2 text-muted small">Chưa có GVHD — Admin sẽ sớm phân công</div>
    </div></div>
    <?php endif; ?>

    <!-- Company contact -->
    <div class="card mb-3 fu2">
      <div class="card-body">
        <h6 class="fw7 mb-2" style="color:var(--ds)"><i class="bi bi-building me-2"></i>Doanh nghiệp</h6>
        <?php if($reg['c_addr']): ?><div class="small mb-1"><i class="bi bi-geo-alt me-2 text-muted"></i><?=htmlspecialchars($reg['c_addr'])?></div><?php endif; ?>
        <?php if($reg['website']): ?><div class="small mb-2"><i class="bi bi-globe me-2 text-muted"></i><a href="<?=htmlspecialchars($reg['website'])?>" target="_blank"><?=htmlspecialchars($reg['website'])?></a></div><?php endif; ?>
        <a href="<?=BASE_PATH?>/modules/messages/chat.php?company_id=<?=$reg['company_id']?>" class="btn btn-primary btn-sm w-100">
          <i class="bi bi-chat-dots me-1"></i>Nhắn tin với công ty
        </a>
      </div>
    </div>

    <!-- Quick actions -->
    <div class="card fu3">
      <div class="card-body">
        <h6 class="fw7 mb-2" style="color:var(--ds)">Thao tác nhanh</h6>
        <div class="d-grid gap-2">
          <?php if($reg['lecturer_name']): ?>
          <a href="<?=BASE_PATH?>/modules/messages/lecturer_chat.php" class="btn btn-sm" style="background:rgba(74,138,150,.12);color:#3a8a96;border:1px solid rgba(74,138,150,.3)">
            <i class="bi bi-person-workspace me-1"></i>Nhắn tin GVHD — <?=htmlspecialchars($reg['lecturer_name'])?>
          </a>
          <?php endif; ?>
          <a href="<?=BASE_PATH?>/modules/reports/submit.php" class="btn btn-primary btn-sm">
            <i class="bi bi-file-earmark-arrow-up me-1"></i><?=($reg['report_file']??'')?'Cập nhật báo cáo':'Nộp báo cáo TT'?>
          </a>
        </div>
      </div>
    </div>
  </div>

  <!-- Right: evaluation + report -->
  <div class="col-md-8">
    <!-- Evaluation -->
    <?php if($reg['overall_score']): ?>
    <div class="card mb-3 fu1" style="border-left:4px solid <?=$reg['overall_score']>=7?'#2d6a40':($reg['overall_score']>=5?'var(--ds)':'#9a3030')?>">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h6 class="fw7 mb-0"><i class="bi bi-star-fill me-2" style="color:#a07040"></i>Đánh giá từ doanh nghiệp</h6>
          <div>
            <span style="font-size:2.5rem;font-weight:800;color:<?=$reg['overall_score']>=7?'#2d6a40':($reg['overall_score']>=5?'var(--ds)':'#9a3030')?>;line-height:1"><?=$reg['overall_score']?></span>
            <span class="text-muted small">/10</span>
          </div>
        </div>
        <div class="row g-2 mb-3">
          <?php foreach(['technical_skill'=>'🔧 Kỹ thuật','teamwork'=>'👥 Nhóm','communication'=>'💬 Giao tiếp','attitude'=>'😊 Thái độ'] as $k=>$l): ?>
          <div class="col-6">
            <div class="small text-muted mb-1"><?=$l?></div>
            <div class="d-flex align-items-center gap-2">
              <div style="flex:1;height:7px;background:rgba(164,195,162,.22);border-radius:4px;overflow:hidden">
                <div style="height:7px;background:linear-gradient(90deg,var(--ds),var(--sg));border-radius:4px;width:<?=($reg[$k]/10*100)?>%"></div>
              </div>
              <span class="fw7 small"><?=$reg[$k]?></span>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <?php if($reg['ev_comment']): ?>
        <div style="background:rgba(164,195,162,.08);border-radius:9px;padding:12px;font-size:.85rem">
          <strong>Nhận xét:</strong> <?=nl2br(htmlspecialchars($reg['ev_comment']))?>
        </div>
        <?php endif; ?>
      </div>
    </div>
    <?php else: ?>
    <div class="card mb-3 fu1" style="border:1.5px dashed rgba(164,195,162,.4)"><div class="card-body text-center py-3">
      <i class="bi bi-star text-muted fs-2 opacity-50"></i>
      <div class="mt-2 text-muted small">Chưa có đánh giá từ doanh nghiệp</div>
    </div></div>
    <?php endif; ?>

    <!-- Report status -->
    <?php if($reg['report_file']||$reg['report_submitted']): ?>
    <div class="card fu2" style="border-left:4px solid <?=$reg['report_status']==='approved'?'#2d6a40':($reg['report_status']==='rejected'?'#9a3030':'var(--ds)')?>">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-start">
          <h6 class="fw7 mb-2"><i class="bi bi-file-earmark-text me-2"></i>Báo cáo thực tập</h6>
          <?php
          $rs_map=['pending'=>['⏳ Chờ duyệt','rgba(196,154,108,.12)','#a07040'],'approved'=>['✅ Đã duyệt','rgba(74,158,106,.12)','#2d6a40'],'rejected'=>['❌ Cần sửa lại','rgba(192,96,80,.12)','#9a3030']];
          [$rl,$rb,$rc]=$rs_map[$reg['report_status']]??['—','rgba(160,160,160,.1)','#5a5a5a'];
          ?><span class="badge" style="background:<?=$rb?>;color:<?=$rc?>"><?=$rl?></span>
        </div>
        <?php if($reg['report_submitted']): ?><div class="small text-muted mb-2">Nộp lúc: <?=date('d/m/Y H:i',strtotime($reg['report_submitted']))?></div><?php endif; ?>
        <?php if($reg['lecturer_comment']): ?>
        <div style="background:rgba(164,195,162,.08);border-radius:9px;padding:10px 12px;font-size:.82rem">
          <strong>Nhận xét GVHD:</strong> <?=nl2br(htmlspecialchars($reg['lecturer_comment']))?>
        </div>
        <?php endif; ?>
        <div class="d-flex gap-2 mt-2">
          <?php if($reg['report_file']): ?><a href="<?=UPLOAD_URL.'/'.$reg['report_file']?>" target="_blank" class="btn btn-secondary btn-sm"><i class="bi bi-download me-1"></i>Tải báo cáo</a><?php endif; ?>
          <?php if($reg['report_status']==='rejected'): ?><a href="<?=BASE_PATH?>/modules/reports/submit.php" class="btn btn-primary btn-sm"><i class="bi bi-arrow-repeat me-1"></i>Nộp lại</a><?php endif; ?>
        </div>
      </div>
    </div>
    <?php else: ?>
    <div class="card fu3" style="border:1.5px dashed rgba(164,195,162,.4)"><div class="card-body text-center py-3">
      <i class="bi bi-file-earmark-arrow-up text-muted fs-2 opacity-50"></i>
      <div class="mt-2 text-muted small">Chưa nộp báo cáo thực tập</div>
      <a href="<?=BASE_PATH?>/modules/reports/submit.php" class="btn btn-primary btn-sm mt-2"><i class="bi bi-upload me-1"></i>Nộp ngay</a>
    </div></div>
    <?php endif; ?>
  </div>
</div>
<?php endif; ?>
<?php include '../../includes/footer.php'; ?>
