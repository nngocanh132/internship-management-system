<?php
$base_sys = '/internship-management-system/internship_system';
$base_pub = '/internship-management-system/public';
require_once __DIR__.'/../internship_system/config/database.php';
$stats=[];
foreach(['jobs'=>"SELECT COUNT(*) c FROM internships WHERE status='open'",'students'=>"SELECT COUNT(*) c FROM users WHERE role='student'",'companies'=>"SELECT COUNT(*) c FROM company_profiles",'completed'=>"SELECT COUNT(*) c FROM internship_registrations WHERE status='completed'"] as $k=>$q){
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
<title>ISchool Internship — Nền tảng thực tập hàng đầu</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@700;800;900&display=swap" rel="stylesheet">
<style>
:root{--pg:#5D7B6F;--pg2:#3D5A50;--pg3:#2A3F38;--sg:#A4C3A2;--sm:#B0D4B8;--bg:#EAE7D6;--td:#1A2E28;--tm:#4A6058;--tl:#7A9590;}
*,*::before,*::after{box-sizing:border-box}
body{font-family:'Inter',sans-serif;background:#fff;color:var(--td);margin:0;padding-top:56px;}
::-webkit-scrollbar{width:5px}::-webkit-scrollbar-thumb{background:var(--sg);border-radius:4px}
/* NAV STYLES — defined in _nav.php */
/* ANIMATIONS */
@keyframes fadeUp{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:none}}
@keyframes floatY{0%,100%{transform:translateY(0)}50%{transform:translateY(-14px)}}
@keyframes floatCard{0%,100%{transform:translateY(0)}50%{transform:translateY(-10px)}}
@keyframes pulse{0%,100%{opacity:1;transform:scale(1)}50%{opacity:.5;transform:scale(.9)}}
@keyframes countUp{from{opacity:0;transform:scale(.7)}to{opacity:1;transform:scale(1)}}
.fu{animation:fadeUp .55s ease both}
.fu1{animation:fadeUp .55s .1s ease both}
.fu2{animation:fadeUp .55s .2s ease both}
.fu3{animation:fadeUp .55s .3s ease both}
</style>
</head>
<body>
<?php require_once __DIR__.'/_nav.php'; ?>

<!-- ══════════════════ HERO ══════════════════ -->
<section style="background:linear-gradient(148deg,var(--pg3) 0%,var(--pg2) 45%,#3a6e58 100%);min-height:90vh;display:flex;align-items:center;position:relative;overflow:hidden;">
  <!-- BG blobs -->
  <div style="position:absolute;width:500px;height:500px;border-radius:50%;background:radial-gradient(circle,rgba(141,184,124,.12),transparent 70%);top:-100px;right:-80px;animation:floatY 12s ease-in-out infinite;pointer-events:none;"></div>
  <div style="position:absolute;width:360px;height:360px;border-radius:50%;background:radial-gradient(circle,rgba(181,212,168,.08),transparent 70%);bottom:-80px;left:-60px;animation:floatY 16s ease-in-out infinite reverse;pointer-events:none;"></div>
  <!-- Dots pattern -->
  <div style="position:absolute;inset:0;background-image:radial-gradient(rgba(181,212,168,.12) 1px,transparent 1px);background-size:36px 36px;pointer-events:none;"></div>

  <div class="container" style="max-width:1200px;position:relative;z-index:1;">
    <div class="row align-items-center g-5">
      <div class="col-lg-6">
        <div class="fu" style="display:inline-flex;align-items:center;gap:8px;background:rgba(141,184,124,.16);border:1px solid rgba(181,212,168,.3);border-radius:50px;padding:6px 16px;margin-bottom:22px;">
          <span style="width:7px;height:7px;background:#8DB87C;border-radius:50%;animation:pulse 2s infinite;display:inline-block;"></span>
          <span style="color:var(--sm);font-size:.77rem;font-weight:600;">🎓 Hệ thống thực tập chính thức của ISchool</span>
        </div>
        <h1 class="fu1" style="font-family:'Plus Jakarta Sans',sans-serif;font-size:clamp(2.2rem,4vw,3.4rem);font-weight:900;color:#fff;line-height:1.12;margin-bottom:20px;">
          Khởi đầu sự nghiệp<br>
          <span style="background:linear-gradient(90deg,#B5D4A8,#6ed4a4);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">từ kỳ thực tập</span><br>
          hoàn hảo
        </h1>
        <p class="fu2" style="color:rgba(255,255,255,.75);font-size:1rem;line-height:1.8;margin-bottom:34px;max-width:490px;">
          Kết nối sinh viên với hàng trăm doanh nghiệp hàng đầu. Quản lý toàn bộ quy trình thực tập từ tìm kiếm đến hoàn thành — nhanh chóng, minh bạch và hiệu quả.
        </p>
        <div class="fu3" style="display:flex;gap:14px;flex-wrap:wrap;">
          <a href="<?=$base_sys?>/auth/register.php" style="padding:14px 30px;background:linear-gradient(135deg,var(--sg),#5aaa70);border-radius:11px;color:#fff;font-weight:800;font-size:.93rem;display:inline-flex;align-items:center;gap:8px;transition:all .25s;box-shadow:0 6px 22px rgba(141,184,124,.45);" onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform=''">
            <i class="bi bi-rocket-takeoff-fill"></i> Bắt đầu miễn phí
          </a>
          <a href="<?=$base_pub?>/internships.php" style="padding:14px 30px;border:2px solid rgba(181,212,168,.4);border-radius:11px;color:rgba(255,255,255,.92);font-weight:700;font-size:.93rem;display:inline-flex;align-items:center;gap:8px;transition:all .22s;" onmouseover="this.style.background='rgba(181,212,168,.1)'" onmouseout="this.style.background=''">
            <i class="bi bi-search"></i> Xem vị trí TT
          </a>
        </div>
        <!-- Trust badges -->
        <div style="display:flex;align-items:center;gap:16px;margin-top:32px;flex-wrap:wrap;">
          <div style="display:flex;align-items:center;gap:6px;color:rgba(255,255,255,.6);font-size:.78rem;">
            <i class="bi bi-shield-check-fill" style="color:var(--sm);font-size:.9rem;"></i> Bảo mật dữ liệu
          </div>
          <div style="display:flex;align-items:center;gap:6px;color:rgba(255,255,255,.6);font-size:.78rem;">
            <i class="bi bi-check-circle-fill" style="color:var(--sm);font-size:.9rem;"></i> Miễn phí cho SV
          </div>
          <div style="display:flex;align-items:center;gap:6px;color:rgba(255,255,255,.6);font-size:.78rem;">
            <i class="bi bi-star-fill" style="color:var(--sm);font-size:.9rem;"></i> Hỗ trợ 24/7
          </div>
        </div>
      </div>

      <!-- Hero card -->
      <div class="col-lg-6 d-none d-lg-flex justify-content-center">
        <div style="width:100%;max-width:420px;position:relative;">
          <div style="background:rgba(255,255,255,.1);backdrop-filter:blur(20px);border:1px solid rgba(255,255,255,.18);border-radius:22px;padding:28px;animation:floatCard 7s ease-in-out infinite;">
            <div style="display:flex;align-items:center;gap:12px;margin-bottom:22px;">
              <div style="width:48px;height:48px;background:linear-gradient(135deg,var(--sg),#5aaa70);border-radius:13px;display:flex;align-items:center;justify-content:center;font-size:1.4rem;">🎓</div>
              <div>
                <div style="font-family:'Plus Jakarta Sans',sans-serif;font-weight:800;color:#fff;font-size:1rem;">ISchool Internship</div>
                <div style="color:rgba(255,255,255,.55);font-size:.72rem;">Quản lý thực tập toàn diện</div>
              </div>
              <div style="margin-left:auto;background:rgba(141,184,124,.25);color:#B5D4A8;padding:4px 10px;border-radius:20px;font-size:.68rem;font-weight:700;">LIVE</div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:18px;">
              <?php $hero_s=[['🏢',$stats['companies'],'Doanh nghiệp','rgba(141,184,124,.2)'],['💼',$stats['jobs'],'Vị trí mở','rgba(110,212,164,.18)'],['👨‍🎓',$stats['students'],'Sinh viên','rgba(181,212,168,.18)'],['🏆',$stats['completed'],'Hoàn thành','rgba(255,213,128,.18)']];
              foreach($hero_s as [$em,$n,$lb,$bg]):?>
              <div style="background:<?=$bg?>;border:1px solid rgba(255,255,255,.1);border-radius:13px;padding:14px 12px;">
                <div style="font-size:1.25rem;margin-bottom:4px;"><?=$em?></div>
                <div style="font-family:'Plus Jakarta Sans',sans-serif;font-weight:800;color:#fff;font-size:1.4rem;line-height:1;"><?=$n?></div>
                <div style="color:rgba(255,255,255,.55);font-size:.68rem;margin-top:3px;"><?=$lb?></div>
              </div>
              <?php endforeach;?>
            </div>
            <div style="background:rgba(141,184,124,.15);border-radius:11px;padding:11px 14px;display:flex;align-items:center;gap:10px;">
              <div style="width:36px;height:36px;background:linear-gradient(135deg,#4a9e6a,#2d7a50);border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:.9rem;flex-shrink:0;">✅</div>
              <div><div style="color:#fff;font-weight:700;font-size:.82rem;">Quy trình chuẩn 6 bước</div><div style="color:rgba(255,255,255,.55);font-size:.7rem;">Từ ứng tuyển đến hoàn thành</div></div>
            </div>
          </div>
          <!-- Floating badges -->
          <div style="position:absolute;top:-18px;right:-20px;background:#fff;border-radius:12px;padding:9px 14px;box-shadow:0 8px 24px rgba(0,0,0,.18);animation:floatCard 5s ease-in-out infinite;font-size:.77rem;font-weight:700;color:var(--pg3);white-space:nowrap;display:flex;align-items:center;gap:6px;">
            <i class="bi bi-patch-check-fill" style="color:#4a9e6a;"></i> Duyệt hồ sơ nhanh
          </div>
          <div style="position:absolute;bottom:-14px;left:-18px;background:#fff;border-radius:12px;padding:9px 14px;box-shadow:0 8px 24px rgba(0,0,0,.18);animation:floatCard 9s ease-in-out infinite reverse;font-size:.77rem;font-weight:700;color:var(--pg3);white-space:nowrap;display:flex;align-items:center;gap:6px;">
            <i class="bi bi-chat-dots-fill" style="color:var(--sg);"></i> Chat trực tiếp DN ↔ SV
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- Wave -->
  <div style="position:absolute;bottom:0;left:0;right:0;line-height:0;">
    <svg viewBox="0 0 1440 56" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none" style="display:block;width:100%;height:56px;">
      <path d="M0,56 C360,0 1080,56 1440,0 L1440,56 Z" fill="#ffffff"/>
    </svg>
  </div>
</section>

<!-- ══ STATS BAR ══ -->
<section style="background:#fff;padding:36px 0;border-bottom:1px solid rgba(141,184,124,.12);">
  <div class="container" style="max-width:1100px;">
    <div class="row g-4 text-center">
      <?php $si=[['bi-briefcase-fill',$stats['jobs'],'Vị trí thực tập','#4A6741'],['bi-building-fill',$stats['companies'],'Doanh nghiệp','#3a8a58'],['bi-mortarboard-fill',$stats['students'],'Sinh viên','#4a9e8a'],['bi-trophy-fill',$stats['completed'],'Đã hoàn thành','#c49a6c']];
      foreach($si as [$ic,$n,$lb,$c]):?>
      <div class="col-6 col-md-3">
        <div style="padding:14px;">
          <i class="bi <?=$ic?>" style="font-size:1.7rem;color:<?=$c?>;margin-bottom:8px;display:block;"></i>
          <div class="stat-num" data-target="<?=$n?>" style="font-family:'Plus Jakarta Sans',sans-serif;font-size:2.2rem;font-weight:900;color:var(--td);line-height:1;">0</div>
          <div style="font-size:.78rem;color:var(--tl);font-weight:500;margin-top:4px;"><?=$lb?></div>
        </div>
      </div>
      <?php endforeach;?>
    </div>
  </div>
</section>

<!-- ══ HOW IT WORKS ══ -->
<section style="padding:88px 0;background:var(--bg);">
  <div class="container" style="max-width:1140px;">
    <div class="text-center mb-5">
      <span style="background:rgba(74,103,65,.1);color:var(--pg);padding:6px 18px;border-radius:50px;font-size:.77rem;font-weight:700;display:inline-block;margin-bottom:14px;">🔄 QUY TRÌNH</span>
      <h2 style="font-family:'Plus Jakarta Sans',sans-serif;font-size:2.1rem;font-weight:900;color:var(--td);">Cách hoạt động</h2>
      <p style="color:var(--tl);font-size:.9rem;max-width:520px;margin:10px auto 0;">Từ đăng ký đến hoàn thành — toàn bộ được quản lý trên 1 nền tảng duy nhất</p>
    </div>
    <!-- Step connector line -->
    <div class="row g-4 position-relative">
      <div class="d-none d-lg-block" style="position:absolute;top:38px;left:calc(100%/12 + 20px);right:calc(100%/12 + 20px);height:2px;background:linear-gradient(90deg,var(--sg),var(--sm));z-index:0;border-radius:2px;"></div>
      <?php $steps=[
        ['1','bi-person-plus-fill','#4A6741','rgba(74,103,65,.12)','Đăng ký','Tạo tài khoản và hoàn thiện hồ sơ'],
        ['2','bi-search','#3a8a58','rgba(58,138,88,.12)','Tìm vị trí','Duyệt và ứng tuyển vị trí phù hợp'],
        ['3','bi-clipboard-check-fill','#c49a6c','rgba(196,154,108,.12)','Xét duyệt','Trường & DN xem xét hồ sơ'],
        ['4','bi-briefcase-fill','#4a9e8a','rgba(74,158,138,.12)','Thực tập','Bắt đầu với GVHD hỗ trợ'],
        ['5','bi-star-fill','#9a6a3a','rgba(154,106,58,.12)','Đánh giá','DN đánh giá, SV nộp báo cáo'],
        ['6','bi-trophy-fill','#4a6a9a','rgba(74,106,154,.12)','Hoàn thành','Nhận kết quả chính thức'],
      ]; foreach($steps as $idx=>[$n,$ic,$c,$bg,$t,$d]):?>
      <div class="col-6 col-md-4 col-lg-2" style="text-align:center;position:relative;z-index:1;">
        <div style="width:62px;height:62px;background:<?=$bg?>;border:2.5px solid <?=$c?>44;border-radius:18px;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;transition:all .2s;box-shadow:0 4px 14px <?=$c?>22;" onmouseover="this.style.transform='scale(1.1)';this.style.boxShadow='0 8px 24px <?=$c?>44'" onmouseout="this.style.transform='';this.style.boxShadow='0 4px 14px <?=$c?>22'">
          <i class="bi <?=$ic?>" style="font-size:1.45rem;color:<?=$c?>;"></i>
        </div>
        <div style="width:24px;height:24px;background:<?=$c?>;border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-size:.68rem;font-weight:800;margin:0 auto 10px;"><?=$n?></div>
        <div style="font-weight:700;font-size:.88rem;color:var(--td);margin-bottom:5px;"><?=$t?></div>
        <div style="font-size:.74rem;color:var(--tl);line-height:1.6;"><?=$d?></div>
      </div>
      <?php endforeach;?>
    </div>
  </div>
</section>

<!-- ══ LATEST JOBS ══ -->
<section style="padding:88px 0;background:#fff;">
  <div class="container" style="max-width:1140px;">
    <div style="display:flex;align-items:flex-end;justify-content:space-between;margin-bottom:40px;flex-wrap:wrap;gap:14px;">
      <div>
        <span style="background:rgba(74,103,65,.1);color:var(--pg);padding:6px 18px;border-radius:50px;font-size:.77rem;font-weight:700;display:inline-block;margin-bottom:10px;">💼 MỚI NHẤT</span>
        <h2 style="font-family:'Plus Jakarta Sans',sans-serif;font-size:1.95rem;font-weight:900;color:var(--td);margin:0;">Cơ hội thực tập hôm nay</h2>
      </div>
      <a href="<?=$base_pub?>/internships.php" style="padding:10px 22px;border:1.5px solid rgba(74,103,65,.25);border-radius:9px;color:var(--pg);font-weight:700;font-size:.84rem;transition:all .18s;white-space:nowrap;" onmouseover="this.style.background='rgba(74,103,65,.06)'" onmouseout="this.style.background=''">
        Xem tất cả <i class="bi bi-arrow-right ms-1"></i>
      </a>
    </div>
    <?php if(empty($jobs)):?>
    <div style="text-align:center;padding:70px 20px;background:var(--bg);border-radius:20px;">
      <i class="bi bi-briefcase" style="font-size:3rem;color:var(--sg);opacity:.4;display:block;margin-bottom:14px;"></i>
      <p style="color:var(--tl);font-size:.9rem;">Chưa có vị trí thực tập nào đang mở.</p>
    </div>
    <?php else:?>
    <div class="row g-4">
      <?php foreach($jobs as $j):
        $logo=!empty($j['logo'])?'/internship-management-system/internship_system/uploads/'.$j['logo']:null;
        $init=strtoupper(mb_substr($j['company_name']??'C',0,1));
        $cols=['#4A6741','#3a8a58','#4a9e8a','#c49a6c','#9a6a3a','#4a6a9a'];
        $col=$cols[abs(crc32($j['company_name']??''))%count($cols)];
        $loc=!empty($j['location'])?$j['location']:(!empty($j['address'])?substr($j['address'],0,28):'');
      ?>
      <div class="col-md-6 col-lg-4">
        <div style="background:#fff;border:1.5px solid rgba(141,184,124,.2);border-radius:18px;padding:22px;height:100%;display:flex;flex-direction:column;transition:all .25s;" onmouseover="this.style.transform='translateY(-5px)';this.style.boxShadow='0 14px 36px rgba(74,103,65,.14)';this.style.borderColor='rgba(74,103,65,.32)'" onmouseout="this.style.transform='';this.style.boxShadow='';this.style.borderColor='rgba(141,184,124,.2)'">
          <div style="display:flex;align-items:flex-start;gap:14px;margin-bottom:14px;">
            <?php if($logo):?>
            <img src="<?=$logo?>" style="width:48px;height:48px;border-radius:12px;object-fit:cover;border:1px solid rgba(0,0,0,.08);flex-shrink:0;">
            <?php else:?>
            <div style="width:48px;height:48px;border-radius:12px;background:<?=$col?>;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:800;font-size:1.15rem;flex-shrink:0;"><?=$init?></div>
            <?php endif;?>
            <div style="flex:1;min-width:0;">
              <div style="font-weight:800;font-size:.93rem;color:var(--td);margin-bottom:3px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?=htmlspecialchars($j['title']??'')?></div>
              <div style="color:var(--tl);font-size:.77rem;"><?=htmlspecialchars($j['company_name']??'')?></div>
            </div>
          </div>
          <div style="display:flex;flex-wrap:wrap;gap:6px;margin-bottom:12px;">
            <?php if($loc):?><span style="background:rgba(141,184,124,.15);color:var(--pg);padding:3px 10px;border-radius:20px;font-size:.71rem;font-weight:600;"><i class="bi bi-geo-alt-fill" style="font-size:.62rem;"></i> <?=htmlspecialchars($loc)?></span><?php endif;?>
            <?php if(!empty($j['quantity'])):?><span style="background:rgba(74,103,65,.1);color:var(--pg2);padding:3px 10px;border-radius:20px;font-size:.71rem;font-weight:600;"><i class="bi bi-people-fill" style="font-size:.62rem;"></i> <?=$j['quantity']?> chỗ</span><?php endif;?>
          </div>
          <?php if(!empty($j['description'])):?>
          <p style="color:var(--tl);font-size:.79rem;line-height:1.65;flex:1;margin:0 0 14px;overflow:hidden;display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;"><?=htmlspecialchars(substr($j['description'],0,150))?></p>
          <?php endif;?>
          <a href="<?=$base_sys?>/auth/login.php" style="display:flex;align-items:center;justify-content:center;gap:7px;padding:9px;border-radius:10px;background:linear-gradient(135deg,var(--pg),var(--pg2));color:#fff;font-weight:700;font-size:.83rem;transition:all .18s;" onmouseover="this.style.opacity='.88'" onmouseout="this.style.opacity='1'">
            <i class="bi bi-send-fill"></i> Ứng tuyển ngay
          </a>
        </div>
      </div>
      <?php endforeach;?>
    </div>
    <?php endif;?>
  </div>
</section>

<!-- ══ FEATURES ══ -->
<section style="padding:88px 0;background:var(--bg);">
  <div class="container" style="max-width:1140px;">
    <div class="text-center mb-5">
      <span style="background:rgba(74,103,65,.1);color:var(--pg);padding:6px 18px;border-radius:50px;font-size:.77rem;font-weight:700;display:inline-block;margin-bottom:14px;">⭐ TÍNH NĂNG</span>
      <h2 style="font-family:'Plus Jakarta Sans',sans-serif;font-size:2rem;font-weight:900;color:var(--td);">Tại sao chọn ISchool Internship?</h2>
      <p style="color:var(--tl);font-size:.9rem;max-width:500px;margin:10px auto 0;">Được thiết kế dành riêng cho hệ sinh thái trường — doanh nghiệp — sinh viên</p>
    </div>
    <div class="row g-4">
      <?php $feats=[
        ['bi-shield-check-fill','#4A6741','rgba(74,103,65,.1)','Quy trình chuẩn hóa','Toàn bộ từ ứng tuyển → phỏng vấn → thực tập → đánh giá — minh bạch và có kiểm soát chặt chẽ.'],
        ['bi-chat-dots-fill','#3a8a58','rgba(58,138,88,.1)','Nhắn tin trực tiếp','Sinh viên và doanh nghiệp chat ngay trên nền tảng để trao đổi và hẹn lịch phỏng vấn.'],
        ['bi-file-earmark-arrow-up-fill','#c49a6c','rgba(196,154,108,.1)','Upload CV & hồ sơ','Tải lên CV, ảnh đại diện và giấy tờ liên quan dễ dàng, lưu trữ bảo mật.'],
        ['bi-person-workspace','#4a9e8a','rgba(74,158,138,.1)','Giảng viên hướng dẫn','Mỗi sinh viên được phân công GVHD riêng, theo dõi và hỗ trợ trong toàn kỳ thực tập.'],
        ['bi-star-fill','#9a6a3a','rgba(154,106,58,.1)','Đánh giá chuyên nghiệp','Doanh nghiệp đánh giá kết quả, sinh viên nộp báo cáo và GVHD phê duyệt.'],
        ['bi-graph-up-arrow','#4a6a9a','rgba(74,106,154,.1)','Dashboard tổng quan','Admin theo dõi toàn bộ hệ thống qua dashboard trực quan với thống kê real-time.'],
      ]; foreach($feats as [$ic,$c,$bg,$t,$d]):?>
      <div class="col-md-6 col-lg-4">
        <div style="background:#fff;border-radius:18px;padding:26px;height:100%;border:1px solid rgba(141,184,124,.15);transition:all .22s;" onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 14px 32px rgba(74,103,65,.1)'" onmouseout="this.style.transform='';this.style.boxShadow=''">
          <div style="width:52px;height:52px;background:<?=$bg?>;border-radius:14px;display:flex;align-items:center;justify-content:center;margin-bottom:16px;">
            <i class="bi <?=$ic?>" style="font-size:1.35rem;color:<?=$c?>;"></i>
          </div>
          <h5 style="font-weight:800;font-size:.95rem;color:var(--td);margin-bottom:8px;"><?=$t?></h5>
          <p style="color:var(--tl);font-size:.81rem;line-height:1.7;margin:0;"><?=$d?></p>
        </div>
      </div>
      <?php endforeach;?>
    </div>
  </div>
</section>

<!-- ══ ROLES ══ -->
<section style="padding:88px 0;background:#fff;">
  <div class="container" style="max-width:1140px;">
    <div class="text-center mb-5">
      <span style="background:rgba(74,103,65,.1);color:var(--pg);padding:6px 18px;border-radius:50px;font-size:.77rem;font-weight:700;display:inline-block;margin-bottom:14px;">👥 VAI TRÒ</span>
      <h2 style="font-family:'Plus Jakarta Sans',sans-serif;font-size:2rem;font-weight:900;color:var(--td);">Dành cho tất cả mọi người</h2>
    </div>
    <div class="row g-4">
      <?php $roles=[
        ['🎓','Sinh viên','#4A6741','rgba(74,103,65,.08)','Tìm & ứng tuyển','Upload CV, nhắn tin DN, theo dõi thực tập và nộp báo cáo cuối kỳ.'],
        ['🏢','Doanh nghiệp','#3a8a58','rgba(58,138,88,.08)','Tuyển & quản lý','Đăng vị trí, duyệt hồ sơ, phỏng vấn và đánh giá sinh viên.'],
        ['👨‍🏫','Giảng viên','#4a9e8a','rgba(74,158,138,.08)','Hướng dẫn & duyệt','Hỗ trợ sinh viên trong quá trình TT và phê duyệt báo cáo.'],
        ['🛡️','Quản trị viên','#c49a6c','rgba(196,154,108,.08)','Quản lý hệ thống','Xét duyệt đơn, phân công GVHD và theo dõi tổng thể.'],
      ]; foreach($roles as [$em,$t,$c,$bg,$sub,$d]):?>
      <div class="col-md-6 col-lg-3">
        <div style="background:#fff;border-radius:18px;padding:28px;text-align:center;border:2px solid rgba(141,184,124,.18);height:100%;transition:all .22s;" onmouseover="this.style.borderColor='<?=$c?>';this.style.transform='translateY(-5px)';this.style.boxShadow='0 14px 32px <?=$c?>22'" onmouseout="this.style.borderColor='rgba(141,184,124,.18)';this.style.transform='';this.style.boxShadow=''">
          <div style="font-size:2.8rem;margin-bottom:12px;"><?=$em?></div>
          <div style="font-weight:800;font-size:1rem;color:<?=$c?>;margin-bottom:4px;"><?=$t?></div>
          <div style="font-size:.75rem;color:var(--tl);font-weight:600;margin-bottom:12px;text-transform:uppercase;letter-spacing:.5px;"><?=$sub?></div>
          <p style="color:var(--tl);font-size:.8rem;line-height:1.7;margin:0;"><?=$d?></p>
        </div>
      </div>
      <?php endforeach;?>
    </div>
  </div>
</section>

<!-- ══ CTA ══ -->
<section style="padding:80px 0;background:linear-gradient(135deg,var(--pg3),var(--pg2),#3a6e58);position:relative;overflow:hidden;">
  <div style="position:absolute;inset:0;background-image:radial-gradient(rgba(181,212,168,.08) 1px,transparent 1px);background-size:32px 32px;pointer-events:none;"></div>
  <div class="container text-center" style="max-width:680px;position:relative;">
    <div style="font-size:3rem;margin-bottom:16px;animation:floatY 6s ease-in-out infinite;display:inline-block;">🚀</div>
    <h2 style="font-family:'Plus Jakarta Sans',sans-serif;font-size:2.1rem;font-weight:900;color:#fff;margin-bottom:14px;">Sẵn sàng bắt đầu hành trình?</h2>
    <p style="color:rgba(255,255,255,.72);font-size:.94rem;line-height:1.75;margin-bottom:34px;">Đăng ký miễn phí và tham gia cùng sinh viên và doanh nghiệp trên nền tảng ISchool Internship.</p>
    <div style="display:flex;gap:14px;justify-content:center;flex-wrap:wrap;">
      <a href="<?=$base_sys?>/auth/register.php" style="padding:14px 32px;background:linear-gradient(135deg,var(--sm),var(--sg));border-radius:11px;color:var(--pg3);font-weight:800;font-size:.93rem;display:inline-flex;align-items:center;gap:8px;transition:all .22s;box-shadow:0 6px 22px rgba(181,212,168,.32);" onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform=''">
        <i class="bi bi-person-plus-fill"></i> Đăng ký sinh viên
      </a>
      <a href="<?=$base_sys?>/auth/register.php" style="padding:14px 32px;border:2px solid rgba(181,212,168,.38);border-radius:11px;color:rgba(255,255,255,.92);font-weight:700;font-size:.93rem;display:inline-flex;align-items:center;gap:8px;transition:all .22s;" onmouseover="this.style.background='rgba(181,212,168,.1)'" onmouseout="this.style.background=''">
        <i class="bi bi-building-fill"></i> Đăng ký doanh nghiệp
      </a>
    </div>
  </div>
</section>

<!-- ══ FOOTER ══ -->
<footer style="background:var(--pg3);padding:52px 0 24px;">
  <div class="container" style="max-width:1140px;">
    <div class="row g-5 mb-5">
      <div class="col-md-4">
        <div style="display:flex;align-items:center;gap:11px;margin-bottom:16px;">
          <div style="width:42px;height:42px;background:linear-gradient(135deg,var(--sm),var(--sg));border-radius:11px;display:flex;align-items:center;justify-content:center;font-size:1.1rem;">🎓</div>
          <div>
            <div style="font-family:'Plus Jakarta Sans',sans-serif;font-weight:800;color:#fff;font-size:.95rem;">ISchool Internship</div>
            <div style="font-size:.6rem;color:var(--sm);opacity:.7;">Management System</div>
          </div>
        </div>
        <p style="font-size:.8rem;line-height:1.8;color:rgba(255,255,255,.58);">Nền tảng quản lý thực tập toàn diện, kết nối sinh viên với cơ hội nghề nghiệp thực tế.</p>
        <div style="display:flex;gap:10px;margin-top:16px;">
          <?php foreach([['bi-facebook','#4267B2'],['bi-linkedin','#0077B5'],['bi-envelope-fill','#4a9e6a']] as [$ic,$c]):?>
          <div style="width:34px;height:34px;background:rgba(255,255,255,.08);border-radius:8px;display:flex;align-items:center;justify-content:center;cursor:pointer;transition:all .18s;" onmouseover="this.style.background='<?=$c?>44'" onmouseout="this.style.background='rgba(255,255,255,.08)'">
            <i class="bi <?=$ic?>" style="color:rgba(255,255,255,.7);font-size:.85rem;"></i>
          </div>
          <?php endforeach;?>
        </div>
      </div>
      <div class="col-6 col-md-2">
        <div style="font-weight:700;color:#fff;font-size:.82rem;margin-bottom:14px;text-transform:uppercase;letter-spacing:.8px;">Trang</div>
        <?php foreach([['Trang chủ',$base_pub.'/index.php'],['Vị trí TT',$base_pub.'/internships.php'],['Giới thiệu',$base_pub.'/about.php']] as [$l,$u]):?>
        <a href="<?=$u?>" style="display:block;color:rgba(255,255,255,.55);font-size:.8rem;margin-bottom:8px;transition:color .15s;" onmouseover="this.style.color='var(--sm)'" onmouseout="this.style.color='rgba(255,255,255,.55)'"><?=$l?></a>
        <?php endforeach;?>
      </div>
      <div class="col-6 col-md-2">
        <div style="font-weight:700;color:#fff;font-size:.82rem;margin-bottom:14px;text-transform:uppercase;letter-spacing:.8px;">Tài khoản</div>
        <a href="<?=$base_sys?>/auth/login.php" style="display:block;color:rgba(255,255,255,.55);font-size:.8rem;margin-bottom:8px;transition:color .15s;" onmouseover="this.style.color='var(--sm)'" onmouseout="this.style.color='rgba(255,255,255,.55)'">Đăng nhập</a>
        <a href="<?=$base_sys?>/auth/register.php" style="display:block;color:rgba(255,255,255,.55);font-size:.8rem;margin-bottom:8px;transition:color .15s;" onmouseover="this.style.color='var(--sm)'" onmouseout="this.style.color='rgba(255,255,255,.55)'">Đăng ký SV</a>
        <a href="<?=$base_sys?>/auth/register.php" style="display:block;color:rgba(255,255,255,.55);font-size:.8rem;transition:color .15s;" onmouseover="this.style.color='var(--sm)'" onmouseout="this.style.color='rgba(255,255,255,.55)'">Đăng ký DN</a>
      </div>
      <div class="col-md-4">
        <div style="font-weight:700;color:#fff;font-size:.82rem;margin-bottom:14px;text-transform:uppercase;letter-spacing:.8px;">Liên hệ</div>
        <?php foreach([['bi-geo-alt-fill','123 Nguyễn Văn Linh, Q.7, TP.HCM'],['bi-envelope-fill','info@ischool.edu.vn'],['bi-telephone-fill','1800 1234 (miễn phí)'],['bi-clock-fill','T2–T6: 8:00–17:30']] as [$ic,$txt]):?>
        <div style="display:flex;align-items:flex-start;gap:9px;margin-bottom:10px;">
          <i class="bi <?=$ic?>" style="color:var(--sm);font-size:.82rem;margin-top:2px;flex-shrink:0;"></i>
          <span style="font-size:.8rem;color:rgba(255,255,255,.55);line-height:1.5;"><?=$txt?></span>
        </div>
        <?php endforeach;?>
      </div>
    </div>
    <div style="border-top:1px solid rgba(255,255,255,.1);padding-top:20px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;">
      <div style="font-size:.74rem;color:rgba(255,255,255,.38);">© 2025 ISchool Internship Management System. All rights reserved.</div>
      <div style="font-size:.74rem;color:rgba(255,255,255,.38);">Designed with 💚 for students & companies</div>
    </div>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Count-up animation
function animateCount(el){
  const target=parseInt(el.dataset.target)||0;
  if(target===0){el.textContent='0';return;}
  let start=0,dur=1600,step=dur/60;
  const inc=target/60;
  const timer=setInterval(()=>{
    start+=inc; if(start>=target){el.textContent=target;clearInterval(timer);}
    else el.textContent=Math.floor(start);
  },step);
}
const obs=new IntersectionObserver((entries)=>{
  entries.forEach(e=>{if(e.isIntersecting){animateCount(e.target);obs.unobserve(e.target);}});
},{threshold:.4});
document.querySelectorAll('.stat-num').forEach(el=>obs.observe(el));
</script>
</body>
</html>
