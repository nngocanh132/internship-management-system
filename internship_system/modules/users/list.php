<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';
requireRole('admin');

$role_f=sanitize($_GET['role']??'');
$search=sanitize($_GET['q']??'');
$sql="SELECT u.*,
  COALESCE(sp.full_name,cp.company_name,lp.full_name,'Admin') AS display_name,
  sp.student_code,sp.gpa,sp.major,sp.avatar AS s_av,
  cp.company_name,cp.logo,
  lp.department
  FROM users u
  LEFT JOIN student_profiles sp ON u.user_id=sp.user_id AND u.role='student'
  LEFT JOIN company_profiles cp ON u.user_id=cp.user_id AND u.role='company'
  LEFT JOIN lecturer_profiles lp ON u.user_id=lp.user_id AND u.role='lecturer'
  WHERE 1=1";
$p=[]; $t='';
if($role_f){$sql.=" AND u.role=?";$p[]=$role_f;$t='s';}
if($search){$sql.=" AND (sp.full_name LIKE ? OR cp.company_name LIKE ? OR lp.full_name LIKE ? OR u.email LIKE ?)";$like="%$search%";$p=array_merge($p,[$like,$like,$like,$like]);$t.='ssss';}
$sql.=" ORDER BY u.created_at DESC";
$st=$conn->prepare($sql); if($p) $st->bind_param($t,...$p); $st->execute();
$users=$st->get_result()->fetch_all(MYSQLI_ASSOC);
$counts=[];
foreach(['student','company','lecturer','admin'] as $r)
    $counts[$r]=$conn->query("SELECT COUNT(*) c FROM users WHERE role='$r'")->fetch_assoc()['c'];
?>
<?php include '../../includes/header.php'; ?>
<div class="ph fu">
  <div><h4><i class="bi bi-people-fill me-2"></i>Quản lý Người dùng</h4><div class="ph-sub">Tổng: <?=array_sum($counts)?></div></div>
  <a href="create_lecturer.php" class="btn btn-primary"><i class="bi bi-person-plus me-1"></i>Thêm Giảng viên</a>
</div>
<?php showFlash(); ?>
<div class="d-flex gap-2 mb-3 flex-wrap fu1">
  <?php $tc=[''=> ['Tất cả',array_sum($counts)],'student'=>['Sinh viên',$counts['student']],'company'=>['Doanh nghiệp',$counts['company']],'lecturer'=>['Giảng viên',$counts['lecturer']],'admin'=>['Admin',$counts['admin']]];
  $tc_c=[''=>'var(--ds)','student'=>'#2d6a40','company'=>'#a07040','lecturer'=>'#3a8a96','admin'=>'#9a3030'];
  foreach($tc as $val=>[$lbl,$cnt]): $c=$tc_c[$val]; ?>
  <a href="?role=<?=$val?>" style="text-decoration:none;padding:6px 14px;border-radius:50px;background:<?=($role_f===$val)?'rgba(93,123,111,.1)':'rgba(255,255,255,.7)'?>;border:1.5px solid <?=($role_f===$val)?$c:'rgba(164,195,162,.25)'?>;color:<?=($role_f===$val)?$c:'var(--tm)'?>;font-size:.78rem;font-weight:700">
    <?=$lbl?> <span style="background:rgba(0,0,0,.07);padding:1px 6px;border-radius:9px;font-size:.67rem"><?=$cnt?></span>
  </a>
  <?php endforeach; ?>
</div>
<div class="card mb-3 fu2"><div class="card-body py-2">
  <form method="GET" class="d-flex gap-2">
    <input type="hidden" name="role" value="<?=htmlspecialchars($role_f)?>">
    <input type="text" name="q" class="form-control" placeholder="🔍 Tên, email..." value="<?=htmlspecialchars($search)?>">
    <button type="submit" class="btn btn-primary px-4">Tìm</button>
    <?php if($search): ?><a href="?role=<?=$role_f?>" class="btn btn-secondary">Xóa</a><?php endif; ?>
  </form>
</div></div>
<div class="card tc fu3"><div class="card-body p-0">
  <table class="table mb-0">
    <thead><tr><th>#</th><th>Người dùng</th><th>Email</th><th>Vai trò</th><th>Thông tin thêm</th><th>Ngày đăng ký</th><th>Hồ sơ</th></tr></thead>
    <tbody>
    <?php if(empty($users)): ?><tr><td colspan="7" class="text-center py-5 text-muted"><i class="bi bi-people fs-1 d-block mb-2 opacity-25"></i>Không có dữ liệu</td></tr>
    <?php else: foreach($users as $i=>$u):
      $av=$u['s_av']?UPLOAD_URL.'/'.$u['s_av']:($u['logo']?UPLOAD_URL.'/'.$u['logo']:null);
      $rb=['student'=>['Sinh viên','rgba(74,158,106,.12)','#2d6a40'],'company'=>['Doanh nghiệp','rgba(196,154,108,.12)','#a07040'],'lecturer'=>['Giảng viên','rgba(74,138,150,.12)','#3a8a96'],'admin'=>['Admin','rgba(192,96,80,.12)','#9a3030']];
      [$rl,$rbb,$rc]=$rb[$u['role']]??[$u['role'],'rgba(160,160,160,.1)','#5a5a5a'];
    ?>
    <tr>
      <td class="small text-muted"><?=$i+1?></td>
      <td>
        <div class="d-flex align-items-center gap-2">
          <?php if($av): ?><img src="<?=$av?>" style="width:32px;height:32px;border-radius:8px;object-fit:cover;flex-shrink:0">
          <?php else: ?><div class="av"><?=strtoupper(mb_substr($u['display_name'],0,1))?></div><?php endif; ?>
          <span class="fw7 small"><?=htmlspecialchars($u['display_name'])?></span>
        </div>
      </td>
      <td class="small"><?=htmlspecialchars($u['email'])?></td>
      <td><span class="badge" style="background:<?=$rbb?>;color:<?=$rc?>"><?=$rl?></span></td>
      <td class="small text-muted">
        <?php if($u['role']==='student'&&$u['student_code']): ?><div>MSV: <?=htmlspecialchars($u['student_code'])?></div><?php if($u['gpa']): ?><div>GPA: <strong><?=$u['gpa']?></strong> · <?=htmlspecialchars($u['major']??'')?></div><?php endif; ?>
        <?php elseif($u['role']==='lecturer'&&$u['department']): ?><?=htmlspecialchars($u['department'])?>
        <?php else: ?>—<?php endif; ?>
      </td>
      <td class="small text-muted"><?=date('d/m/Y',strtotime($u['created_at']))?></td>
      <td><span class="badge" style="<?=$u['is_profile_completed']?'background:rgba(74,158,106,.12);color:#2d6a40':'background:rgba(196,154,108,.12);color:#a07040'?>"><?=$u['is_profile_completed']?'✅':'⚠️'?></span></td>
    </tr>
    <?php endforeach; endif; ?>
    </tbody>
  </table>
</div></div>
<?php include '../../includes/footer.php'; ?>
