<?ph
session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';
requireRole('company');

$uid = $_SESSION['user_id'];
$cq  = $conn->prepare("SELECT company_id FROM company_profiles WHERE user_id=?");
$cid = 0;
if ($cq) { $cq->bind_param('i',$uid); $cq->execute(); $cid = $cq->get_result()->fetch_assoc()['company_id'] ?? 0; }

$pending = safeQuery($conn,"SELECT ir.*,sp.full_name,sp.student_code,i.title
  FROM internship_registrations ir
  JOIN student_profiles sp ON ir.student_id=sp.student_id
  JOIN internships i ON ir.internship_id=i.internship_id
  WHERE ir.company_id=$cid
  AND ir.registration_id NOT IN (SELECT registration_id FROM evaluations)
  ORDER BY sp.full_name");

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reg_id  = (int)($_POST['registration_id'] ?? 0);
    $tech    = max(1, min(10, (int)($_POST['technical_skill'] ?? 5)));
    $team    = max(1, min(10, (int)($_POST['teamwork'] ?? 5)));
    $comm    = max(1, min(10, (int)($_POST['communication'] ?? 5)));
    $att     = max(1, min(10, (int)($_POST['attitude'] ?? 5)));
    $comment = sanitize($_POST['comment'] ?? '');

    if (!$reg_id) $errors[] = 'Vui lòng chọn sinh viên.';

    if (empty($errors)) {
        $overall = round(($tech + $team + $comm + $att) / 4, 2);
        $ins = $conn->prepare("INSERT INTO evaluations (registration_id,technical_skill,teamwork,communication,attitude,overall_score,comment) VALUES (?,?,?,?,?,?,?)");
        if ($ins) {
            $ins->bind_param('iiiiiis', $reg_id,$tech,$team,$comm,$att,$overall,$comment);
            if ($ins->execute()) {
                setFlash('success','✅ Đã lưu đánh giá sinh viên!');
                redirect('list.php');
            } else {
                $errors[] = 'Lỗi lưu: '.$conn->error;
            }
        } else {
            $errors[] = 'Lỗi truy vấn: '.$conn->error;
        }
    }
}
?>
<?php include '../../includes/header.php'; ?>
<div class="ph fu">
  <div><h4><i class="bi bi-star-fill me-2"></i>Đánh giá Sinh viên Thực tập</h4></div>
  <a href="list.php" class="btn btn-secondary"><i class="bi bi-arrow-left me-1"></i>Quay lại</a>
</div>
<?php if(!empty($errors)): ?><div class="alert alert-danger fu"><ul class="mb-0"><?php foreach($errors as $e):?><li><?=htmlspecialchars($e)?></li><?php endforeach;?></ul></div><?php endif; ?>
<?php showFlash(); ?>

<?php if(empty($pending)): ?>
<div class="card text-center py-4 fu1"><div class="card-body">
  <i class="bi bi-check-circle-fill" style="font-size:2.5rem;color:#2d6a40"></i>
  <h5 class="mt-2 fw7">Tất cả sinh viên đã được đánh giá!</h5>
  <a href="list.php" class="btn btn-primary mt-2">Xem danh sách đánh giá</a>
</div></div>
<?php else: ?>
<div class="row g-4">
  <div class="col-lg-8">
    <div class="card fu1"><div class="card-body">
      <form method="POST"><div class="row g-3">
        <div class="col-12">
          <label class="form-label">Chọn sinh viên cần đánh giá *</label>
          <select name="registration_id" class="form-select" required>
            <option value="">— Chọn sinh viên —</option>
            <?php foreach($pending as $r): ?>
            <option value="<?=$r['registration_id']?>" <?=($_POST['registration_id']??'')==$r['registration_id']?'selected':''?>>
              <?=htmlspecialchars($r['full_name'].' ('.$r['student_code'].') — '.$r['title'])?>
            </option>
            <?php endforeach; ?>
          </select>
        </div>

        <?php
        $criteria = [
          ['technical_skill','Kỹ năng kỹ thuật','bi-code-slash'],
          ['teamwork','Làm việc nhóm','bi-people-fill'],
          ['communication','Giao tiếp','bi-chat-fill'],
          ['attitude','Thái độ làm việc','bi-emoji-smile-fill'],
        ];
        foreach ($criteria as [$name,$label,$ico]):
          $val = (int)($_POST[$name] ?? 7);
        ?>
        <div class="col-md-6">
          <label class="form-label"><i class="bi <?=$ico?> me-2"></i><?=$label?> (1–10)</label>
          <div class="d-flex align-items-center gap-3">
            <input type="range" name="<?=$name?>" class="form-range" min="1" max="10" value="<?=$val?>"
                   oninput="document.getElementById('v_<?=$name?>').textContent=this.value" style="flex:1">
            <span class="fw7" style="color:var(--ds);min-width:20px;text-align:center" id="v_<?=$name?>"><?=$val?></span>
          </div>
          <div class="d-flex justify-content-between small text-muted"><span>1 — Kém</span><span>10 — Xuất sắc</span></div>
        </div>
        <?php endforeach; ?>

        <div class="col-12">
          <label class="form-label">Nhận xét tổng quát</label>
          <textarea name="comment" class="form-control" rows="4"
                    placeholder="Nhận xét về thái độ, năng lực, sự tiến bộ trong kỳ thực tập..."><?=htmlspecialchars($_POST['comment']??'')?></textarea>
        </div>
      </div>
      <hr>
      <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Lưu đánh giá</button>
      <a href="list.php" class="btn btn-secondary ms-2">Hủy</a>
      </form>
    </div></div>
  </div>
  <div class="col-lg-4">
    <div class="card fu2" style="background:rgba(234,231,214,.3)"><div class="card-body">
      <h6 class="fw7 mb-3" style="color:var(--ds)"><i class="bi bi-info-circle me-2"></i>Thang điểm</h6>
      <?php foreach(['9–10'=>['Xuất sắc','#2d6a40'],'7–8'=>['Tốt','var(--ds)'],'5–6'=>['Khá','#a07040'],'3–4'=>['Trung bình','#888'],'1–2'=>['Yếu','#9a3030']] as $r=>[$l,$c]): ?>
      <div class="d-flex justify-content-between small mb-1">
        <span class="fw7" style="color:<?=$c?>"><?=$r?></span>
        <span class="text-muted"><?=$l?></span>
      </div>
      <?php endforeach; ?>
      <hr>
      <div class="small text-muted"><i class="bi bi-calculator me-2"></i>Điểm tổng = trung bình 4 tiêu chí</div>
    </div></div>
  </div>
</div>
<?php endif; ?>
<?php include '../../includes/footer.php'; ?>
