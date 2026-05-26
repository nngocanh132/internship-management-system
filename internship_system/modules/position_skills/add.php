<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';
requireRole(['admin','lecturer']);
$errors=[];
$positions=$conn->query("SELECT p.position_id,p.title,c.name AS company_name FROM internship_positions p JOIN companies c ON p.company_id=c.company_id ORDER BY p.title")->fetch_all(MYSQLI_ASSOC);
$skills=$conn->query("SELECT skill_id,skill_name FROM skills ORDER BY skill_name")->fetch_all(MYSQLI_ASSOC);
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $position_id=(int)($_POST['position_id']??0);
    $skill_ids=array_map('intval',$_POST['skill_ids']??[]);
    if ($position_id<=0) $errors[]='Vui lòng chọn vị trí.';
    if (empty($skill_ids)) $errors[]='Vui lòng chọn ít nhất một kỹ năng.';
    if (empty($errors)) {
        $inserted=0; $skipped=0;
        $chk=$conn->prepare("SELECT position_skill_id FROM position_skills WHERE position_id=? AND skill_id=?");
        $ins=$conn->prepare("INSERT INTO position_skills (position_id,skill_id) VALUES (?,?)");
        foreach ($skill_ids as $sid) {
            $chk->bind_param('ii',$position_id,$sid); $chk->execute();
            if ($chk->get_result()->num_rows>0) { $skipped++; continue; }
            $ins->bind_param('ii',$position_id,$sid); $ins->execute(); $inserted++;
        }
        $msg="Đã gán <strong>$inserted</strong> kỹ năng.";
        if ($skipped>0) $msg.=" ($skipped đã tồn tại, bỏ qua.)";
        setFlash($inserted>0?'success':'warning',$msg);
        redirect('list.php?position_id='.$position_id);
    }
}
$sel_pos=(int)($_POST['position_id']??$_GET['position_id']??0);
$sel_skills=array_map('intval',$_POST['skill_ids']??[]);
$skill_colors=['#a78bfa','#34d399','#60a5fa','#f472b6','#fb923c','#38bdf8','#4ade80'];
?>
<?php include '../../includes/header.php'; ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4><i class="bi bi-tags-fill me-2" style="color:#34d399"></i>Gán Kỹ năng cho Vị trí</h4>
    <a href="list.php" class="btn btn-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Quay lại</a>
</div>
<?php if ($errors): ?><div class="alert alert-danger"><ul class="mb-0"><?php foreach($errors as $e): ?><li><?= $e ?></li><?php endforeach; ?></ul></div><?php endif; ?>
<div class="card"><div class="card-body">
    <form method="POST"><div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Vị trí thực tập <span class="text-danger">*</span></label>
            <select name="position_id" class="form-select" required>
                <option value="">— Chọn vị trí —</option>
                <?php foreach ($positions as $p): ?>
                <option value="<?= $p['position_id'] ?>" <?= $sel_pos===$p['position_id']?'selected':'' ?>><?= htmlspecialchars($p['title']) ?> (<?= htmlspecialchars($p['company_name']) ?>)</option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-12">
            <label class="form-label">Kỹ năng yêu cầu <span class="text-danger">*</span></label>
            <?php if (empty($skills)): ?>
            <div class="alert alert-warning">Chưa có kỹ năng. <a href="../skills/add.php">Thêm kỹ năng</a> trước.</div>
            <?php else: ?>
            <div class="row g-2">
                <?php foreach ($skills as $idx=>$s): $sc=$skill_colors[$idx%count($skill_colors)]; $checked=in_array($s['skill_id'],$sel_skills); ?>
                <div class="col-md-3 col-sm-4 col-6">
                    <label class="d-flex align-items-center gap-2 p-2 rounded" style="border:1.5px solid <?= $checked?$sc:'#e2e8f0' ?>;background:<?= $checked?$sc.'18':'#fff' ?>;cursor:pointer;transition:all .15s">
                        <input type="checkbox" name="skill_ids[]" value="<?= $s['skill_id'] ?>" <?= $checked?'checked':'' ?> style="accent-color:<?= $sc ?>">
                        <span style="font-size:.82rem;font-weight:500"><?= htmlspecialchars($s['skill_name']) ?></span>
                    </label>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div><hr>
    <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Lưu</button>
    <a href="list.php" class="btn btn-secondary ms-2">Hủy</a>
    </form>
</div></div>
<?php include '../../includes/footer.php'; ?>
