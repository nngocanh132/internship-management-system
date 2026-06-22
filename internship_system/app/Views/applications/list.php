<?php // View: applications/list — nhận $apps, $counts, $status_f từ controller ?>
<?php include BASE_PATH_FS . 'includes/header.php'; ?>
<div class="ph fu">
  <div><h4><i class="bi bi-clipboard-check-fill me-2"></i>Quản lý Đơn ứng tuyển</h4><div class="ph-sub">Tổng: <?=array_sum($counts)?></div></div>
</div>
<?php showFlash(); ?>

<!-- Filter tabs -->
<div class="d-flex gap-2 mb-3 flex-wrap fu1" style="overflow-x:auto">
  <?php $tabs=[
    ''               =>['Tất cả',array_sum($counts),'rgba(93,123,111,.1)','var(--ds)'],
    'pending_admin'  =>['⏳ Chờ TN duyệt',$counts['pending_admin'],'rgba(196,154,108,.1)','#a07040'],
    'approved_admin' =>['✅ TN đã duyệt',$counts['approved_admin'],'rgba(74,138,150,.1)','#3a8a96'],
    'approved_company'=>['🏢 CTy chấp nhận',$counts['approved_company'],'rgba(74,158,106,.1)','#2d6a40'],
    'interview_passed'=>['🎉 Đậu PV',$counts['interview_passed'],'rgba(74,158,106,.2)','#1a4a2a'],
    'internship_active'=>['🚀 Đang TT',$counts['internship_active'],'rgba(93,123,111,.15)','var(--ds)'],
    'internship_completed'=>['🏆 Hoàn thành',$counts['internship_completed'],'rgba(74,158,106,.2)','#1a4a2a'],
  ];
  foreach($tabs as $val=>[$lbl,$cnt,$bg,$col]): ?>
  <a href="?status=<?=$val?>" style="text-decoration:none;padding:6px 13px;border-radius:50px;white-space:nowrap;
    background:<?=($status_f===$val)?$bg:'rgba(255,255,255,.7)'?>;
    border:1.5px solid <?=($status_f===$val)?$col:'rgba(164,195,162,.25)'?>;
    color:<?=($status_f===$val)?$col:'var(--tm)'?>;
    font-size:.78rem;font-weight:700;transition:all .2s">
    <?=$lbl?> <span style="background:<?=$bg?>;color:<?=$col?>;padding:1px 6px;border-radius:9px;font-size:.68rem"><?=$cnt?></span>
  </a>
  <?php endforeach; ?>
</div>

<div class="card tc fu2">
  <div class="card-header d-flex justify-content-between">
    <span><i class="bi bi-table me-2"></i>Danh sách đơn</span>
    <span class="badge bg-primary"><?=count($apps)?></span>
  </div>
  <div class="card-body p-0">
    <table class="table mb-0">
      <thead><tr><th>#</th><th>Sinh viên</th><th>Vị trí / DN</th><th>GPA</th><th>CV</th><th>Trạng thái</th><th>Ngày nộp</th><th>Thao tác</th></tr></thead>
      <tbody>
      <?php if(empty($apps)): ?>
        <tr><td colspan="8" class="text-center py-5 text-muted"><i class="bi bi-inbox fs-1 d-block mb-2 opacity-25"></i>Không có dữ liệu</td></tr>
      <?php else: foreach($apps as $i=>$a):
        [$lbl,$bg,$c]=appStatusLabel($a['status']);
        $av=$a['s_avatar']?UPLOAD_URL.'/'.$a['s_avatar']:null;
      ?>
      <tr>
        <td class="text-muted small"><?=$i+1?></td>
        <td>
          <div class="d-flex align-items-center gap-2">
            <?php if($av): ?><img src="<?=$av?>" style="width:30px;height:30px;border-radius:7px;object-fit:cover">
            <?php else: ?><div class="av"><?=strtoupper(mb_substr($a['full_name'],0,1))?></div><?php endif; ?>
            <div><div class="fw7" style="font-size:.84rem"><?=htmlspecialchars($a['full_name'])?></div>
            <div class="small text-muted"><?=htmlspecialchars($a['student_code']??'')?></div></div>
          </div>
        </td>
        <td><div class="fw7 small"><?=htmlspecialchars($a['title'])?></div><div class="small text-muted"><?=htmlspecialchars($a['company_name'])?></div></td>
        <td><span class="badge bg-primary"><?=$a['gpa']?></span></td>
        <td><?php if($a['cv_file']): ?><a href="<?=UPLOAD_URL.'/'.$a['cv_file']?>" target="_blank" class="btn btn-secondary btn-sm"><i class="bi bi-file-earmark-pdf"></i></a><?php else: ?>—<?php endif; ?></td>
        <td><span class="badge" style="background:<?=$bg?>;color:<?=$c?>"><?=$lbl?></span></td>
        <td class="small text-muted"><?=date('d/m/Y',strtotime($a['applied_at']))?></td>
        <td><a href="review.php?id=<?=$a['application_id']?>" class="btn btn-primary btn-sm"><i class="bi bi-eye me-1"></i>Xem</a></td>
      </tr>
      <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php include BASE_PATH_FS . 'includes/footer.php'; ?>
