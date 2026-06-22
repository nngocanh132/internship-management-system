<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';
requireRole('lecturer');

$uid=$_SESSION['user_id'];
$lq=$conn->prepare("SELECT lecturer_id FROM lecturer_profiles WHERE user_id=?");
$lid=0;
if($lq){ $lq->bind_param('i',$uid); $lq->execute(); $lid=$lq->get_result()->fetch_assoc()['lecturer_id']??0; }

if($_SERVER['REQUEST_METHOD']==='POST'){
    $rid=(int)($_POST['report_id']??0);
    $comment=sanitize($_POST['comment']??'');
    $action=sanitize($_POST['action']??'');
    $status=$action==='approve'?'approved':'rejected';
    $u=$conn->prepare("UPDATE internship_reports SET status=?,lecturer_comment=?,reviewed_at=NOW() WHERE report_id=? AND registration_id IN (SELECT registration_id FROM internship_registrations WHERE lecturer_id=?)");
    if($u){ $u->bind_param('ssii',$status,$comment,$rid,$lid); $u->execute(); }
    if($action==='approve'){
        // Hoàn thành kỳ thực tập
        $uc=$conn->prepare("UPDATE internship_registrations SET status='completed' WHERE registration_id=(SELECT registration_id FROM internship_reports WHERE report_id=?)");
        if($uc){ $uc->bind_param('i',$rid); $uc->execute(); }
        setFlash('success','✅ Đã duyệt báo cáo — Kỳ thực tập đã hoàn thành!');
    } else {
        setFlash('info','📝 Đã yêu cầu sinh viên chỉnh sửa báo cáo.');
    }
    redirect('review.php');
}

$rows=[];
if($lid){
    $rq=$conn->prepare("SELECT rp.*,sp.full_name,sp.student_code,sp.avatar AS s_av,cp.company_name,i.title
      FROM internship_reports rp
      JOIN internship_registrations ir ON rp.registration_id=ir.registration_id
      JOIN student_profiles sp ON ir.student_id=sp.student_id
      JOIN internships i ON ir.internship_id=i.internship_id
      JOIN company_profiles cp ON ir.company_id=cp.company_id
      WHERE ir.lecturer_id=?
      ORDER BY rp.submitted_at DESC");
    if($rq){ $rq->bind_param('i',$lid); $rq->execute(); $rows=$rq->get_result()->fetch_all(MYSQLI_ASSOC); }
}
?>
<?php include '../../includes/header.php'; ?>
<div class="ph fu"><div><h4><i class="bi bi-file-earmark-check-fill me-2"></i>Duyệt Báo cáo Sinh viên</h4><div class="ph-sub">Tổng: <?=count($rows)?> báo cáo</div></div></div>
<?php showFlash(); ?>
<?php if(empty($rows)): ?>
<div class="card text-center py-5 fu1"><div class="card-body"><i class="bi bi-file-earmark fs-1 text-muted opacity-25"></i><h5 class="mt-2 fw7">Chưa có báo cáo nào</h5></div></div>
<?php else: foreach($rows as $i=>$r):
  $sc_map=['pending'=>['⏳ Chờ duyệt','rgba(196,154,108,.12)','#a07040'],'approved'=>['✅ Đã duyệt','rgba(74,158,106,.12)','#2d6a40'],'rejected'=>['❌ Cần sửa','rgba(192,96,80,.12)','#9a3030']];
  [$sl,$sb,$sc]=$sc_map[$r['status']]??['—','rgba(160,160,160,.1)','#5a5a5a'];
  $av=$r['s_av']?UPLOAD_URL.'/'.$r['s_av']:'https://ui-avatars.com/api/?name='.urlencode($r['full_name']).'&background=5D7B6F&color=fff&size=60';
?>
<div class="card mb-3 fu" style="border-left:4px solid <?=$sc?>;animation-delay:<?=$i*.04?>s">
  <div class="card-body">
    <div class="row align-items-start g-3">
      <div class="col-md-5">
        <div class="d-flex align-items-center gap-3 mb-2">
          <img src="<?=$av?>" style="width:44px;height:44px;border-radius:50%;object-fit:cover">
          <div>
            <div class="fw7"><?=htmlspecialchars($r['full_name'])?></div>
            <div class="small text-muted"><?=htmlspecialchars($r['student_code']??'')?></div>
          </div>
        </div>
        <div class="small"><i class="bi bi-briefcase me-2 text-muted"></i><?=htmlspecialchars($r['title'])?></div>
        <div class="small text-muted"><i class="bi bi-building me-2"></i><?=htmlspecialchars($r['company_name'])?></div>
        <div class="small text-muted mt-1"><i class="bi bi-clock me-2"></i>Nộp: <?=date('d/m/Y H:i',strtotime($r['submitted_at']))?></div>
        <span class="badge mt-2" style="background:<?=$sb?>;color:<?=$sc?>"><?=$sl?></span>
      </div>
      <div class="col-md-7">
        <?php if($r['report_file']): ?>
        <a href="<?=UPLOAD_URL.'/'.$r['report_file']?>" target="_blank" class="btn btn-primary w-100 mb-3">
          <i class="bi bi-file-earmark-pdf me-2"></i>Mở & đọc báo cáo
        </a>
        <?php endif; ?>
        <?php if($r['status']==='pending'): ?>
        <form method="POST">
          <input type="hidden" name="report_id" value="<?=$r['report_id']?>">
          <div class="mb-2">
            <label class="form-label">Nhận xét cho sinh viên</label>
            <textarea name="comment" class="form-control" rows="3" placeholder="Nhận xét về báo cáo..."></textarea>
          </div>
          <div class="d-flex gap-2">
            <button type="submit" name="action" value="approve" class="btn btn-success flex-fill">
              <i class="bi bi-check-lg me-1"></i>Duyệt
            </button>
            <button type="submit" name="action" value="reject" class="btn btn-warning flex-fill">
              <i class="bi bi-arrow-return-left me-1"></i>Yêu cầu sửa
            </button>
          </div>
        </form>
        <?php else: ?>
        <?php if($r['lecturer_comment']): ?>
        <div style="background:rgba(164,195,162,.08);border-radius:9px;padding:12px;font-size:.85rem">
          <strong>Nhận xét của bạn:</strong><br><?=nl2br(htmlspecialchars($r['lecturer_comment']))?>
        </div>
        <?php endif; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
<?php endforeach; endif; ?>
<?php include '../../includes/footer.php'; ?>
