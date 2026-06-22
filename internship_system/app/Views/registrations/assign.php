<?php // View: registrations/assign — nhận $need_create, $no_lecturer, $has_lecturer, $lecturers, $total_need ?>
<?php include BASE_PATH_FS . 'includes/header.php'; ?>
<div class="ph fu">
  <div>
    <h4><i class="bi bi-person-check-fill me-2"></i>Phân công Giảng viên Hướng dẫn</h4>
    <div class="ph-sub"><?=$total_need?> SV cần phân công · <?=count($has_lecturer)?> đã có GVHD</div>
  </div>
</div>
<?php showFlash(); ?>

<?php if(empty($lecturers)): ?>
<div class="alert alert-warning fu">
  <i class="bi bi-exclamation-triangle-fill me-2"></i>
  <strong>Chưa có giảng viên nào!</strong>
  <a href="<?=BASE_PATH?>/users/create_lecturer.php" class="btn btn-warning btn-sm ms-2">Thêm giảng viên ngay</a>
</div>
<?php endif; ?>

<?php
function _renderAssignForm($r, $lecturers, $is_app=false){
    $id_name   = $is_app ? 'application_id' : 'registration_id';
    $id_val    = $is_app ? $r['application_id'] : $r['registration_id'];
    $default_sd = $r['j_start'] ?? $r['start_date'] ?? '';
    $default_ed = $r['j_end']   ?? $r['end_date']   ?? '';
    $av = ($r['s_av']??'') ? UPLOAD_URL.'/'.$r['s_av']
        : 'https://ui-avatars.com/api/?name='.urlencode($r['full_name']).'&background=5D7B6F&color=fff&size=60';
    ?>
    <div class="card h-100" style="border:1.5px solid rgba(196,154,108,.35)"><div class="card-body">
      <div class="d-flex align-items-center gap-3 mb-3">
        <img src="<?=$av?>" style="width:44px;height:44px;border-radius:50%;object-fit:cover;flex-shrink:0">
        <div>
          <div class="fw7"><?=htmlspecialchars($r['full_name'])?></div>
          <div class="small text-muted"><?=htmlspecialchars($r['student_code']??'')?> · GPA: <?=$r['gpa']??'—'?></div>
          <div class="small"><i class="bi bi-briefcase me-1 text-muted"></i><?=htmlspecialchars($r['title'])?> @ <?=htmlspecialchars($r['company_name'])?></div>
        </div>
      </div>
      <?php if($is_app): ?>
      <div class="alert alert-success p-2 mb-2" style="font-size:.78rem;border-radius:8px"><i class="bi bi-trophy-fill me-1"></i>Đậu phỏng vấn — Cần GVHD để bắt đầu TT</div>
      <?php endif; ?>
      <form method="POST"><div class="row g-2">
        <input type="hidden" name="<?=$id_name?>" value="<?=$id_val?>">
        <div class="col-12">
          <label class="form-label fw7">Giảng viên hướng dẫn *</label>
          <select name="lecturer_id" class="form-select form-select-sm" required>
            <option value="">— Chọn GVHD —</option>
            <?php foreach($lecturers as $l): ?>
            <option value="<?=$l['lecturer_id']?>"><?=htmlspecialchars($l['full_name'])?> — <?=htmlspecialchars($l['department']??'Chưa có khoa')?> (<?=$l['sv_count']?> SV đang TT)</option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-6">
          <label class="form-label">Ngày bắt đầu</label>
          <input type="date" name="start_date" class="form-control form-control-sm" value="<?=htmlspecialchars($default_sd)?>">
        </div>
        <div class="col-6">
          <label class="form-label">Ngày kết thúc</label>
          <input type="date" name="end_date" class="form-control form-control-sm" value="<?=htmlspecialchars($default_ed)?>">
        </div>
        <div class="col-12">
          <button type="submit" class="btn btn-primary btn-sm w-100">
            <i class="bi bi-person-check me-1"></i>Phân công GVHD<?=$is_app?' & Bắt đầu TT':''?>
          </button>
        </div>
      </div></form>
    </div></div>
    <?php
}
?>

<?php if(!empty($need_create)): ?>
<div class="card mb-4 fu1" style="border-left:4px solid #2d6a40"><div class="card-body">
  <h5 class="fw8 mb-3" style="color:#2d6a40"><i class="bi bi-trophy-fill me-2"></i>Đậu phỏng vấn — Phân công GVHD & Bắt đầu TT (<?=count($need_create)?>)</h5>
  <div class="row g-3">
    <?php foreach($need_create as $r): ?><div class="col-md-6"><?php _renderAssignForm($r,$lecturers,true); ?></div><?php endforeach; ?>
  </div>
</div></div>
<?php endif; ?>

<?php if(!empty($no_lecturer)): ?>
<div class="card mb-4 fu2" style="border-left:4px solid #a07040"><div class="card-body">
  <h5 class="fw8 mb-3" style="color:#a07040"><i class="bi bi-exclamation-triangle me-2"></i>Đang TT chưa có GVHD (<?=count($no_lecturer)?>)</h5>
  <div class="row g-3">
    <?php foreach($no_lecturer as $r): ?><div class="col-md-6"><?php _renderAssignForm($r,$lecturers,false); ?></div><?php endforeach; ?>
  </div>
</div></div>
<?php endif; ?>

<?php if($total_need===0): ?>
<div class="card mb-3 fu2" style="border-left:4px solid #2d6a40"><div class="card-body">
  <i class="bi bi-check-circle-fill me-2 text-success"></i><span class="fw7">Không có sinh viên nào cần phân công GVHD!</span>
</div></div>
<?php endif; ?>

<?php if(!empty($has_lecturer)): ?>
<div class="card tc fu3">
  <div class="card-header"><span><i class="bi bi-table me-2"></i>Đã phân công (<?=count($has_lecturer)?>)</span></div>
  <div class="card-body p-0"><table class="table mb-0">
    <thead><tr><th>#</th><th>Sinh viên</th><th>Vị trí / DN</th><th>GVHD</th><th>Thời gian</th><th>TT</th><th>Đổi GVHD</th></tr></thead>
    <tbody>
    <?php foreach($has_lecturer as $i=>$r): ?>
    <tr>
      <td class="small text-muted"><?=$i+1?></td>
      <td><div class="fw7 small"><?=htmlspecialchars($r['full_name'])?></div><div class="small text-muted"><?=htmlspecialchars($r['student_code']??'')?></div></td>
      <td><div class="fw7 small"><?=htmlspecialchars($r['title'])?></div><div class="small text-muted"><?=htmlspecialchars($r['company_name'])?></div></td>
      <td><div class="fw7 small"><?=htmlspecialchars($r['lname']??'—')?></div><?php if($r['department']??''): ?><div class="small text-muted"><?=htmlspecialchars($r['department'])?></div><?php endif; ?></td>
      <td class="small text-muted"><?=$r['start_date']?date('d/m/Y',strtotime($r['start_date'])):''?><?php if($r['end_date']??''): ?><br>→ <?=date('d/m/Y',strtotime($r['end_date']))?><?php endif; ?></td>
      <td><span class="badge" style="<?=$r['status']==='active'?'background:rgba(74,158,106,.12);color:#2d6a40':'background:rgba(93,123,111,.12);color:var(--ds)'?>"><?=$r['status']==='active'?'🚀 Đang TT':'🏆 Xong'?></span></td>
      <td>
        <form method="POST" class="d-flex gap-1">
          <input type="hidden" name="registration_id" value="<?=$r['registration_id']?>">
          <select name="lecturer_id" class="form-select form-select-sm" style="min-width:140px">
            <?php foreach($lecturers as $l): ?><option value="<?=$l['lecturer_id']?>" <?=$r['lecturer_id']==$l['lecturer_id']?'selected':''?>><?=htmlspecialchars($l['full_name'])?></option><?php endforeach; ?>
          </select>
          <button type="submit" class="btn btn-warning btn-sm"><i class="bi bi-check"></i></button>
        </form>
      </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table></div>
</div>
<?php endif; ?>
<?php include BASE_PATH_FS . 'includes/footer.php'; ?>
