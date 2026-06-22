<?php
$base_sys = '/internship-management-system/internship_system';
$base_pub = '/internship-management-system/public';
require_once __DIR__.'/../internship_system/config/database.php';
$stats=[];
foreach(['jobs'=>"SELECT COUNT(*) c FROM internships WHERE status='open'",
         'students'=>"SELECT COUNT(*) c FROM users WHERE role='student'",
         'companies'=>"SELECT COUNT(*) c FROM company_profiles",
         'completed'=>"SELECT COUNT(*) c FROM internship_registrations WHERE status='completed'"] as $k=>$q){
  $r=$conn->query($q); $stats[$k]=($r&&$r!==true)?(int)$r->fetch_assoc()['c']:0;
}
$jobs=[];
$r=$conn->query("SELECT i.*,cp.company_name,cp.logo,cp.address FROM internships i LEFT JOIN company_profiles cp ON i.company_id=cp.company_id WHERE i.status='open' ORDER BY i.created_at DESC LIMIT 6");
if($r&&$r!==true) while($row=$r->fetch_assoc()) $jobs[]=$row;
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>ISchool Internship &mdash; Trang ch&#7911;</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@700;800;900&display=swap" rel="stylesheet">
<style>
:root{--ds:#5D7B6F;--ds2:#3D5A50;--ds3:#2A3F38;--sg:#A4C3A2;--sm:#B0D4B8;--wc:#EAE7D6;--td:#1A2E28;--tm:#4A6058;--tl:#7A9590;}
*,*::before,*::after{box-sizing:border-box}
body{font-family:'Inter',sans-serif;background:#eef5f2;color:var(--td);margin:0;padding-top:56px;}
::-webkit-scrollbar{width:5px}::-webkit-scrollbar-thumb{background:var(--sg);border-radius:4px}
@keyframes fadeUp{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:none}}
@keyframes floatY{0%,100%{transform:translateY(0)}50%{transform:translateY(-12px)}}
@keyframes floatCard{0%,100%{transform:translateY(0)}50%{transform:translateY(-8px)}}
@keyframes pulse{0%,100%{opacity:1}50%{opacity:.4}}
.fu{animation:fadeUp .5s ease both}.fu1{animation:fadeUp .5s .1s ease both}
.fu2{animation:fadeUp .5s .2s ease both}.fu3{animation:fadeUp .5s .3s ease both}
</style>
</head>
<body>
<?php require_once __DIR__.'/_nav.php'; ?>

<!-- HERO -->
<section style="background:linear-gradient(148deg,var(--ds3) 0%,var(--ds2) 45%,#4a7a68 100%);min-height:88vh;display:flex;align-items:center;position:relative;overflow:hidden;">
  <div style="position:absolute;width:480px;height:480px;border-radius:50%;background:radial-gradient(circle,rgba(164,195,162,.12),transparent 70%);top:-100px;right:-80px;animation:floatY 12s ease-in-out infinite;pointer-events:none;"></div>
  <div style="position:absolute;width:320px;height:320px;border-radius:50%;background:radial-gradient(circle,rgba(176,212,184,.08),transparent 70%);bottom:-60px;left:-40px;animation:floatY 16s ease-in-out infinite reverse;pointer-events:none;"></div>
  <div style="position:absolute;inset:0;background-image:radial-gradient(rgba(176,212,184,.1) 1px,transparent 1px);background-size:34px 34px;pointer-events:none;"></div>
  <div class="container" style="max-width:1140px;position:relative;z-index:1;">
    <div class="row align-items-center g-5">
      <div class="col-lg-6">
        <div class="fu" style="display:inline-flex;align-items:center;gap:8px;background:rgba(164,195,162,.15);border:1px solid rgba(176,212,184,.28);border-radius:50px;padding:6px 16px;margin-bottom:20px;">
          <span style="width:7px;height:7px;background:var(--sg);border-radius:50%;animation:pulse 2s infinite;display:inline-block;"></span>
          <span style="color:var(--sm);font-size:.77rem;font-weight:600;">H&#7879; th&#7889;ng th&#7921;c t&#7853;p ch&#237;nh th&#7913;c c&#7911;a iSchool</span>
        </div>
        <h1 class="fu1" style="font-family:'Plus Jakarta Sans',sans-serif;font-size:clamp(2rem,4vw,3.2rem);font-weight:900;color:#fff;line-height:1.12;margin-bottom:20px;">
          Kh&#7903;i &#273;&#7847;u s&#7921; nghi&#7879;p<br>
          <span style="background:linear-gradient(90deg,var(--sm),#8de0b0);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">t&#7915; k&#7923; th&#7921;c t&#7853;p</span><br>
          ho&#224;n h&#7843;o
        </h1>
        <p class="fu2" style="color:rgba(255,255,255,.72);font-size:.97rem;line-height:1.8;margin-bottom:32px;max-width:480px;">K&#7871;t n&#7889;i sinh vi&#234;n v&#7899;i h&#224;ng tr&#259;m doanh nghi&#7879;p h&#224;ng &#273;&#7847;u. Qu&#7843;n l&#253; to&#224;n b&#7897; quy tr&#236;nh th&#7921;c t&#7853;p t&#7915; t&#236;m ki&#7871;m &#273;&#7871;n ho&#224;n th&#224;nh.</p>
        <div class="fu3" style="display:flex;gap:14px;flex-wrap:wrap;">
          <a href="<?=$base_sys?>/auth/register.php" style="padding:13px 28px;background:linear-gradient(135deg,var(--sg),#4a9e78);border-radius:10px;color:#fff;font-weight:800;font-size:.92rem;display:inline-flex;align-items:center;gap:8px;transition:all .25s;box-shadow:0 6px 20px rgba(93,123,111,.38);text-decoration:none;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform=''">
            <i class="bi bi-rocket-takeoff-fill"></i> B&#7855;t &#273;&#7847;u mi&#7877;n ph&#237;
          </a>
          <a href="<?=$base_pub?>/internships.php" style="padding:13px 28px;border:2px solid rgba(176,212,184,.38);border-radius:10px;color:rgba(255,255,255,.9);font-weight:700;font-size:.92rem;display:inline-flex;align-items:center;gap:8px;transition:all .22s;text-decoration:none;" onmouseover="this.style.background='rgba(176,212,184,.1)'" onmouseout="this.style.background=''">
            <i class="bi bi-search"></i> Xem v&#7883; tr&#237; TT
          </a>
        </div>
        <div style="display:flex;align-items:center;gap:18px;margin-top:28px;flex-wrap:wrap;">
          <div style="display:flex;align-items:center;gap:6px;color:rgba(255,255,255,.55);font-size:.78rem;"><i class="bi bi-shield-check-fill" style="color:var(--sm);"></i> B&#7843;o m&#7853;t d&#7919; li&#7879;u</div>
          <div style="display:flex;align-items:center;gap:6px;color:rgba(255,255,255,.55);font-size:.78rem;"><i class="bi bi-check-circle-fill" style="color:var(--sm);"></i> Mi&#7877;n ph&#237; cho SV</div>
          <div style="display:flex;align-items:center;gap:6px;color:rgba(255,255,255,.55);font-size:.78rem;"><i class="bi bi-star-fill" style="color:var(--sm);"></i> H&#7895; tr&#7907; 24/7</div>
        </div>
      </div>
      <div class="col-lg-6 d-none d-lg-flex justify-content-center">
        <div style="width:100%;max-width:400px;position:relative;">
          <div style="background:rgba(255,255,255,.09);backdrop-filter:blur(18px);border:1px solid rgba(255,255,255,.16);border-radius:20px;padding:26px;animation:floatCard 7s ease-in-out infinite;">
            <div style="display:flex;align-items:center;gap:12px;margin-bottom:20px;">
              <div style="width:44px;height:44px;background:linear-gradient(135deg,var(--sg),#4a9e78);border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.3rem;">&#127979;</div>
              <div>
                <div style="font-family:'Plus Jakarta Sans',sans-serif;font-weight:800;color:#fff;font-size:.95rem;">iSchool Internship</div>
                <div style="color:rgba(255,255,255,.5);font-size:.7rem;">Qu&#7843;n l&#253; th&#7921;c t&#7853;p to&#224;n di&#7879;n</div>
              </div>
              <div style="margin-left:auto;background:rgba(164,195,162,.22);color:var(--sm);padding:3px 9px;border-radius:20px;font-size:.67rem;font-weight:700;">LIVE</div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:16px;">
              <?php $hs=[['&#127970;',$stats['companies'],'Doanh nghi&#7879;p','rgba(164,195,162,.18)'],['&#128188;',$stats['jobs'],'V&#7883; tr&#237; m&#7903;','rgba(110,212,164,.15)'],['&#127891;',$stats['students'],'Sinh vi&#234;n','rgba(176,212,184,.16)'],['&#127942;',$stats['completed'],'Ho&#224;n th&#224;nh','rgba(255,213,128,.15)']];
              foreach($hs as [$em,$n,$lb,$bg]):?>
              <div style="background:<?=$bg?>;border:1px solid rgba(255,255,255,.08);border-radius:12px;padding:13px 11px;">
                <div style="font-size:1.2rem;margin-bottom:4px;"><?=$em?></div>
                <div style="font-family:'Plus Jakarta Sans',sans-serif;font-weight:800;color:#fff;font-size:1.35rem;line-height:1;"><?=$n?></div>
                <div style="color:rgba(255,255,255,.5);font-size:.67rem;margin-top:2px;"><?=$lb?></div>
              </div>
              <?php endforeach;?>
            </div>
            <div style="background:rgba(164,195,162,.13);border-radius:10px;padding:10px 13px;display:flex;align-items:center;gap:10px;">
              <div style="width:34px;height:34px;background:linear-gradient(135deg,#4a9e6a,#2d7a50);border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:.85rem;flex-shrink:0;">&#9989;</div>
              <div><div style="color:#fff;font-weight:700;font-size:.8rem;">Quy tr&#236;nh chu&#7849;n 6 b&#432;&#7899;c</div><div style="color:rgba(255,255,255,.5);font-size:.68rem;">T&#7915; &#7913;ng tuy&#7875;n &#273;&#7871;n ho&#224;n th&#224;nh</div></div>
            </div>
          </div>
          <div style="position:absolute;top:-16px;right:-16px;background:#fff;border-radius:10px;padding:8px 13px;box-shadow:0 6px 20px rgba(0,0,0,.15);animation:floatCard 5s ease-in-out infinite;font-size:.75rem;font-weight:700;color:var(--ds3);white-space:nowrap;display:flex;align-items:center;gap:5px;">
            <i class="bi bi-patch-check-fill" style="color:#4a9e6a;"></i> Duy&#7879;t h&#7891; s&#417; nhanh
          </div>
          <div style="position:absolute;bottom:-12px;left:-16px;background:#fff;border-radius:10px;padding:8px 13px;box-shadow:0 6px 20px rgba(0,0,0,.15);animation:floatCard 9s ease-in-out infinite reverse;font-size:.75rem;font-weight:700;color:var(--ds3);white-space:nowrap;display:flex;align-items:center;gap:5px;">
            <i class="bi bi-chat-dots-fill" style="color:var(--sg);"></i> Chat tr&#7921;c ti&#7871;p DN &harr; SV
          </div>
        </div>
      </div>
    </div>
  </div>
  <div style="position:absolute;bottom:0;left:0;right:0;line-height:0;">
    <svg viewBox="0 0 1440 50" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none" style="display:block;width:100%;height:50px;"><path d="M0,50 C360,0 1080,50 1440,0 L1440,50 Z" fill="#eef5f2"/></svg>
  </div>
</section>

<!-- STATS BAR -->
<section style="background:#fff;padding:32px 0;border-bottom:1px solid rgba(164,195,162,.15);">
  <div class="container" style="max-width:1100px;">
    <div class="row g-4 text-center">
      <?php foreach([['bi-briefcase-fill',$stats['jobs'],'V&#7883; tr&#237; th&#7921;c t&#7853;p','#5D7B6F'],['bi-building-fill',$stats['companies'],'Doanh nghi&#7879;p','#3D5A50'],['bi-mortarboard-fill',$stats['students'],'Sinh vi&#234;n','#4a9e8a'],['bi-trophy-fill',$stats['completed'],'&#272;&#227; ho&#224;n th&#224;nh','#a07040']] as [$ic,$n,$lb,$c]):?>
      <div class="col-6 col-md-3">
        <i class="bi <?=$ic?>" style="font-size:1.6rem;color:<?=$c?>;display:block;margin-bottom:8px;"></i>
        <div class="stat-num" data-target="<?=$n?>" style="font-family:'Plus Jakarta Sans',sans-serif;font-size:2.1rem;font-weight:900;color:var(--td);line-height:1;">0</div>
        <div style="font-size:.77rem;color:var(--tl);font-weight:500;margin-top:4px;"><?=$lb?></div>
      </div>
      <?php endforeach;?>
    </div>
  </div>
</section>

<!-- HOW IT WORKS -->
<section style="padding:80px 0;background:var(--wc);">
  <div class="container" style="max-width:1100px;">
    <div class="text-center mb-5">
      <span style="background:rgba(93,123,111,.1);color:var(--ds);padding:6px 16px;border-radius:50px;font-size:.76rem;font-weight:700;display:inline-block;margin-bottom:12px;">QUY TR&#204;NH</span>
      <h2 style="font-family:'Plus Jakarta Sans',sans-serif;font-size:2rem;font-weight:900;color:var(--td);">C&#225;ch ho&#7841;t &#273;&#7897;ng</h2>
      <p style="color:var(--tl);font-size:.9rem;max-width:480px;margin:10px auto 0;">T&#7915; &#273;&#259;ng k&#253; &#273;&#7871;n ho&#224;n th&#224;nh &mdash; to&#224;n b&#7897; tr&#234;n 1 n&#7873;n t&#7843;ng</p>
    </div>
    <div class="row g-4">
      <?php $steps=[['1','bi-person-plus-fill','#5D7B6F','&#272;&#259;ng k&#253;','T&#7841;o t&#224;i kho&#7843;n v&#224; ho&#224;n thi&#7879;n h&#7891; s&#417;'],['2','bi-search','#3D5A50','T&#236;m v&#7883; tr&#237;','Duy&#7879;t v&#224; &#7913;ng tuy&#7875;n v&#7883; tr&#237; ph&#249; h&#7907;p'],['3','bi-clipboard-check-fill','#a07040','X&#233;t duy&#7879;t','Tr&#432;&#7901;ng &amp; DN xem x&#233;t h&#7891; s&#417;'],['4','bi-briefcase-fill','#4a9e8a','Th&#7921;c t&#7853;p','B&#7855;t &#273;&#7847;u v&#7899;i GVHD h&#7895; tr&#7907;'],['5','bi-star-fill','#9a6a3a','&#272;&#225;nh gi&#225;','DN &#273;&#225;nh gi&#225;, SV n&#7897;p b&#225;o c&#225;o'],['6','bi-trophy-fill','#4a6a9a','Ho&#224;n th&#224;nh','Nh&#7853;n k&#7871;t qu&#7843; ch&#237;nh th&#7913;c']];
      foreach($steps as [$n,$ic,$c,$t,$d]):?>
      <div class="col-6 col-md-4 col-lg-2 text-center">
        <div style="width:58px;height:58px;background:<?=$c?>18;border:2.5px solid <?=$c?>44;border-radius:16px;display:flex;align-items:center;justify-content:center;margin:0 auto 12px;transition:all .2s;" onmouseover="this.style.transform='scale(1.08)'" onmouseout="this.style.transform=''">
          <i class="bi <?=$ic?>" style="font-size:1.4rem;color:<?=$c?>;"></i>
        </div>
        <div style="width:22px;height:22px;background:<?=$c?>;border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-size:.65rem;font-weight:800;margin:0 auto 8px;"><?=$n?></div>
        <div style="font-weight:700;font-size:.86rem;color:var(--td);margin-bottom:4px;"><?=$t?></div>
        <div style="font-size:.74rem;color:var(--tl);line-height:1.55;"><?=$d?></div>
      </div>
      <?php endforeach;?>
    </div>
  </div>
</section>

<!-- LATEST JOBS -->
<section style="padding:80px 0;background:#fff;">
  <div class="container" style="max-width:1100px;">
    <div style="display:flex;align-items:flex-end;justify-content:space-between;margin-bottom:36px;flex-wrap:wrap;gap:12px;">
      <div>
        <span style="background:rgba(93,123,111,.1);color:var(--ds);padding:6px 16px;border-radius:50px;font-size:.76rem;font-weight:700;display:inline-block;margin-bottom:10px;">V&#7882; TR&#205; M&#7900;I NH&#7844;T</span>
        <h2 style="font-family:'Plus Jakarta Sans',sans-serif;font-size:1.9rem;font-weight:900;color:var(--td);margin:0;">C&#417; h&#7897;i th&#7921;c t&#7853;p h&#244;m nay</h2>
      </div>
      <a href="<?=$base_pub?>/internships.php" style="padding:9px 20px;border:1.5px solid rgba(93,123,111,.25);border-radius:9px;color:var(--ds);font-weight:700;font-size:.83rem;text-decoration:none;transition:all .18s;white-space:nowrap;" onmouseover="this.style.background='rgba(93,123,111,.06)'" onmouseout="this.style.background=''">Xem t&#7845;t c&#7843; <i class="bi bi-arrow-right ms-1"></i></a>
    </div>
    <?php if(empty($jobs)):?>
    <div style="text-align:center;padding:60px;background:var(--wc);border-radius:16px;">
      <i class="bi bi-briefcase" style="font-size:3rem;color:var(--sg);opacity:.4;display:block;margin-bottom:12px;"></i>
      <p style="color:var(--tl);font-size:.9rem;">Ch&#432;a c&#243; v&#7883; tr&#237; n&#224;o &#273;ang m&#7903;.</p>
    </div>
    <?php else:?>
    <div class="row g-4">
      <?php foreach($jobs as $j):
        $logo=!empty($j['logo'])?'/internship-management-system/internship_system/uploads/'.$j['logo']:null;
        $init=strtoupper(mb_substr($j['company_name']??'C',0,1));
        $cols=['#5D7B6F','#3D5A50','#4a9e8a','#a07040','#9a6a3a','#4a6a9a'];
        $col=$cols[abs(crc32($j['company_name']??''))%count($cols)];
        $loc=!empty($j['location'])?$j['location']:(!empty($j['address'])?substr($j['address'],0,28):'');
      ?>
      <div class="col-md-6 col-lg-4">
        <div style="background:#fff;border:1.5px solid rgba(164,195,162,.2);border-radius:16px;padding:22px;height:100%;display:flex;flex-direction:column;transition:all .22s;" onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 12px 28px rgba(93,123,111,.12)';this.style.borderColor='rgba(93,123,111,.28)'" onmouseout="this.style.transform='';this.style.boxShadow='';this.style.borderColor='rgba(164,195,162,.2)'">
          <div style="display:flex;align-items:flex-start;gap:13px;margin-bottom:13px;">
            <?php if($logo):?><img src="<?=$logo?>" style="width:46px;height:46px;border-radius:11px;object-fit:cover;border:1px solid rgba(0,0,0,.07);flex-shrink:0;"><?php else:?><div style="width:46px;height:46px;border-radius:11px;background:<?=$col?>;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:800;font-size:1.1rem;flex-shrink:0;"><?=$init?></div><?php endif;?>
            <div style="flex:1;min-width:0;"><div style="font-weight:800;font-size:.92rem;color:var(--td);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?=htmlspecialchars($j['title']??'')?></div><div style="color:var(--tl);font-size:.76rem;"><?=htmlspecialchars($j['company_name']??'')?></div></div>
          </div>
          <div style="display:flex;flex-wrap:wrap;gap:5px;margin-bottom:11px;">
            <?php if($loc):?><span style="background:rgba(93,123,111,.1);color:var(--ds);padding:3px 9px;border-radius:20px;font-size:.7rem;font-weight:600;"><i class="bi bi-geo-alt-fill" style="font-size:.6rem;"></i> <?=htmlspecialchars($loc)?></span><?php endif;?>
            <?php if(!empty($j['quantity'])):?><span style="background:rgba(61,90,80,.09);color:var(--ds2);padding:3px 9px;border-radius:20px;font-size:.7rem;font-weight:600;"><i class="bi bi-people-fill" style="font-size:.6rem;"></i> <?=$j['quantity']?> ch&#7895;</span><?php endif;?>
          </div>
          <?php if(!empty($j['description'])):?><p style="color:var(--tl);font-size:.78rem;line-height:1.62;flex:1;margin:0 0 13px;overflow:hidden;display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;"><?=htmlspecialchars(substr($j['description'],0,140))?></p><?php endif;?>
          <a href="<?=$base_sys?>/auth/login.php" style="display:flex;align-items:center;justify-content:center;gap:7px;padding:9px;border-radius:9px;background:linear-gradient(135deg,var(--ds),var(--ds2));color:#fff;font-weight:700;font-size:.82rem;text-decoration:none;transition:opacity .18s;" onmouseover="this.style.opacity='.88'" onmouseout="this.style.opacity='1'"><i class="bi bi-send-fill"></i> &#7�;ng tuy&#7875;n ngay</a>
        </div>
      </div>
      <?php endforeach;?>
    </div>
    <?php endif;?>
  </div>
</section>

<!-- FEATURES -->
<section style="padding:80px 0;background:var(--wc);">
  <div class="container" style="max-width:1100px;">
    <div class="text-center mb-5">
      <span style="background:rgba(93,123,111,.1);color:var(--ds);padding:6px 16px;border-radius:50px;font-size:.76rem;font-weight:700;display:inline-block;margin-bottom:12px;">T&#205;NH N&#258;NG</span>
      <h2 style="font-family:'Plus Jakarta Sans',sans-serif;font-size:2rem;font-weight:900;color:var(--td);">T&#7841;i sao ch&#7885;n iSchool Internship?</h2>
    </div>
    <div class="row g-4">
      <?php $feats=[['bi-shield-check-fill','#5D7B6F','Quy tr&#236;nh chu&#7849;n h&#243;a','To&#224;n b&#7897; t&#7915; &#7913;ng tuy&#7875;n &rarr; ph&#7887;ng v&#7845;n &rarr; th&#7921;c t&#7853;p &rarr; &#273;&#225;nh gi&#225;.'],['bi-chat-dots-fill','#3D5A50','Nh&#7855;n tin tr&#7921;c ti&#7871;p','SV v&#224; DN chat ngay tr&#234;n n&#7873;n t&#7843;ng &#273;&#7875; h&#7865;n l&#7883;ch ph&#7887;ng v&#7845;n.'],['bi-file-earmark-arrow-up-fill','#a07040','Upload CV &amp; h&#7891; s&#417;','T&#7843;i l&#234;n CV, &#7843;nh &#273;&#7841;i di&#7879;n v&#224; gi&#7845;y t&#7901; li&#234;n quan.'],['bi-person-workspace','#4a9e8a','Gi&#7843;ng vi&#234;n h&#432;&#7899;ng d&#7851;n','M&#7895;i SV &#273;&#432;&#7907;c ph&#226;n c&#244;ng GVHD ri&#234;ng trong su&#7889;t k&#7923; TT.'],['bi-star-fill','#9a6a3a','&#272;&#225;nh gi&#225; chuy&#234;n nghi&#7879;p','DN &#273;&#225;nh gi&#225; k&#7871;t qu&#7843;, SV n&#7897;p b&#225;o c&#225;o, GVHD ph&#234; duy&#7879;t.'],['bi-graph-up-arrow','#4a6a9a','Dashboard t&#7893;ng quan','Admin theo d&#245;i to&#224;n b&#7897; h&#7879; th&#7889;ng v&#7899;i th&#7889;ng k&#234; real-time.']];
      foreach($feats as [$ic,$c,$t,$d]):?>
      <div class="col-md-6 col-lg-4">
        <div style="background:#fff;border-radius:16px;padding:24px;height:100%;border:1px solid rgba(164,195,162,.15);transition:all .22s;" onmouseover="this.style.transform='translateY(-3px)';this.style.boxShadow='0 10px 28px rgba(93,123,111,.1)'" onmouseout="this.style.transform='';this.style.boxShadow=''">
          <div style="width:48px;height:48px;background:<?=$c?>16;border-radius:13px;display:flex;align-items:center;justify-content:center;margin-bottom:14px;"><i class="bi <?=$ic?>" style="font-size:1.3rem;color:<?=$c?>;"></i></div>
          <h5 style="font-weight:800;font-size:.93rem;color:var(--td);margin-bottom:8px;"><?=$t?></h5>
          <p style="color:var(--tl);font-size:.8rem;line-height:1.7;margin:0;"><?=$d?></p>
        </div>
      </div>
      <?php endforeach;?>
    </div>
  </div>
</section>

<!-- CTA -->
<section style="padding:72px 0;background:linear-gradient(135deg,var(--ds3),var(--ds2),#4a7a68);position:relative;overflow:hidden;">
  <div style="position:absolute;inset:0;background-image:radial-gradient(rgba(176,212,184,.08) 1px,transparent 1px);background-size:30px 30px;pointer-events:none;"></div>
  <div class="container text-center" style="max-width:640px;position:relative;">
    <h2 style="font-family:'Plus Jakarta Sans',sans-serif;font-size:2rem;font-weight:900;color:#fff;margin-bottom:12px;">S&#7861;n s&#224;ng b&#7855;t &#273;&#7847;u?</h2>
    <p style="color:rgba(255,255,255,.7);font-size:.93rem;line-height:1.75;margin-bottom:30px;">&#272;&#259;ng k&#253; mi&#7877;n ph&#237; v&#224; tham gia c&#249;ng sinh vi&#234;n v&#224; doanh nghi&#7879;p tr&#234;n n&#7873;n t&#7843;ng iSchool Internship.</p>
    <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;">
      <a href="<?=$base_sys?>/auth/register.php" style="padding:13px 28px;background:linear-gradient(135deg,var(--sm),var(--sg));border-radius:10px;color:var(--ds3);font-weight:800;font-size:.9rem;display:inline-flex;align-items:center;gap:8px;text-decoration:none;box-shadow:0 5px 18px rgba(176,212,184,.3);transition:all .22s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform=''"><i class="bi bi-person-plus-fill"></i> &#272;&#259;ng k&#253; sinh vi&#234;n</a>
      <a href="<?=$base_sys?>/auth/register.php" style="padding:13px 28px;border:2px solid rgba(176,212,184,.35);border-radius:10px;color:rgba(255,255,255,.9);font-weight:700;font-size:.9rem;display:inline-flex;align-items:center;gap:8px;text-decoration:none;transition:all .22s;" onmouseover="this.style.background='rgba(176,212,184,.1)'" onmouseout="this.style.background=''"><i class="bi bi-building-fill"></i> &#272;&#259;ng k&#253; doanh nghi&#7879;p</a>
    </div>
  </div>
</section>

<!-- FOOTER -->
<footer style="background:var(--ds3);padding:32px 0;text-align:center;">
  <div class="container">
    <div style="display:flex;align-items:center;justify-content:center;gap:10px;margin-bottom:14px;">
      <div style="width:34px;height:34px;background:linear-gradient(135deg,var(--sm),var(--sg));border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:.9rem;">&#127979;</div>
      <span style="font-family:'Plus Jakarta Sans',sans-serif;font-weight:800;color:#fff;font-size:.88rem;">iSchool Internship</span>
    </div>
    <div style="display:flex;align-items:center;justify-content:center;gap:18px;flex-wrap:wrap;margin-bottom:12px;">
      <?php foreach([['Trang ch&#7911;',$base_pub.'/index.php'],['V&#7883; tr&#237; TT',$base_pub.'/internships.php'],['Gi&#7899;i thi&#7879;u',$base_pub.'/about.php'],['&#272;&#259;ng nh&#7853;p',$base_sys.'/auth/login.php']] as [$l,$u]):?>
      <a href="<?=$u?>" style="color:rgba(255,255,255,.5);font-size:.78rem;text-decoration:none;transition:color .15s;" onmouseover="this.style.color='var(--sm)'" onmouseout="this.style.color='rgba(255,255,255,.5)'"><?=$l?></a>
      <?php endforeach;?>
    </div>
    <div style="font-size:.72rem;color:rgba(255,255,255,.3);">&copy; 2025 iSchool Internship Management System</div>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Count-up
var obs=new IntersectionObserver(function(entries){entries.forEach(function(e){if(e.isIntersecting){var el=e.target,target=parseInt(el.dataset.target)||0,dur=1400,step=dur/60,inc=target/60,cur=0;var t=setInterval(function(){cur+=inc;if(cur>=target){el.textContent=target;clearInterval(t);}else el.textContent=Math.floor(cur);},step);obs.unobserve(el);}});},{threshold:.4});
document.querySelectorAll('.stat-num').forEach(function(el){obs.observe(el);});
</script>
</body>
</html>
