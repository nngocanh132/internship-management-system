<?php // View: users/list — nhận $users, $counts, $role_f, $search ?>
<?php include BASE_PATH_FS . 'includes/header.php'; ?>
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
    <thead><tr><th>#</th><th>Người dùng</th><th>Email</th><th>Vai trò</th><th>Thông tin thêm</th><th>Ngày ĐK</th><th>Hồ sơ</th><th></th></tr></thead>
    <tbody>
    <?php if(empty($users)): ?><tr><td colspan="8" class="text-center py-5 text-muted"><i class="bi bi-people fs-1 d-block mb-2 opacity-25"></i>Không có dữ liệu</td></tr>
    <?php else: foreach($users as $i=>$u):
      $av=$u['s_av']??false?UPLOAD_URL.'/'.$u['s_av']:($u['logo']??false?UPLOAD_URL.'/'.$u['logo']:null);
      $rb=['student'=>['Sinh viên','rgba(74,158,106,.12)','#2d6a40'],'company'=>['Doanh nghiệp','rgba(196,154,108,.12)','#a07040'],'lecturer'=>['Giảng viên','rgba(74,138,150,.12)','#3a8a96'],'admin'=>['Admin','rgba(192,96,80,.12)','#9a3030']];
      [$rl,$rbb,$rc]=$rb[$u['role']]??[$u['role'],'rgba(160,160,160,.1)','#5a5a5a'];
    ?>
    <tr>
      <td class="small text-muted"><?=$i+1?></td>
      <td><div class="d-flex align-items-center gap-2">
        <?php if($av): ?><img src="<?=$av?>" style="width:32px;height:32px;border-radius:8px;object-fit:cover;flex-shrink:0">
        <?php else: ?><div class="av"><?=strtoupper(mb_substr($u['display_name'],0,1))?></div><?php endif; ?>
        <span class="fw7 small"><?=htmlspecialchars($u['display_name'])?></span>
      </div></td>
      <td class="small"><?=htmlspecialchars($u['email'])?></td>
      <td><span class="badge" style="background:<?=$rbb?>;color:<?=$rc?>"><?=$rl?></span></td>
      <td class="small text-muted">
        <?php if($u['role']==='student'&&($u['student_code']??'')): ?>MSV: <?=htmlspecialchars($u['student_code'])?><?php if($u['gpa']??0): ?><br>GPA: <strong><?=$u['gpa']?></strong> · <?=htmlspecialchars($u['major']??'')?><?php endif; ?>
        <?php elseif($u['role']==='lecturer'&&($u['department']??'')): ?><?=htmlspecialchars($u['department'])?>
        <?php else: ?>—<?php endif; ?>
      </td>
      <td class="small text-muted"><?=date('d/m/Y',strtotime($u['created_at']))?></td>
      <td><span class="badge" style="<?=($u['is_profile_completed']??0)?'background:rgba(74,158,106,.12);color:#2d6a40':'background:rgba(196,154,108,.12);color:#a07040'?>"><?=($u['is_profile_completed']??0)?'✅':'⚠️'?></span></td>
      <td><?php if($u['user_id']!==$_SESSION['user_id']): ?>
        <a href="?delete=<?=$u['user_id']?>&role=<?=urlencode($role_f)?>&q=<?=urlencode($search)?>" class="btn btn-danger btn-sm" onclick="return confirm('Xóa người dùng này?\n⚠️ Toàn bộ dữ liệu liên quan sẽ bị xóa!')"><i class="bi bi-trash3-fill"></i></a>
        <?php else: ?><span class="badge" style="background:rgba(160,160,160,.1);color:#888;font-size:.68rem">Bạn</span><?php endif; ?></td>
    </tr>
    <?php endforeach; endif; ?>
    </tbody>
  </table>
</div></div>
<?php include BASE_PATH_FS . 'includes/footer.php'; ?>
