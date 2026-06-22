<?php // View: applications/review — nhận $a, $others, $lbl, $bg, $c, $av, $clogo từ controller ?>
<?php include BASE_PATH_FS . 'includes/header.php'; ?>
<div class="ph fu">
  <div><h4><i class="bi bi-person-check me-2"></i>Xét duyệt Đơn ứng tuyển</h4></div>
  <a href="list.php" class="btn btn-secondary"><i class="bi bi-arrow-left me-1"></i>Quay lại</a>
</div>
<?php showFlash(); ?>

<div class="row g-4">
  <!-- Student info -->
  <div class="col-md-4">
    <div class="card fu text-center">
      <div class="card-body">
        <img src="<?=$av?>" style="width:90px;height:90px;border-radius:50%;object-fit:cover;border:3px solid var(--sg);margin-bottom:12px">
        <h5 class="fw8"><?=htmlspecialchars($a['full_name'])?></h5>
        <div class="small text-muted"><?=htmlspecialchars($a['email'])?></div>
        <div class="mt-2 d-flex justify-content-center gap-2 flex-wrap">
          <span class="badge bg-primary" style="padding:5px 10px">GPA: <?=$a['gpa']??'—'?></span>
          <span class="badge bg-secondary" style="padding:5px 10px"><?=htmlspecialchars($a['student_code']??'—')?></span>
        </div>
        <div class="mt-3 text-start small">
          <?php if($a['major']): ?><div class="mb-1"><i class="bi bi-mortarboard me-2 text-muted"></i><?=htmlspecialchars($a['major'])?></div><?php endif; ?>
          <?php if($a['phone']): ?><div class="mb-1"><i class="bi bi-phone me-2 text-muted"></i><?=htmlspecialchars($a['phone'])?></div><?php endif; ?>
          <?php if($a['linkedin_url']): ?><div class="mb-1"><i class="bi bi-linkedin me-2 text-muted"></i><a href="<?=htmlspecialchars($a['linkedin_url'])?>" target="_blank">LinkedIn</a></div><?php endif; ?>
        </div>
        <?php if($a['about_me']): ?>
        <div class="mt-3 p-2" style="background:rgba(164,195,162,.08);border-radius:9px;text-align:left;font-size:.78rem;color:var(--tm)"><?=nl2br(htmlspecialchars($a['about_me']))?></div>
        <?php endif; ?>
        <?php if($a['cv_file']): ?>
        <a href="<?=UPLOAD_URL.'/'.$a['cv_file']?>" target="_blank" class="btn btn-primary w-100 mt-3 btn-sm">
          <i class="bi bi-file-earmark-pdf me-1"></i>Xem CV
        </a>
        <?php endif; ?>
      </div>
    </div>
    <?php if(!empty($others)): ?>
    <div class="card mt-3 fu1">
      <div class="card-body">
        <h6 class="fw7 mb-2" style="font-size:.82rem">Các nguyện vọng khác của SV này:</h6>
        <?php foreach($others as $o):
          [$ol,$ob,$oc]=appStatusLabel($o['status']); ?>
        <div class="d-flex justify-content-between align-items-center mb-1">
          <div class="small"><div class="fw7"><?=htmlspecialchars($o['title'])?></div><div style="color:var(--tl);font-size:.72rem"><?=htmlspecialchars($o['company_name'])?></div></div>
          <span class="badge" style="background:<?=$ob?>;color:<?=$oc?>;font-size:.65rem"><?=$ol?></span>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>
  </div>

  <!-- Job info + Decision -->
  <div class="col-md-8">
    <div class="card fu1 mb-3">
      <div class="card-body">
        <div class="d-flex align-items-center gap-3 mb-3">
          <img src="<?=$clogo?>" style="width:48px;height:48px;border-radius:11px;object-fit:cover">
          <div>
            <h5 class="fw8 mb-0"><?=htmlspecialchars($a['title'])?></h5>
            <div class="small text-muted"><?=htmlspecialchars($a['company_name'])?><?=$a['location']?' · '.htmlspecialchars($a['location']):''?></div>
          </div>
          <span class="badge ms-auto" style="background:<?=$bg?>;color:<?=$c?>;padding:6px 12px"><?=$lbl?></span>
        </div>
        <?php if($a['description']): ?><div class="small mb-2"><strong>Mô tả:</strong> <?=nl2br(htmlspecialchars($a['description']))?></div><?php endif; ?>
        <?php if($a['requirements']): ?><div class="small"><strong>Yêu cầu:</strong> <?=nl2br(htmlspecialchars($a['requirements']))?></div><?php endif; ?>
        <?php if($a['start_date']): ?><div class="small text-muted mt-2"><i class="bi bi-calendar me-1"></i><?=date('d/m/Y',strtotime($a['start_date']))?> – <?=date('d/m/Y',strtotime($a['end_date']??$a['start_date']))?> | Số lượng: <?=$a['quantity']?></div><?php endif; ?>
      </div>
    </div>

    <?php if($a['status']==='pending_admin'): ?>
    <div class="card fu2">
      <div class="card-body">
        <h6 class="fw7 mb-3" style="color:var(--ds)"><i class="bi bi-gavel me-2"></i>Quyết định của Trường</h6>
        <div class="alert alert-info mb-3">
          <i class="bi bi-info-circle-fill me-2"></i>
          Sinh viên đăng ký <?=count($others)+1?> vị trí. Hãy chọn <strong>1 vị trí phù hợp nhất</strong> và duyệt đơn đó, từ chối các đơn còn lại.
        </div>
        <form method="POST">
          <div class="mb-3">
            <label class="form-label">Ghi chú (tùy chọn)</label>
            <textarea name="admin_note" class="form-control" rows="3" placeholder="Lý do duyệt/từ chối..."><?=htmlspecialchars($a['admin_note']??'')?></textarea>
          </div>
          <div class="d-flex gap-3">
            <button type="submit" name="action" value="approve" class="btn btn-success flex-fill" onclick="return confirm('Duyệt đơn này và chuyển sang công ty?')">
              <i class="bi bi-check-lg me-2"></i>Duyệt — Chuyển sang công ty
            </button>
            <button type="submit" name="action" value="reject" class="btn btn-danger flex-fill" onclick="return confirm('Từ chối đơn này?')">
              <i class="bi bi-x-lg me-2"></i>Từ chối
            </button>
          </div>
        </form>
      </div>
    </div>
    <?php else: ?>
    <div class="card fu2" style="border-left:4px solid <?=$c?>">
      <div class="card-body">
        <div class="fw7 mb-1">Trạng thái hiện tại: <span style="color:<?=$c?>"><?=$lbl?></span></div>
        <?php if($a['admin_note']): ?><div class="small text-muted">Ghi chú: <?=htmlspecialchars($a['admin_note'])?></div><?php endif; ?>
      </div>
    </div>
    <?php endif; ?>
  </div>
</div>
<?php include BASE_PATH_FS . 'includes/footer.php'; ?>
