<?php // View: profiles/student_list — nhận $students, $search ?>
<?php include BASE_PATH_FS . 'includes/header.php'; ?>
<div class="ph fu"><div><h4><i class="bi bi-people-fill me-2"></i>Hồ sơ Sinh viên</h4><div class="ph-sub">Tổng: <?=count($students)?></div></div></div>
<?php showFlash(); ?>
<div class="card mb-3 fu1"><div class="card-body py-2">
  <form method="GET" class="d-flex gap-2">
    <input type="text" name="q" class="form-control" placeholder="🔍 Tên, mã SV, email..." value="<?=htmlspecialchars($search)?>">
    <button type="submit" class="btn btn-primary px-4">Tìm</button>
    <?php if($search): ?><a href="list.php" class="btn btn-secondary">Xóa</a><?php endif; ?>
  </form>
</div></div>
<div class="card tc fu2"><div class="card-body p-0"><table class="table mb-0">
  <thead><tr><th>#</th><th>Sinh viên</th><th>Email</th><th>Mã SV</th><th>Chuyên ngành</th><th class="text-center">GPA</th><th>Ngày ĐK</th><th>Hồ sơ</th></tr></thead>
  <tbody>
  <?php if(empty($students)): ?>
    <tr><td colspan="8" class="text-center py-5 text-muted"><i class="bi bi-people fs-1 d-block mb-2 opacity-25"></i>Không có dữ liệu</td></tr>
  <?php else: foreach($students as $i=>$s):
    $av=$s['avatar']?UPLOAD_URL.'/'.$s['avatar']:null;
  ?>
  <tr>
    <td class="small text-muted"><?=$i+1?></td>
    <td><div class="d-flex align-items-center gap-2">
      <?php if($av): ?><img src="<?=$av?>" style="width:30px;height:30px;border-radius:7px;object-fit:cover">
      <?php else: ?><div class="av"><?=strtoupper(mb_substr($s['full_name'],0,1))?></div><?php endif; ?>
      <div class="fw7 small"><?=htmlspecialchars($s['full_name'])?></div>
    </div></td>
    <td class="small"><?=htmlspecialchars($s['email'])?></td>
    <td class="small fw7"><?=htmlspecialchars($s['student_code']??'—')?></td>
    <td class="small text-muted"><?=htmlspecialchars($s['major']??'—')?></td>
    <td class="text-center"><span class="badge bg-primary"><?=$s['gpa']??'—'?></span></td>
    <td class="small text-muted"><?=date('d/m/Y',strtotime($s['created_at']))?></td>
    <td><span class="badge" style="<?=($s['is_profile_completed']??0)?'background:rgba(74,158,106,.12);color:#2d6a40':'background:rgba(196,154,108,.12);color:#a07040'?>"><?=($s['is_profile_completed']??0)?'✅ Hoàn thiện':'⚠️ Chưa xong'?></span></td>
  </tr>
  <?php endforeach; endif; ?>
  </tbody>
</table></div></div>
<?php include BASE_PATH_FS . 'includes/footer.php'; ?>
