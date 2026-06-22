<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';
requireRole('lecturer');

$uid = $_SESSION['user_id'];
$lid = 0;
$lq  = $conn->prepare("SELECT lecturer_id FROM lecturer_profiles WHERE user_id=?");
if ($lq) { $lq->bind_param('i',$uid); $lq->execute(); $lid=$lq->get_result()->fetch_assoc()['lecturer_id']??0; }

$rows = [];
if ($lid) {
    $sq = $conn->prepare("SELECT ir.registration_id, ir.student_id, ir.company_id, ir.internship_id,
        ir.lecturer_id, ir.start_date, ir.end_date, ir.status, ir.created_at,
        sp.full_name, sp.student_code, sp.gpa, sp.major, sp.avatar AS s_av,
        sp.phone AS s_phone, sp.about_me, sp.linkedin_url, sp.student_id AS sp_sid,
        u.email AS s_email, u.user_id AS s_user_id,
        cp.company_name, cp.phone AS c_phone, cp.website, cp.address AS c_addr, cp.company_id,
        i.title, i.description, i.requirements, i.location,
        i.start_date AS j_start, i.end_date AS j_end,
        rp.status AS report_status, rp.submitted_at AS report_at,
        rp.report_id, rp.report_file, rp.lecturer_comment,
        ev.overall_score, ev.technical_skill, ev.teamwork, ev.communication, ev.attitude
        FROM internship_registrations ir
        JOIN student_profiles sp ON ir.student_id = sp.student_id
        JOIN users u ON sp.user_id = u.user_id
        JOIN internships i ON ir.internship_id = i.internship_id
        JOIN company_profiles cp ON ir.company_id = cp.company_id
        LEFT JOIN internship_reports rp ON rp.registration_id = ir.registration_id
        LEFT JOIN evaluations ev ON ev.registration_id = ir.registration_id
        WHERE ir.lecturer_id = ?
        ORDER BY ir.status ASC, sp.full_name ASC");
    if ($sq) {
        $sq->bind_param('i', $lid);
        $sq->execute();
        $rows = $sq->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}

// View detail
$view_id = (int)($_GET['view']??0);
$detail  = null;
foreach($rows as $r) { if ($r['registration_id']==$view_id) { $detail=$r; break; } }
?>
<?php include '../../includes/header.php'; ?>

<?php if($detail): ?>
<!-- Chi tiết một sinh viên -->
<div class="ph fu">
  <div><h4><i class="bi bi-person-badge me-2"></i>Chi tiết sinh viên thực tập</h4></div>
  <a href="my_students.php" class="btn btn-secondary"><i class="bi bi-arrow-left me-1"></i>Quay lại danh sách</a>
</div>

<div class="row g-4">
  <!-- Student info -->
  <div class="col-md-4">
    <?php
    $av = ($detail['s_av']??'') ? UPLOAD_URL.'/'.$detail['s_av'] : 'https://ui-avatars.com/api/?name='.urlencode($detail['full_name']).'&background=5D7B6F&color=fff&size=100';
    ?>
    <div class="card fu text-center">
      <div class="card-body">
        <img src="<?=$av?>" style="width:90px;height:90px;border-radius:50%;object-fit:cover;border:3px solid var(--sg);margin-bottom:12px">
        <h5 class="fw8 mb-1"><?=htmlspecialchars($detail['full_name'])?></h5>
        <div class="small text-muted"><?=htmlspecialchars($detail['student_code']??'')?></div>
        <div class="d-flex justify-content-center gap-2 mt-2 flex-wrap">
          <span class="badge bg-primary" style="font-size:.8rem">GPA: <?=$detail['gpa']??'—'?></span>
          <?php if($detail['major']??''): ?><span class="badge bg-secondary" style="font-size:.8rem"><?=htmlspecialchars($detail['major'])?></span><?php endif; ?>
        </div>
        <hr>
        <div class="text-start small">
          <div class="mb-1"><i class="bi bi-envelope me-2" style="color:var(--ds)"></i><a href="mailto:<?=htmlspecialchars($detail['s_email'])?>" style="color:var(--ds)"><?=htmlspecialchars($detail['s_email'])?></a></div>
          <?php if($detail['s_phone']??''): ?><div class="mb-1"><i class="bi bi-telephone me-2" style="color:var(--ds)"></i><a href="tel:<?=htmlspecialchars($detail['s_phone'])?>" style="color:var(--ds)"><?=htmlspecialchars($detail['s_phone'])?></a></div><?php endif; ?>
          <?php if($detail['linkedin_url']??''): ?><div class="mb-1"><i class="bi bi-linkedin me-2" style="color:var(--ds)"></i><a href="<?=htmlspecialchars($detail['linkedin_url'])?>" target="_blank" style="color:var(--ds)">LinkedIn</a></div><?php endif; ?>
        </div>
        <?php if($detail['about_me']??''): ?>
        <div class="mt-2 p-2" style="background:var(--warm-cream);border-radius:8px;font-size:.78rem;text-align:left;color:var(--tm)"><?=nl2br(htmlspecialchars($detail['about_me']))?></div>
        <?php endif; ?>
        <hr>
        <!-- Nút nhắn tin -->
        <a href="<?=BASE_PATH?>/modules/messages/lecturer_chat.php?student_uid=<?=$detail['s_user_id']?>" class="btn btn-primary w-100">
          <i class="bi bi-chat-dots-fill me-1"></i>Nhắn tin với sinh viên
        </a>
      </div>
    </div>

    <!-- Trạng thái -->
    <div class="card mt-3 fu1"><div class="card-body">
      <h6 class="fw7 mb-2" style="color:var(--ds)">Trạng thái TT</h6>
      <span class="badge" style="<?=$detail['status']==='active'?'background:rgba(74,158,106,.12);color:#2d6a40':'background:rgba(93,123,111,.12);color:var(--ds)'?>;font-size:.85rem;padding:6px 12px"><?=$detail['status']==='active'?'🚀 Đang thực tập':'🏆 Hoàn thành'?></span>
      <?php if($detail['j_start']||$detail['j_end']): ?>
      <div class="small text-muted mt-2"><i class="bi bi-calendar3 me-1"></i><?=$detail['j_start']?date('d/m/Y',strtotime($detail['j_start'])):''?> → <?=$detail['j_end']?date('d/m/Y',strtotime($detail['j_end'])):'?'?></div>
      <?php endif; ?>
    </div></div>
  </div>

  <div class="col-md-8">
    <!-- Job info -->
    <div class="card mb-3 fu1" style="border-left:4px solid var(--ds)"><div class="card-body">
      <h6 class="fw7 mb-2" style="color:var(--ds)"><i class="bi bi-briefcase me-2"></i>Thông tin vị trí thực tập</h6>
      <div class="fw7 mb-1"><?=htmlspecialchars($detail['title'])?></div>
      <div class="small text-muted mb-2"><i class="bi bi-building me-1"></i><?=htmlspecialchars($detail['company_name'])?><?=$detail['c_addr']?' · '.htmlspecialchars($detail['c_addr']):''?></div>
      <?php if($detail['location']??''): ?><div class="small mb-1"><i class="bi bi-geo-alt me-1 text-muted"></i><?=htmlspecialchars($detail['location'])?></div><?php endif; ?>
      <?php if($detail['description']??''): ?>
      <div class="mt-2 p-2" style="background:rgba(164,195,162,.08);border-radius:8px;font-size:.82rem"><?=nl2br(htmlspecialchars($detail['description']))?></div>
      <?php endif; ?>
      <?php if($detail['requirements']??''): ?>
      <div class="mt-2 small"><strong>Yêu cầu:</strong> <?=nl2br(htmlspecialchars($detail['requirements']))?></div>
      <?php endif; ?>
      <?php if($detail['c_phone']||$detail['website']): ?>
      <div class="d-flex gap-3 mt-2 small text-muted">
        <?php if($detail['c_phone']): ?><span><i class="bi bi-telephone me-1"></i><?=htmlspecialchars($detail['c_phone'])?></span><?php endif; ?>
        <?php if($detail['website']): ?><a href="<?=htmlspecialchars($detail['website'])?>" target="_blank" style="color:var(--ds)"><i class="bi bi-globe me-1"></i>Website DN</a><?php endif; ?>
      </div>
      <?php endif; ?>
    </div></div>

    <!-- Báo cáo -->
    <div class="card mb-3 fu2"><div class="card-body">
      <h6 class="fw7 mb-2" style="color:var(--ds)"><i class="bi bi-file-earmark-text me-2"></i>Báo cáo thực tập</h6>
      <?php if($detail['report_id']??0): ?>
      <?php $sc_map=['pending'=>['⏳ Chờ duyệt','#a07040'],'approved'=>['✅ Đã duyệt','#2d6a40'],'rejected'=>['❌ Cần sửa','#9a3030']]; [$rl,$rc]=$sc_map[$detail['report_status']]??['Chưa nộp','#5a5a5a']; ?>
      <div class="d-flex justify-content-between align-items-center mb-2">
        <span class="fw7 small" style="color:<?=$rc?>"><?=$rl?></span>
        <span class="small text-muted"><?=date('d/m/Y H:i',strtotime($detail['report_at']))?></span>
      </div>
      <?php if($detail['report_file']): ?><a href="<?=UPLOAD_URL.'/'.$detail['report_file']?>" target="_blank" class="btn btn-primary btn-sm mb-2 w-100"><i class="bi bi-file-earmark-pdf me-1"></i>Xem & Tải báo cáo</a><?php endif; ?>
      <?php if($detail['lecturer_comment']??''): ?><div class="p-2 mb-2" style="background:rgba(74,138,150,.08);border-radius:8px;font-size:.82rem"><strong>Nhận xét của bạn:</strong> <?=nl2br(htmlspecialchars($detail['lecturer_comment']))?></div><?php endif; ?>
      <?php if($detail['report_status']==='pending'): ?>
      <a href="<?=BASE_PATH?>/modules/reports/review.php" class="btn btn-warning btn-sm w-100"><i class="bi bi-check-circle me-1"></i>Duyệt báo cáo</a>
      <?php endif; ?>
      <?php else: ?>
      <div class="text-muted small"><i class="bi bi-clock me-2"></i>Sinh viên chưa nộp báo cáo</div>
      <?php endif; ?>
    </div></div>

    <!-- Đánh giá DN -->
    <?php if($detail['overall_score']??0): ?>
    <div class="card fu3"><div class="card-body">
      <h6 class="fw7 mb-2" style="color:var(--ds)"><i class="bi bi-star-fill me-2"></i>Đánh giá từ Doanh nghiệp</h6>
      <div class="d-flex align-items-center gap-3 mb-2">
        <div style="font-size:2rem;font-weight:800;color:<?=$detail['overall_score']>=7?'#2d6a40':'#a07040'?>"><?=number_format($detail['overall_score'],1)?></div>
        <div class="small text-muted">/10 điểm tổng</div>
      </div>
      <div class="row g-2">
        <?php foreach(['technical_skill'=>'Kỹ thuật','teamwork'=>'Nhóm','communication'=>'Giao tiếp','attitude'=>'Thái độ'] as $k=>$l): ?>
        <div class="col-6"><div class="small text-muted"><?=$l?></div>
          <div class="d-flex align-items-center gap-2">
            <div style="flex:1;height:5px;background:rgba(164,195,162,.25);border-radius:3px">
              <div style="height:5px;background:var(--ds);border-radius:3px;width:<?=($detail[$k]??0)*10?>%"></div>
            </div><span class="fw7 small"><?=$detail[$k]??0?></span>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div></div>
    <?php endif; ?>
  </div>
</div>

<?php else: ?>
<!-- Danh sách sinh viên -->
<div class="ph fu">
  <div><h4><i class="bi bi-people-fill me-2"></i>Sinh viên được phân công</h4><div class="ph-sub">Tổng: <?=count($rows)?></div></div>
</div>
<?php showFlash(); ?>

<?php if(empty($rows)): ?>
<div class="card text-center py-5 fu1"><div class="card-body">
  <i class="bi bi-people" style="font-size:3rem;color:var(--tl)"></i>
  <h5 class="mt-3 fw7">Chưa có sinh viên được phân công</h5>
</div></div>
<?php else: ?>
<div class="row g-3 fu1">
<?php foreach($rows as $i=>$s):
  $av = ($s['s_av']??'') ? UPLOAD_URL.'/'.$s['s_av'] : 'https://ui-avatars.com/api/?name='.urlencode($s['full_name']).'&background=5D7B6F&color=fff&size=60';
  $rp_map=['pending'=>['⏳ Chờ duyệt','#a07040'],'approved'=>['✅ Đã duyệt','#2d6a40'],'rejected'=>['❌ Cần sửa','#9a3030']];
  [$rl,$rc]=isset($s['report_status'])?($rp_map[$s['report_status']]??['Chưa nộp','#5a5a5a']):['Chưa nộp','#5a5a5a'];
?>
<div class="col-md-6" style="animation:fadeUp .32s <?=$i*.04?>s ease both">
  <div class="card h-100" style="border:1.5px solid rgba(164,195,162,.25)">
    <div class="card-body">
      <div class="d-flex align-items-center gap-3 mb-3">
        <img src="<?=$av?>" style="width:48px;height:48px;border-radius:50%;object-fit:cover;flex-shrink:0">
        <div style="flex:1">
          <div class="fw7"><?=htmlspecialchars($s['full_name'])?></div>
          <div class="small text-muted"><?=htmlspecialchars($s['student_code']??'')?> · GPA: <strong><?=$s['gpa']??'—'?></strong></div>
          <?php if($s['major']??''): ?><div class="small text-muted"><?=htmlspecialchars($s['major'])?></div><?php endif; ?>
        </div>
        <span class="badge" style="<?=$s['status']==='active'?'background:rgba(74,158,106,.12);color:#2d6a40':'background:rgba(93,123,111,.12);color:var(--ds)'?>"><?=$s['status']==='active'?'🚀 TT':'🏆 Xong'?></span>
      </div>

      <div class="small mb-2">
        <i class="bi bi-briefcase me-1 text-muted"></i><strong><?=htmlspecialchars($s['title'])?></strong>
        <span class="text-muted ms-1">@ <?=htmlspecialchars($s['company_name'])?></span>
      </div>

      <?php if($s['j_start']||$s['j_end']): ?>
      <div class="small text-muted mb-2"><i class="bi bi-calendar3 me-1"></i><?=$s['j_start']?date('d/m/Y',strtotime($s['j_start'])):''?> → <?=$s['j_end']?date('d/m/Y',strtotime($s['j_end'])):'?'?></div>
      <?php endif; ?>

      <div class="d-flex justify-content-between align-items-center mb-2">
        <div class="small"><span style="color:<?=$rc?>;font-weight:600"><?=$rl?></span></div>
        <?php if($s['overall_score']??0): ?><span class="badge bg-primary">Điểm DN: <?=number_format($s['overall_score'],1)?>/10</span><?php endif; ?>
      </div>

      <div class="d-flex gap-2 mt-2">
        <a href="?view=<?=$s['registration_id']?>" class="btn btn-primary btn-sm flex-fill">
          <i class="bi bi-eye me-1"></i>Xem chi tiết
        </a>
        <a href="<?=BASE_PATH?>/modules/messages/lecturer_chat.php?student_uid=<?=$s['s_user_id']?>" class="btn btn-secondary btn-sm flex-fill">
          <i class="bi bi-chat-dots-fill me-1"></i>Nhắn tin
        </a>
        <?php if(($s['report_status']??'')==='pending'): ?>
        <a href="<?=BASE_PATH?>/modules/reports/review.php" class="btn btn-warning btn-sm" title="Duyệt báo cáo"><i class="bi bi-file-earmark-check"></i></a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>
<?php endif; ?>
<?php include '../../includes/footer.php'; ?>
