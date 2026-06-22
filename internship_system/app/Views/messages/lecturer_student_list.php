<?php // View: messages/lecturer_student_list — nhận $my_students, $uid ?>
<?php include BASE_PATH_FS . 'includes/header.php'; ?>
<div class="ph fu">
  <div><h4><i class="bi bi-chat-dots-fill me-2"></i>Chọn sinh viên để nhắn tin</h4></div>
  <a href="<?=BASE_PATH?>/registrations/my_students.php" class="btn btn-secondary"><i class="bi bi-arrow-left me-1"></i>Quay lại</a>
</div>
<?php if(empty($my_students)): ?>
<div class="card text-center py-5"><div class="card-body">
  <i class="bi bi-people fs-1 text-muted opacity-25"></i>
  <h5 class="mt-2 fw7">Chưa có sinh viên được phân công</h5>
</div></div>
<?php else: ?>
<div class="row g-3 fu1">
<?php foreach($my_students as $i=>$s):
  $av=($s['s_av']??'')?UPLOAD_URL.'/'.$s['s_av']:'https://ui-avatars.com/api/?name='.urlencode($s['full_name']).'&background=5D7B6F&color=fff&size=60';
?>
<div class="col-md-6 col-lg-4" style="animation:fadeUp .32s <?=$i*.04?>s ease both">
  <a href="lecturer_chat.php?student_uid=<?=$s['s_user_id']?>" style="text-decoration:none">
    <div class="card" style="border:1.5px solid <?=$s['unread_from']>0?'var(--ds)':'rgba(164,195,162,.25)'?>;transition:all .2s"
         onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform=''">
      <div class="card-body d-flex align-items-center gap-3">
        <img src="<?=$av?>" style="width:46px;height:46px;border-radius:50%;object-fit:cover;flex-shrink:0">
        <div style="flex:1">
          <div class="fw7"><?=htmlspecialchars($s['full_name'])?></div>
          <div class="small text-muted"><?=htmlspecialchars($s['student_code']??'')?> · GPA: <?=$s['gpa']??'—'?></div>
          <div class="small text-muted"><?=htmlspecialchars($s['title'])?> @ <?=htmlspecialchars($s['company_name'])?></div>
        </div>
        <?php if($s['unread_from']>0): ?><span class="badge bg-primary"><?=$s['unread_from']?></span><?php endif; ?>
      </div>
    </div>
  </a>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>
<?php include BASE_PATH_FS . 'includes/footer.php'; ?>
