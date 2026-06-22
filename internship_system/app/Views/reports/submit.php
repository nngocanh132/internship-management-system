<?php // View: reports/submit — nhận $reg, $existing, $errors từ controller ?>
<?php include BASE_PATH_FS . 'includes/header.php'; ?>
<div class="ph fu">
  <div><h4><i class="bi bi-file-earmark-arrow-up-fill me-2"></i>Nộp Báo cáo Thực tập</h4></div>
  <a href="<?=getDashboardUrl()?>" class="btn btn-secondary"><i class="bi bi-arrow-left me-1"></i>Quay lại</a>
</div>
<?php if(!empty($errors)): ?><div class="alert alert-danger fu"><ul class="mb-0"><?php foreach($errors as $e):?><li><?=htmlspecialchars($e)?></li><?php endforeach;?></ul></div><?php endif; ?>
<?php showFlash(); ?>

<div class="card mb-4 fu" style="border-left:4px solid var(--ds)"><div class="card-body">
  <div class="row g-3">
    <div class="col-md-4"><div class="small text-muted">Vị trí thực tập</div><div class="fw7"><?=htmlspecialchars($reg['title'])?></div></div>
    <div class="col-md-4"><div class="small text-muted">Doanh nghiệp</div><div class="fw7"><?=htmlspecialchars($reg['company_name'])?></div></div>
    <div class="col-md-4"><div class="small text-muted">Giảng viên hướng dẫn</div><div class="fw7"><?=htmlspecialchars($reg['lecturer_name']??'Chưa phân công')?></div></div>
    <?php if($reg['start_date']||$reg['end_date']): ?>
    <div class="col-md-4"><div class="small text-muted">Thời gian</div><div class="fw7"><?=$reg['start_date']?date('d/m/Y',strtotime($reg['start_date'])):''?> → <?=$reg['end_date']?date('d/m/Y',strtotime($reg['end_date'])):''?></div></div>
    <?php endif; ?>
    <div class="col-md-4"><div class="small text-muted">Trạng thái</div>
      <span class="badge" style="<?=$reg['status']==='completed'?'background:rgba(74,158,106,.12);color:#2d6a40':'background:rgba(93,123,111,.12);color:var(--ds)'?>">
        <?=$reg['status']==='completed'?'🏆 Hoàn thành':'🚀 Đang thực tập'?>
      </span>
    </div>
  </div>
</div></div>

<?php if($existing): ?>
<div class="alert alert-info fu1"><i class="bi bi-info-circle-fill me-2"></i>
  Bạn đã nộp báo cáo lúc <?=date('d/m/Y H:i',strtotime($existing['submitted_at']))?>.
  <?php
  $sc_map=['pending'=>['⏳ Chờ duyệt','#a07040'],'approved'=>['✅ Đã duyệt','#2d6a40'],'rejected'=>['❌ Cần sửa','#9a3030']];
  [$sl,$sc]=$sc_map[$existing['status']]??['—','#5a5a5a'];
  ?><strong style="color:<?=$sc?>"><?=$sl?></strong>
  <?php if($existing['report_file']): ?><a href="<?=UPLOAD_URL.'/'.$existing['report_file']?>" target="_blank" class="btn btn-sm btn-secondary ms-2"><i class="bi bi-file-earmark-pdf me-1"></i>Xem file hiện tại</a><?php endif; ?>
  <?php if($existing['lecturer_comment']): ?>
  <div class="mt-2 p-2" style="background:rgba(74,138,150,.08);border-radius:8px;font-size:.85rem">
    <strong>Nhận xét GVHD:</strong> <?=htmlspecialchars($existing['lecturer_comment'])?>
  </div>
  <?php endif; ?>
</div>
<?php endif; ?>

<div class="card fu2"><div class="card-body">
  <h6 class="fw7 mb-3" style="color:var(--ds)"><i class="bi bi-file-earmark-text me-2"></i>
    <?=$existing?'Cập nhật báo cáo':'Nộp báo cáo lần đầu'?>
  </h6>
  <form method="POST" enctype="multipart/form-data">
    <div class="mb-3">
      <label class="form-label fw7">File báo cáo *</label>
      <input type="file" name="report_file" class="form-control" accept=".pdf,.doc,.docx" <?=$existing?'':'required'?>>
      <div class="small text-muted mt-1">PDF/DOC/DOCX, tối đa 20MB<?=$existing?' (để trống = giữ file cũ)':''?></div>
    </div>
    <div class="alert alert-info" style="font-size:.83rem;border-radius:10px">
      <i class="bi bi-lightbulb-fill me-2"></i>
      <strong>Nội dung báo cáo nên bao gồm:</strong><br>
      1. Tổng quan quá trình thực tập &nbsp;|&nbsp; 2. Công việc đã thực hiện &nbsp;|&nbsp;
      3. Kỹ năng học được &nbsp;|&nbsp; 4. Khó khăn &amp; giải pháp &nbsp;|&nbsp; 5. Nhận xét &amp; đề xuất
    </div>
    <button type="submit" class="btn btn-primary">
      <i class="bi bi-send me-1"></i><?=$existing?'Cập nhật báo cáo':'Nộp báo cáo'?>
    </button>
    <a href="<?=getDashboardUrl()?>" class="btn btn-secondary ms-2">Hủy</a>
  </form>
</div></div>
<?php include BASE_PATH_FS . 'includes/footer.php'; ?>
