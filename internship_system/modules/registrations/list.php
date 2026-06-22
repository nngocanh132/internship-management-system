<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';
requireRole('admin');

if (isset($_GET['complete'])) {
    $id = (int)$_GET['complete'];
    $u = $conn->prepare("UPDATE internship_registrations SET status='completed' WHERE registration_id=?");
    if ($u) { $u->bind_param('i',$id); $u->execute(); }
    setFlash('success','🏆 Kỳ thực tập hoàn thành!');
    redirect('list.php');
}

$status_f = sanitize($_GET['status'] ?? '');
$sql = "SELECT ir.*,sp.full_name,sp.student_code,sp.gpa,sp.avatar AS s_av,
        cp.company_name,i.title,lp.full_name AS lecturer_name
        FROM internship_registrations ir
        JOIN student_profiles sp ON ir.student_id=sp.student_id
        JOIN internships i ON ir.internship_id=i.internship_id
        JOIN company_profiles cp ON ir.company_id=cp.company_id
        LEFT JOIN lecturer_profiles lp ON ir.lecturer_id=lp.lecturer_id
        WHERE 1=1";
if ($status_f) $sql .= " AND ir.status='".addslashes($status_f)."'";
$sql .= " ORDER BY ir.created_at DESC";

$regs      = safeQuery($conn, $sql);
$cnt_active = safeCount($conn,"SELECT COUNT(*) c FROM internship_registrations WHERE status='active'");
$cnt_done   = safeCount($conn,"SELECT COUNT(*) c FROM internship_registrations WHERE status='completed'");
?>
<?php include '../../includes/header.php'; ?>
<div class="ph fu">
  <div><h4><i class="bi bi-journal-richtext me-2"></i>Quản lý Thực tập</h4><div class="ph-sub">Tổng: <?=$cnt_active+$cnt_done?></div></div>
</div>
<?php showFlash(); ?>

<div class="d-flex gap-2 mb-3 fu1">
  <?php
  $tabs = [
    '' => ['Tất cả', $cnt_active+$cnt_done, 'var(--ds)'],
    'active' => ['🚀 Đang TT', $cnt_active, '#2d6a40'],
    'completed' => ['🏆 Hoàn thành', $cnt_done, 'var(--ds)'],
  ];
  foreach ($tabs as $val => [$lbl,$cnt,$col]):
    $active = ($status_f === $val);
  ?>
  <a href="?status=<?=$val?>" style="text-decoration:none;padding:6px 14px;border-radius:50px;
    background:<?=$active?'rgba(93,123,111,.1)':'rgba(255,255,255,.7)'?>;
    border:1.5px solid <?=$active?$col:'rgba(164,195,162,.25)'?>;
    color:<?=$active?$col:'var(--tm)'?>;font-size:.78rem;font-weight:700">
    <?=$lbl?> <span style="background:rgba(0,0,0,.07);padding:1px 6px;border-radius:9px;font-size:.67rem"><?=$cnt?></span>
  </a>
  <?php endforeach; ?>
</div>

<div class="card tc fu2"><div class="card-body p-0">
  <table class="table mb-0">
    <thead>
      <tr><th>#</th><th>Sinh viên</th><th>Vị trí / DN</th><th>GVHD</th><th>Thời gian</th><th>Trạng thái</th><th>Thao tác</th></tr>
    </thead>
    <tbody>
    <?php if(empty($regs)): ?>
      <tr><td colspan="7" class="text-center py-5 text-muted"><i class="bi bi-journal fs-1 d-block mb-2 opacity-25"></i>Không có dữ liệu</td></tr>
    <?php else: foreach($regs as $i=>$r):
      $av = $r['s_av'] ? UPLOAD_URL.'/'.$r['s_av'] : null;
    ?>
    <tr>
      <td class="small text-muted"><?=$i+1?></td>
      <td>
        <div class="d-flex align-items-center gap-2">
          <?php if($av): ?><img src="<?=$av?>" style="width:30px;height:30px;border-radius:7px;object-fit:cover">
          <?php else: ?><div class="av"><?=strtoupper(mb_substr($r['full_name'],0,1))?></div><?php endif; ?>
          <div>
            <div class="fw7 small"><?=htmlspecialchars($r['full_name'])?></div>
            <div class="small text-muted"><?=htmlspecialchars($r['student_code']??'')?> · GPA: <?=$r['gpa']??'—'?></div>
          </div>
        </div>
      </td>
      <td>
        <div class="fw7 small"><?=htmlspecialchars($r['title'])?></div>
        <div class="small text-muted"><?=htmlspecialchars($r['company_name'])?></div>
      </td>
      <td>
        <?php if($r['lecturer_name']): ?>
          <span class="small"><?=htmlspecialchars($r['lecturer_name'])?></span>
        <?php else: ?>
          <a href="assign.php?reg_id=<?=$r['registration_id']?>" class="btn btn-warning btn-sm"><i class="bi bi-person-plus me-1"></i>Phân công GV</a>
        <?php endif; ?>
      </td>
      <td class="small text-muted">
        <?=$r['start_date']?date('d/m/Y',strtotime($r['start_date'])):''?>
        <?php if($r['end_date']): ?><br>→ <?=date('d/m/Y',strtotime($r['end_date']))?><?php endif; ?>
      </td>
      <td>
        <span class="badge" style="<?=$r['status']==='active'?'background:rgba(74,158,106,.12);color:#2d6a40':'background:rgba(93,123,111,.12);color:var(--ds)'?>">
          <?=$r['status']==='active'?'🚀 Đang TT':'🏆 Hoàn thành'?>
        </span>
      </td>
      <td>
        <div class="d-flex gap-1">
          <?php if(!$r['lecturer_name']): ?>
            <a href="assign.php?reg_id=<?=$r['registration_id']?>" class="btn btn-primary btn-sm" title="Phân công GV"><i class="bi bi-person-check"></i></a>
          <?php endif; ?>
          <?php if($r['status']==='active'): ?>
            <a href="?complete=<?=$r['registration_id']?>" class="btn btn-success btn-sm" onclick="return confirm('Hoàn thành kỳ TT này?')"><i class="bi bi-check-circle"></i></a>
          <?php endif; ?>
        </div>
      </td>
    </tr>
    <?php endforeach; endif; ?>
    </tbody>
  </table>
</div></div>
<?php include '../../includes/footer.php'; ?>
