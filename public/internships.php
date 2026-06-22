<?php
$base_sys = '/internship-management-system/internship_system';
$base_pub = '/internship-management-system/public';
require_once __DIR__.'/../internship_system/config/database.php';

$search=trim($_GET['q']??''); $loc_f=trim($_GET['loc']??'');
$page_num=max(1,(int)($_GET['page']??1)); $per=9; $offset=($page_num-1)*$per;
$where="i.status='open'"; $params=[]; $types='';
if($search){$where.=" AND (i.title LIKE ? OR cp.company_name LIKE ? OR i.description LIKE ?)";$lk="%$search%";$params[]=$lk;$params[]=$lk;$params[]=$lk;$types.='sss';}
if($loc_f){$where.=" AND (i.location LIKE ? OR cp.address LIKE ?)";$lk2="%$loc_f%";$params[]=$lk2;$params[]=$lk2;$types.='ss';}
$total=0;
$csql="SELECT COUNT(*) c FROM internships i LEFT JOIN company_profiles cp ON i.company_id=cp.company_id WHERE $where";
if($types){$st=$conn->prepare($csql);if($st){$st->bind_param($types,...$params);$st->execute();$total=(int)$st->get_result()->fetch_assoc()['c'];}}
else{$r=$conn->query($csql);$total=$r&&$r!==true?(int)$r->fetch_assoc()['c']:0;}
$tp=max(1,ceil($total/$per));
$jsql="SELECT i.*,cp.company_name,cp.logo,cp.address,cp.website FROM internships i LEFT JOIN company_profiles cp ON i.company_id=cp.company_id WHERE $where ORDER BY i.created_at DESC LIMIT $per OFFSET $offset";
$jobs=[];
if($types){$st2=$conn->prepare($jsql);if($st2){$st2->bind_param($types,...$params);$st2->execute();$res=$st2->get_result();while($row=$res->fetch_assoc())$jobs[]=$row;}}
else{$r2=$conn->query($jsql);if($r2&&$r2!==true)while($row=$r2->fetch_assoc())$jobs[]=$row;}
$locs=[];$r=$conn->query("SELECT DISTINCT location FROM internships WHERE location IS NOT NULL AND location!='' ORDER BY location");if($r&&$r!==true)while($row=$r->fetch_assoc())$locs[]=$row['location'];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Vị trí thực tập — ISchool Internship</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@700;800;900&display=swap" rel="stylesheet">
<style>
:root{--ds:#5D7B6F;--ds2:#3D5A50;--ds3:#2A3F38;--sg:#A4C3A2;--sm:#B0D4B8;--bg:#EAE7D6;--td:#1A2E28;--tm:#4A6058;--tl:#7A9590;}
*,*::before,*::after{box-sizing:border-box}
body{font-family:'Inter',sans-serif;background:var(--bg);color:var(--td);margin:0;padding-top:56px;}
::-webkit-scrollbar{width:5px}::-webkit-scrollbar-thumb{background:var(--sg);border-radius:4px}
/* NAV - defined in _nav.php */
.jc{background:#fff;border:1.5px solid rgba(164,195,162,.2);border-radius:18px;padding:22px;height:100%;display:flex;flex-direction:column;transition:all .22s;}
.jc:hover{transform:translateY(-4px);box-shadow:0 12px 28px rgba(93,123,111,.12);border-color:rgba(93,123,111,.28);}
.tag{background:rgba(93,123,111,.1);color:var(--ds);padding:3px 10px;border-radius:20px;font-size:.7rem;font-weight:600;display:inline-flex;align-items:center;gap:4px;}
.tag-warm{background:rgba(160,107,40,.1);color:#a07040;}
.btn-apply{display:flex;align-items:center;justify-content:center;gap:7px;padding:9px;border-radius:10px;background:linear-gradient(135deg,var(--ds),var(--ds2));color:#fff;font-weight:700;font-size:.83rem;text-decoration:none;transition:opacity .18s;}
.btn-apply:hover{opacity:.88;color:#fff;}
.fc{border-radius:9px;border:1.5px solid rgba(164,195,162,.3);font-size:.86rem;padding:9px 13px;background:#fff;width:100%;transition:all .2s;font-family:'Inter',sans-serif;}
.fc:focus{border-color:var(--ds);box-shadow:0 0 0 3px rgba(93,123,111,.1);outline:none;}
.page-link{border-radius:8px!important;border:1.5px solid rgba(164,195,162,.3)!important;color:var(--ds)!important;margin:0 2px;font-weight:600;font-size:.84rem;}
.page-link:hover{background:rgba(164,195,162,.15)!important;border-color:var(--ds)!important;}
.active .page-link{background:var(--ds)!important;border-color:var(--ds)!important;color:#fff!important;}
</style>
</head>
<body>
<?php require_once __DIR__.'/_nav.php'; ?>

<!-- HERO -->
<section style="background:linear-gradient(148deg,var(--ds3),var(--ds2),#4a7a68);padding:56px 0 44px;position:relative;overflow:hidden;">
  <div style="position:absolute;inset:0;background-image:radial-gradient(rgba(176,212,184,.1) 1px,transparent 1px);background-size:28px 28px;pointer-events:none;"></div>
  <div class="container" style="max-width:1140px;position:relative;">
    <div style="display:flex;align-items:center;gap:8px;margin-bottom:12px;font-size:.78rem;color:rgba(255,255,255,.5);">
      <a href="<?=$base_pub?>/index.php" style="color:rgba(255,255,255,.65);text-decoration:none;"><i class="bi bi-house-fill"></i> Trang chủ</a>
      <i class="bi bi-chevron-right" style="font-size:.6rem;"></i>
      <span style="color:var(--sm);font-weight:600;">Vị trí thực tập</span>
    </div>
    <h1 style="font-family:'Plus Jakarta Sans',sans-serif;font-size:clamp(1.6rem,3.5vw,2.2rem);font-weight:900;color:#fff;margin-bottom:6px;">💼 Vị trí thực tập</h1>
    <p style="color:rgba(255,255,255,.65);font-size:.9rem;margin:0;"><span style="font-weight:700;color:var(--sm);"><?=$total?></span> vị trí đang tuyển dụng</p>
  </div>
  <div style="position:absolute;bottom:0;left:0;right:0;line-height:0;">
    <svg viewBox="0 0 1440 36" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none" style="display:block;width:100%;height:36px;"><path d="M0,36 C480,0 960,36 1440,0 L1440,36 Z" fill="var(--bg)"/></svg>
  </div>
</section>

<div class="container" style="max-width:1140px;padding:32px 16px 56px;">
  <!-- Filter -->
  <form style="background:#fff;border-radius:16px;padding:18px 22px;margin-bottom:26px;box-shadow:0 2px 14px rgba(93,123,111,.08);border:1px solid rgba(164,195,162,.15);" method="GET">
    <div class="row g-3 align-items-end">
      <div class="col-md-6">
        <label style="font-size:.73rem;font-weight:700;color:var(--tl);margin-bottom:4px;display:block;"><i class="bi bi-search me-1"></i>Tìm kiếm</label>
        <div style="position:relative;">
          <input type="text" name="q" class="fc" placeholder="Tên vị trí, công ty..." value="<?=htmlspecialchars($search)?>" style="padding-left:34px;">
          <i class="bi bi-search" style="position:absolute;left:11px;top:50%;transform:translateY(-50%);color:var(--tl);font-size:.8rem;pointer-events:none;"></i>
        </div>
      </div>
      <div class="col-md-3">
        <label style="font-size:.73rem;font-weight:700;color:var(--tl);margin-bottom:4px;display:block;"><i class="bi bi-geo-alt-fill me-1"></i>Địa điểm</label>
        <select name="loc" class="fc" style="appearance:auto;">
          <option value="">Tất cả</option>
          <?php foreach($locs as $l):?><option value="<?=htmlspecialchars($l)?>" <?=$loc_f===$l?'selected':''?>><?=htmlspecialchars($l)?></option><?php endforeach;?>
        </select>
      </div>
      <div class="col-md-2">
        <button type="submit" style="background:linear-gradient(135deg,var(--ds),var(--ds2));color:#fff;border:none;border-radius:9px;padding:10px 16px;font-weight:700;font-size:.86rem;width:100%;cursor:pointer;" onmouseover="this.style.opacity='.88'" onmouseout="this.style.opacity='1'"><i class="bi bi-funnel-fill me-1"></i>Lọc</button>
      </div>
      <?php if($search||$loc_f):?>
      <div class="col-md-1">
        <a href="<?=$base_pub?>/internships.php" style="display:flex;align-items:center;justify-content:center;padding:10px;border-radius:9px;border:1.5px solid rgba(192,96,80,.3);color:#9a3030;text-decoration:none;font-size:.82rem;font-weight:600;" title="Xóa bộ lọc">✕</a>
      </div>
      <?php endif;?>
    </div>
  </form>

  <!-- Jobs -->
  <?php if(empty($jobs)):?>
  <div style="text-align:center;padding:72px 20px;background:#fff;border-radius:18px;">
    <i class="bi bi-briefcase" style="font-size:3.2rem;color:var(--sg);opacity:.35;display:block;margin-bottom:14px;"></i>
    <h5 style="font-weight:700;color:var(--td);">Không tìm thấy vị trí phù hợp</h5>
    <p style="font-size:.85rem;color:var(--tl);">Thử điều chỉnh bộ lọc hoặc quay lại sau.</p>
    <a href="<?=$base_pub?>/internships.php" style="color:var(--ds);font-weight:600;font-size:.84rem;">Xem tất cả →</a>
  </div>
  <?php else:?>
  <div class="row g-4 mb-5">
    <?php foreach($jobs as $j):
      $logo=!empty($j['logo'])?'/internship-management-system/internship_system/uploads/'.$j['logo']:null;
      $init=strtoupper(mb_substr($j['company_name']??'C',0,1));
      $cols=['#5D7B6F','#3D5A50','#4a9e8a','#a07040','#9a6a3a','#4a6a9a'];
      $col=$cols[abs(crc32($j['company_name']??''))%count($cols)];
      $loc=!empty($j['location'])?$j['location']:(!empty($j['address'])?substr($j['address'],0,28):'');
    ?>
    <div class="col-md-6 col-lg-4">
      <div class="jc">
        <div style="display:flex;align-items:flex-start;gap:13px;margin-bottom:13px;">
          <?php if($logo):?><img src="<?=$logo?>" style="width:46px;height:46px;border-radius:11px;object-fit:cover;border:1px solid rgba(0,0,0,.07);flex-shrink:0;"><?php else:?><div style="width:46px;height:46px;border-radius:11px;background:<?=$col?>;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:800;font-size:1.1rem;flex-shrink:0;"><?=$init?></div><?php endif;?>
          <div style="flex:1;min-width:0;"><div style="font-weight:800;font-size:.92rem;color:var(--td);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?=htmlspecialchars($j['title']??'')?></div><div style="color:var(--tl);font-size:.76rem;"><?=htmlspecialchars($j['company_name']??'')?></div></div>
        </div>
        <div style="display:flex;flex-wrap:wrap;gap:5px;margin-bottom:11px;">
          <?php if($loc):?><span class="tag"><i class="bi bi-geo-alt-fill" style="font-size:.6rem;"></i> <?=htmlspecialchars($loc)?></span><?php endif;?>
          <?php if(!empty($j['quantity'])):?><span class="tag"><i class="bi bi-people-fill" style="font-size:.6rem;"></i> <?=$j['quantity']?> chỗ</span><?php endif;?>
          <?php if(!empty($j['start_date'])):?><span class="tag tag-warm"><i class="bi bi-calendar3" style="font-size:.6rem;"></i> <?=date('d/m/Y',strtotime($j['start_date']))?></span><?php endif;?>
        </div>
        <?php if(!empty($j['description'])):?><p style="color:var(--tl);font-size:.79rem;line-height:1.62;flex:1;margin:0 0 13px;overflow:hidden;display:-webkit-box;-webkit-line-clamp:4;-webkit-box-orient:vertical;"><?=htmlspecialchars($j['description'])?></p><?php endif;?>
        <?php if(!empty($j['requirements'])):?><div style="background:rgba(234,231,214,.7);border-radius:9px;padding:9px 12px;margin-bottom:12px;font-size:.74rem;color:var(--tl);border-left:3px solid var(--sg);"><span style="font-weight:700;color:var(--td);">Yêu cầu: </span><?=htmlspecialchars(substr($j['requirements'],0,100)).(strlen($j['requirements'])>100?'...':'')?></div><?php endif;?>
        <a href="<?=$base_sys?>/auth/login.php" class="btn-apply"><i class="bi bi-send-fill"></i> Ứng tuyển ngay</a>
        <?php if(!empty($j['end_date'])):?><div style="text-align:center;margin-top:7px;font-size:.7rem;color:var(--tl);"><i class="bi bi-clock me-1"></i>Hạn: <?=date('d/m/Y',strtotime($j['end_date']))?></div><?php endif;?>
      </div>
    </div>
    <?php endforeach;?>
  </div>
  <?php if($tp>1):?>
  <nav style="display:flex;justify-content:center;">
    <ul class="pagination">
      <?php if($page_num>1):?><li class="page-item"><a class="page-link" href="?q=<?=urlencode($search)?>&loc=<?=urlencode($loc_f)?>&page=<?=$page_num-1?>">‹ Trước</a></li><?php endif;?>
      <?php for($i=max(1,$page_num-2);$i<=min($tp,$page_num+2);$i++):?>
      <li class="page-item <?=$i===$page_num?'active':''?>"><a class="page-link" href="?q=<?=urlencode($search)?>&loc=<?=urlencode($loc_f)?>&page=<?=$i?>"><?=$i?></a></li>
      <?php endfor;?>
      <?php if($page_num<$tp):?><li class="page-item"><a class="page-link" href="?q=<?=urlencode($search)?>&loc=<?=urlencode($loc_f)?>&page=<?=$page_num+1?>">Tiếp ›</a></li><?php endif;?>
    </ul>
  </nav>
  <?php endif;?>
  <?php endif;?>
</div>

<footer style="background:var(--ds3);padding:28px 0;text-align:center;">
  <div class="container">
    <div style="display:flex;align-items:center;justify-content:center;gap:18px;flex-wrap:wrap;margin-bottom:12px;">
      <?php foreach([['Trang chủ',$base_pub.'/index.php'],['Vị trí TT',$base_pub.'/internships.php'],['Giới thiệu',$base_pub.'/about.php'],['Đăng nhập',$base_sys.'/auth/login.php']] as [$l,$u]):?>
      <a href="<?=$u?>" style="color:rgba(255,255,255,.5);font-size:.78rem;text-decoration:none;transition:color .15s;" onmouseover="this.style.color='var(--sm)'" onmouseout="this.style.color='rgba(255,255,255,.5)'"><?=$l?></a>
      <?php endforeach;?>
    </div>
    <div style="font-size:.72rem;color:rgba(255,255,255,.3);">© 2025 iSchool Internship Management System</div>
  </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
