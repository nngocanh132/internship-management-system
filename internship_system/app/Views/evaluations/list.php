<?php // View: evaluations/list — nhận $evals, $role từ controller ?>
<?php include BASE_PATH_FS . 'includes/header.php'; ?>
<div class="ph fu">
  <div><h4><i class="bi bi-star-fill me-2"></i>Đánh giá Thực tập</h4><div class="ph-sub">Tổng: <?=count($evals)?></div></div>
  <?php if($role==='company'): ?><a href="add.php" class="btn btn-primary"><i class="bi bi-plus-circle me-1"></i>Thêm đánh giá</a><?php endif; ?>
</div>
<?php showFlash(); ?>

<?php if(empty($evals)): ?>
<div class="card text-center py-5 fu1"><div class="card-body">
  <i class="bi bi-star" style="font-size:3rem;color:var(--tl)"></i>
  <h5 class="mt-3 fw7">Chưa có đánh giá</h5>
  <?php if($role==='company'): ?><p class="text-muted">Đánh giá sinh viên sau khi kỳ thực tập kết thúc.</p><a href="add.php" class="btn btn-primary">Thêm đánh giá</a><?php endif; ?>
</div></div>
<?php else: ?>
<div class="row g-3 fu1">
<?php foreach($evals as $i=>$e):
  $score  = $e['overall_score'] ?? 0;
  $scolor = $score>=8?'#2d6a40':($score>=6?'var(--ds)':($score>=4?'#a07040':'#9a3030'));
  $av     = $e['s_av'] ? UPLOAD_URL.'/'.$e['s_av'] : 'https://ui-avatars.com/api/?name='.urlencode($e['full_name']).'&background=5D7B6F&color=fff&size=60';
?>
<div class="col-md-6" style="animation:fadeUp .32s <?=$i*.04?>s ease both">
  <div class="card h-100" style="border:1.5px solid rgba(164,195,162,.2)">
    <div class="card-body">
      <div class="d-flex align-items-center gap-3 mb-3">
        <img src="<?=$av?>" style="width:46px;height:46px;border-radius:50%;object-fit:cover;flex-shrink:0">
        <div style="flex:1">
          <div class="fw7"><?=htmlspecialchars($e['full_name'])?></div>
          <div class="small text-muted"><?=htmlspecialchars($e['title'])?> @ <?=htmlspecialchars($e['company_name'])?></div>
        </div>
        <div class="text-end">
          <div style="font-size:2.2rem;font-weight:800;color:<?=$scolor?>;line-height:1"><?=number_format($score,1)?></div>
          <div class="small text-muted">/10</div>
        </div>
      </div>
      <div class="row g-2 mb-3">
        <?php foreach(['technical_skill'=>'🔧 Kỹ thuật','teamwork'=>'👥 Nhóm','communication'=>'💬 Giao tiếp','attitude'=>'😊 Thái độ'] as $k=>$l): ?>
        <div class="col-6">
          <div class="small text-muted mb-1"><?=$l?></div>
          <div class="d-flex align-items-center gap-2">
            <div style="flex:1;height:6px;background:rgba(164,195,162,.2);border-radius:3px;overflow:hidden">
              <div style="height:6px;background:linear-gradient(90deg,var(--ds),var(--sg));border-radius:3px;width:<?=($e[$k]??0)*10?>%"></div>
            </div>
            <span class="fw7 small"><?=$e[$k]??0?></span>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php if($e['comment']): ?>
      <div style="background:rgba(164,195,162,.07);border-radius:8px;padding:9px 12px;font-size:.82rem;color:var(--tm)"><?=nl2br(htmlspecialchars($e['comment']))?></div>
      <?php endif; ?>
      <div class="small text-muted mt-2"><?=date('d/m/Y',strtotime($e['evaluated_at']))?></div>
    </div>
  </div>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>
<?php include BASE_PATH_FS . 'includes/footer.php'; ?>
