<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';
requireRole('company');

$company=$conn->prepare("SELECT company_id FROM companies WHERE contact_email=? AND status='active'");
$company->bind_param('s',$_SESSION['email']); $company->execute();
$cdata=$company->get_result()->fetch_assoc();
$cid=$cdata['company_id']??0;

$regs=[];
if($cid){
    $stmt=$conn->query("SELECT r.*,u.full_name,u.student_code,u.major,p.title,p.required_major
      FROM internship_registrations r JOIN users u ON r.student_id=u.user_id
      JOIN internship_positions p ON r.position_id=p.position_id
      WHERE p.company_id=$cid ORDER BY r.registered_at DESC");
    $regs=$stmt->fetch_all(MYSQLI_ASSOC);
}
?>
<?php include '../../includes/header.php';?>
<div class="page-header fade-up">
  <div>
    <h4><i class="bi bi-people-fill me-2"></i>Sinh viên đăng ký</h4>
    <div class="page-subtitle">Tổng: <?=count($regs)?> đăng ký vào các vị trí của doanh nghiệp</div>
  </div>
</div>
<?php showFlash();?>
<div class="card table-card fade-up-1">
  <div class="card-body p-0">
    <table class="table mb-0">
      <thead><tr><th>#</th><th>Sinh viên</th><th>Ngành</th><th>Vị trí đăng ký</th><th>Ngày đăng ký</th><th>Trạng thái</th></tr></thead>
      <tbody>
      <?php if(empty($regs)):?>
        <tr><td colspan="6" class="text-center py-5 text-muted"><i class="bi bi-people fs-1 d-block mb-2 opacity-25"></i>Chưa có sinh viên đăng ký</td></tr>
      <?php else: foreach($regs as $i=>$r):
        $sc=match($r['status']){'pending'=>['rgba(196,154,108,.14)','#8a5a20','Chờ duyệt'],'approved'=>['rgba(74,158,106,.14)','#2d6a40','Đã duyệt'],'rejected'=>['rgba(192,96,80,.14)','#9a3030','Từ chối'],'cancelled'=>['rgba(160,160,160,.12)','#5a5a5a','Đã hủy'],default=>['rgba(160,160,160,.12)','#5a5a5a',$r['status']]};
      ?>
      <tr>
        <td class="text-muted small"><?=$i+1?></td>
        <td>
          <div class="d-flex align-items-center gap-2">
            <div class="avatar" style="width:30px;height:30px;font-size:.72rem"><?=strtoupper(substr($r['full_name'],0,1))?></div>
            <div>
              <div class="fw-700" style="font-size:.875rem"><?=htmlspecialchars($r['full_name'])?></div>
              <div class="small text-muted"><?=htmlspecialchars($r['student_code']??'')?></div>
            </div>
          </div>
        </td>
        <td class="small text-muted"><?=htmlspecialchars($r['major']??'—')?></td>
        <td class="fw-600 small"><?=htmlspecialchars($r['title'])?></td>
        <td class="small text-muted"><?=date('d/m/Y',strtotime($r['registered_at']))?></td>
        <td><span class="badge" style="background:<?=$sc[0]?>;color:<?=$sc[1]?>"><?=$sc[2]?></span></td>
      </tr>
      <?php endforeach; endif;?>
      </tbody>
    </table>
  </div>
</div>
<?php include '../../includes/footer.php';?>
