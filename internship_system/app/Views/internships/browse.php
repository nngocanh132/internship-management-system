<?php // View: internships/browse — nhận $jobs, $search, $has_reg, $reg_completed từ controller ?>
<?php include BASE_PATH_FS . 'includes/header.php'; ?>
<div class="ph fu">
  <div><h4><i class="bi bi-search me-2"></i>Tìm vị trí Thực tập</h4><div class="ph-sub"><?=count($jobs)?> vị trí đang mở</div></div>
</div>
<?php showFlash(); ?>
<div class="card mb-3 fu1"><div class="card-body py-3">
  <form method="GET" class="d-flex gap-2">
    <div class="input-group">
      <span class="input-group-text igt" style="border-radius:9px 0 0 9px;border:1.5px solid rgba(164,195,162,.32);background:var(--wc);color:var(--ds)"><i class="bi bi-search"></i></span>
      <input type="text" name="q" class="form-control" style="border-radius:0 9px 9px 0" placeholder="Tên vị trí, công ty, mô tả..." value="<?=htmlspecialchars($search)?>">
    </div>
    <button type="submit" class="btn btn-primary px-4">Tìm</button>
    <?php if($search): ?><a href="browse.php" class="btn btn-secondary">Xóa</a><?php endif; ?>
  </form>
</div></div>

<?php if(empty($jobs)): ?>
<div class="card text-center py-5 fu2"><div class="card-body">
  <i class="bi bi-briefcase" style="font-size:3rem;color:var(--tl)"></i>
  <h5 class="mt-3 fw7">Không tìm thấy vị trí nào</h5>
  <p class="text-muted">Thử tìm kiếm với từ khóa khác.</p>
</div></div>
<?php else: ?>
<?php if($has_reg): ?>
<div class="alert <?=$reg_completed?'alert-success':'alert-info'?> fu1">
  <?php if($reg_completed): ?>
  <i class="bi bi-trophy-fill me-2"></i><strong>Bạn đã hoàn thành kỳ thực tập.</strong> Không thể nộp đơn mới.
  <?php else: ?>
  <i class="bi bi-briefcase-fill me-2"></i><strong>Bạn đang trong kỳ thực tập.</strong> Không thể nộp đơn mới cho đến khi hoàn thành.
  <?php endif; ?>
  <a href="<?=BASE_PATH?>/registrations/my_internship.php" class="btn btn-sm btn-primary ms-2">Xem thực tập</a>
</div>
<?php endif; ?>
<div class="row g-3 fu2">
<?php foreach($jobs as $i=>$j):
  $logoUrl=$j['logo']?UPLOAD_URL.'/'.$j['logo']:'https://ui-avatars.com/api/?name='.urlencode($j['company_name']).'&background=A4C3A2&color=2A3F38&size=60&bold=true';
  $applied=$j['my_app']?true:false;
  $full=($j['applied_count']>=$j['quantity']);
?>
<div class="col-md-6 col-lg-4" style="animation:fadeUp .32s <?=$i*.04?>s ease both">
  <div class="card h-100" style="border:1.5px solid rgba(164,195,162,.2)">
    <div class="card-body d-flex flex-column">
      <div class="d-flex align-items-center gap-3 mb-3">
        <img src="<?=$logoUrl?>" style="width:46px;height:46px;border-radius:11px;object-fit:cover;flex-shrink:0">
        <div>
          <div style="font-size:.78rem;color:var(--tl);font-weight:600"><?=htmlspecialchars($j['company_name'])?></div>
          <?php if($j['c_address']): ?><div style="font-size:.72rem;color:var(--tl)"><i class="bi bi-geo-alt me-1"></i><?=htmlspecialchars($j['c_address'])?></div><?php endif; ?>
        </div>
      </div>
      <h6 class="fw8 mb-2" style="font-size:.92rem;color:var(--td)"><?=htmlspecialchars($j['title'])?></h6>
      <?php if($j['description']): ?>
      <p style="font-size:.78rem;color:var(--tl);flex:1;margin-bottom:10px"><?=htmlspecialchars(mb_substr($j['description'],0,120))?>…</p>
      <?php endif; ?>
      <div class="d-flex flex-wrap gap-1 mb-3">
        <?php if($j['location']): ?><span class="badge" style="background:rgba(93,123,111,.1);color:var(--ds)"><i class="bi bi-geo-alt me-1"></i><?=htmlspecialchars($j['location'])?></span><?php endif; ?>
        <?php if($j['start_date']): ?><span class="badge" style="background:rgba(196,154,108,.1);color:#a07040"><i class="bi bi-calendar me-1"></i><?=date('m/Y',strtotime($j['start_date']))?></span><?php endif; ?>
        <span class="badge" style="background:rgba(74,158,106,.1);color:#2d6a40"><?=$j['applied_count']?>/<?=$j['quantity']?> chỗ</span>
      </div>
      <div class="mt-auto">
        <?php if($applied): ?>
        <span class="badge bg-success w-100" style="padding:8px;font-size:.8rem"><i class="bi bi-check-circle-fill me-1"></i>Đã nộp đơn</span>
        <?php elseif($has_reg): ?>
        <span class="badge w-100" style="padding:8px;font-size:.8rem;background:rgba(160,160,160,.15);color:var(--tl)"><i class="bi bi-lock-fill me-1"></i><?=$reg_completed?'Đã hoàn thành TT':'Đang thực tập'?></span>
        <?php elseif($full): ?>
        <span class="badge bg-secondary w-100" style="padding:8px;font-size:.8rem">Hết chỗ</span>
        <?php else: ?>
        <a href="<?=BASE_PATH?>/applications/apply.php?internship_id=<?=$j['internship_id']?>" class="btn btn-primary w-100 btn-sm">
          <i class="bi bi-send me-1"></i>Nộp đơn ứng tuyển
        </a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>
<?php include BASE_PATH_FS . 'includes/footer.php'; ?>
