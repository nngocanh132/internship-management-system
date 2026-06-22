<?php
$base_sys = '/internship-management-system/internship_system';
$base_pub = '/internship-management-system/public';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Giới thiệu — ISchool Internship</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@700;800;900&display=swap" rel="stylesheet">
<style>
:root{--pg:#5D7B6F;--pg2:#3D5A50;--pg3:#2A3F38;--sg:#A4C3A2;--sm:#B0D4B8;--bg:#EAE7D6;--td:#1A2E28;--tm:#4A6058;--tl:#7A9590;}
*,*::before,*::after{box-sizing:border-box}
body{font-family:'Inter',sans-serif;background:#fff;color:var(--td);margin:0;padding-top:56px;}
::-webkit-scrollbar{width:5px}::-webkit-scrollbar-thumb{background:var(--sg);border-radius:4px}
/* NAV - defined in _nav.php */
/* ANIMATIONS */
@keyframes fadeUp{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:none}}
@keyframes floatY{0%,100%{transform:translateY(0)}50%{transform:translateY(-12px)}}
</style>
</head>
<body>
<?php require_once __DIR__.'/_nav.php'; ?>

<!-- HERO -->
<section style="background:linear-gradient(148deg,var(--pg3),var(--pg2),#3a6e58);padding:80px 0 70px;position:relative;overflow:hidden;">
  <div style="position:absolute;inset:0;background-image:radial-gradient(rgba(181,212,168,.1) 1px,transparent 1px);background-size:30px 30px;pointer-events:none;"></div>
  <div style="position:absolute;width:400px;height:400px;border-radius:50%;background:radial-gradient(circle,rgba(141,184,124,.14),transparent 70%);top:-100px;right:-80px;animation:floatY 10s ease-in-out infinite;pointer-events:none;"></div>
  <div class="container" style="max-width:1000px;text-align:center;position:relative;z-index:1;">
    <div style="display:inline-block;font-size:3.5rem;margin-bottom:18px;animation:floatY 5s ease-in-out infinite;">🎓</div>
    <h1 style="font-family:'Plus Jakarta Sans',sans-serif;font-size:clamp(1.9rem,4vw,2.8rem);font-weight:900;color:#fff;margin-bottom:16px;animation:fadeUp .6s ease both;">Về ISchool Internship</h1>
    <p style="color:rgba(255,255,255,.75);font-size:.97rem;max-width:600px;margin:0 auto;line-height:1.8;animation:fadeUp .6s .12s ease both;">
      Nền tảng quản lý thực tập toàn diện — kết nối sinh viên với cơ hội nghề nghiệp thực tế từ các doanh nghiệp hàng đầu, với sự hỗ trợ sát sao từ đội ngũ giảng viên.
    </p>
    <!-- Breadcrumb -->
    <div style="display:flex;align-items:center;justify-content:center;gap:8px;margin-top:24px;font-size:.8rem;color:rgba(255,255,255,.5);">
      <a href="<?=$base_pub?>/index.php" style="color:rgba(255,255,255,.65);text-decoration:none;" onmouseover="this.style.color='#B5D4A8'" onmouseout="this.style.color='rgba(255,255,255,.65)'"><i class="bi bi-house-fill"></i> Trang chủ</a>
      <i class="bi bi-chevron-right" style="font-size:.65rem;"></i>
      <span style="color:var(--sm);font-weight:600;">Giới thiệu</span>
    </div>
  </div>
  <div style="position:absolute;bottom:0;left:0;right:0;line-height:0;">
    <svg viewBox="0 0 1440 50" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none" style="display:block;width:100%;height:50px;">
      <path d="M0,50 C480,0 960,50 1440,0 L1440,50 Z" fill="#ffffff"/>
    </svg>
  </div>
</section>

<!-- MISSION -->
<section style="padding:88px 0;background:#fff;">
  <div class="container" style="max-width:1120px;">
    <div class="row g-5 align-items-center">
      <div class="col-lg-6">
        <span style="background:rgba(74,103,65,.1);color:var(--pg);padding:6px 18px;border-radius:50px;font-size:.77rem;font-weight:700;display:inline-block;margin-bottom:16px;">🎯 SỨ MỆNH</span>
        <h2 style="font-family:'Plus Jakarta Sans',sans-serif;font-size:1.95rem;font-weight:900;color:var(--td);margin-bottom:18px;">Kết nối tri thức<br>với thực tiễn nghề nghiệp</h2>
        <p style="color:var(--tl);line-height:1.85;font-size:.92rem;margin-bottom:16px;">Chúng tôi tin rằng mỗi sinh viên xứng đáng có cơ hội trải nghiệm môi trường làm việc thực tế trước khi tốt nghiệp. ISchool Internship ra đời để biến điều đó thành hiện thực.</p>
        <p style="color:var(--tl);line-height:1.85;font-size:.92rem;margin-bottom:28px;">Nền tảng đơn giản hóa toàn bộ quy trình — từ tìm kiếm vị trí, nộp đơn, phỏng vấn đến đánh giá kết quả — giúp mọi bên tiết kiệm thời gian và tập trung vào điều quan trọng nhất.</p>
        <div style="display:flex;gap:14px;flex-wrap:wrap;">
          <?php foreach([['2024','Năm thành lập'],['100%','Miễn phí cho SV'],['4','Roles rõ ràng'],['12','Bảng dữ liệu']] as [$n,$lb]):?>
          <div style="background:var(--bg);border-radius:14px;padding:16px 20px;text-align:center;min-width:90px;">
            <div style="font-family:'Plus Jakarta Sans',sans-serif;font-size:1.65rem;font-weight:900;color:var(--pg);"><?=$n?></div>
            <div style="font-size:.72rem;color:var(--tl);margin-top:2px;"><?=$lb?></div>
          </div>
          <?php endforeach;?>
        </div>
      </div>
      <div class="col-lg-6">
        <div style="background:linear-gradient(145deg,var(--pg3),var(--pg2));border-radius:22px;padding:38px;color:#fff;position:relative;overflow:hidden;">
          <div style="position:absolute;top:-40px;right:-40px;width:200px;height:200px;border-radius:50%;background:radial-gradient(circle,rgba(181,212,168,.12),transparent 70%);"></div>
          <h4 style="font-family:'Plus Jakarta Sans',sans-serif;font-weight:800;font-size:1.1rem;margin-bottom:26px;position:relative;z-index:1;">🌟 Tầm nhìn của chúng tôi</h4>
          <?php $visions=[
            ['🎯','Chuẩn hóa quy trình','Đưa toàn bộ chu trình thực tập vào một nền tảng duy nhất, minh bạch và có kiểm soát.'],
            ['🤝','Kết nối bền vững','Xây dựng quan hệ lâu dài giữa nhà trường, sinh viên và doanh nghiệp.'],
            ['📈','Phát triển liên tục','Không ngừng cải tiến dựa trên phản hồi từ cộng đồng người dùng.'],
            ['🌍','Mở rộng quy mô','Hướng đến phục vụ toàn bộ hệ thống trường iSchool trên cả nước.'],
          ]; foreach($visions as [$em,$t,$d]):?>
          <div style="display:flex;gap:14px;margin-bottom:18px;position:relative;z-index:1;">
            <div style="font-size:1.3rem;flex-shrink:0;margin-top:2px;"><?=$em?></div>
            <div>
              <div style="font-weight:700;font-size:.88rem;margin-bottom:4px;"><?=$t?></div>
              <div style="font-size:.79rem;opacity:.72;line-height:1.65;"><?=$d?></div>
            </div>
          </div>
          <?php endforeach;?>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- CORE VALUES -->
<section style="padding:88px 0;background:var(--bg);">
  <div class="container" style="max-width:1120px;">
    <div class="text-center mb-5">
      <span style="background:rgba(74,103,65,.1);color:var(--pg);padding:6px 18px;border-radius:50px;font-size:.77rem;font-weight:700;display:inline-block;margin-bottom:14px;">💎 GIÁ TRỊ CỐT LÕI</span>
      <h2 style="font-family:'Plus Jakarta Sans',sans-serif;font-size:2rem;font-weight:900;color:var(--td);">Những gì chúng tôi tin tưởng</h2>
    </div>
    <div class="row g-4">
      <?php $values=[
        ['bi-shield-check-fill','#4A6741','rgba(74,103,65,.1)','Minh bạch','Mọi bước trong quy trình đều được ghi nhận và theo dõi bởi tất cả bên liên quan.'],
        ['bi-people-fill','#3a8a58','rgba(58,138,88,.1)','Cộng đồng','Xây dựng hệ sinh thái nơi sinh viên, doanh nghiệp và nhà trường cùng phát triển.'],
        ['bi-lightning-charge-fill','#c49a6c','rgba(196,154,108,.1)','Hiệu quả','Tối giản thủ tục, giảm giấy tờ — để mọi người tập trung vào công việc thực sự.'],
        ['bi-heart-fill','#9a3a5a','rgba(154,58,90,.1)','Tận tâm','Từng tính năng được thiết kế với sự quan tâm đến trải nghiệm từng người dùng.'],
      ]; foreach($values as [$ic,$c,$bg,$t,$d]):?>
      <div class="col-md-6 col-lg-3">
        <div style="background:#fff;border-radius:18px;padding:26px;height:100%;border:1px solid rgba(141,184,124,.15);transition:all .22s;text-align:center;" onmouseover="this.style.transform='translateY(-5px)';this.style.boxShadow='0 14px 32px rgba(74,103,65,.1)'" onmouseout="this.style.transform='';this.style.boxShadow=''">
          <div style="width:58px;height:58px;background:<?=$bg?>;border-radius:16px;display:flex;align-items:center;justify-content:center;margin:0 auto 18px;">
            <i class="bi <?=$ic?>" style="font-size:1.5rem;color:<?=$c?>;"></i>
          </div>
          <h5 style="font-weight:800;font-size:.97rem;margin-bottom:10px;"><?=$t?></h5>
          <p style="color:var(--tl);font-size:.81rem;line-height:1.75;margin:0;"><?=$d?></p>
        </div>
      </div>
      <?php endforeach;?>
    </div>
  </div>
</section>

<!-- ROLES -->
<section style="padding:88px 0;background:#fff;">
  <div class="container" style="max-width:1120px;">
    <div class="text-center mb-5">
      <span style="background:rgba(74,103,65,.1);color:var(--pg);padding:6px 18px;border-radius:50px;font-size:.77rem;font-weight:700;display:inline-block;margin-bottom:14px;">👥 VAI TRÒ</span>
      <h2 style="font-family:'Plus Jakarta Sans',sans-serif;font-size:2rem;font-weight:900;color:var(--td);">Ai tham gia vào hệ thống?</h2>
      <p style="color:var(--tl);font-size:.9rem;max-width:500px;margin:10px auto 0;">Mỗi vai trò có giao diện và luồng riêng biệt, tối ưu cho từng đối tượng</p>
    </div>
    <div class="row g-4">
      <?php $roles=[
        ['🎓','Sinh viên','#4A6741','rgba(74,103,65,.08)','Tìm kiếm & ứng tuyển','Upload CV, tìm vị trí TT phù hợp, nhắn tin với DN, theo dõi tiến độ và nộp báo cáo cuối kỳ.','Đăng ký →',$base_sys.'/auth/register.php'],
        ['🏢','Doanh nghiệp','#3a8a58','rgba(58,138,88,.08)','Tuyển dụng & quản lý','Đăng vị trí TT, duyệt hồ sơ, phỏng vấn, đánh giá kết quả và nhắn tin với sinh viên.','Đăng ký →',$base_sys.'/auth/register.php'],
        ['👨‍🏫','Giảng viên','#4a9e8a','rgba(74,158,138,.08)','Hướng dẫn & duyệt','Được phân công hướng dẫn SV, trao đổi qua chat và phê duyệt báo cáo thực tập.','Xem thêm →',$base_pub.'/about.php'],
        ['🛡️','Quản trị viên','#c49a6c','rgba(196,154,108,.08)','Quản lý hệ thống','Xét duyệt đơn ứng tuyển (bước 1), phân công GVHD và theo dõi toàn bộ hệ thống.','Đăng nhập →',$base_sys.'/auth/login.php'],
      ]; foreach($roles as [$em,$t,$c,$bg,$sub,$d,$btn,$url]):?>
      <div class="col-md-6 col-lg-3">
        <div style="background:#fff;border-radius:18px;padding:28px;border:2px solid rgba(141,184,124,.18);height:100%;display:flex;flex-direction:column;transition:all .22s;" onmouseover="this.style.borderColor='<?=$c?>';this.style.transform='translateY(-5px)';this.style.boxShadow='0 14px 32px <?=$c?>22'" onmouseout="this.style.borderColor='rgba(141,184,124,.18)';this.style.transform='';this.style.boxShadow=''">
          <div style="font-size:2.8rem;margin-bottom:12px;text-align:center;"><?=$em?></div>
          <div style="font-weight:800;font-size:1.02rem;color:<?=$c?>;margin-bottom:4px;text-align:center;"><?=$t?></div>
          <div style="font-size:.72rem;color:var(--tl);font-weight:600;margin-bottom:14px;text-transform:uppercase;letter-spacing:.5px;text-align:center;"><?=$sub?></div>
          <p style="color:var(--tl);font-size:.81rem;line-height:1.7;flex:1;"><?=$d?></p>
          <a href="<?=$url?>" style="display:block;text-align:center;padding:9px;border-radius:9px;background:<?=$c?>18;color:<?=$c?>;font-weight:700;font-size:.82rem;transition:all .18s;margin-top:14px;border:1.5px solid <?=$c?>33;" onmouseover="this.style.background='<?=$c?>28'" onmouseout="this.style.background='<?=$c?>18'"><?=$btn?></a>
        </div>
      </div>
      <?php endforeach;?>
    </div>
  </div>
</section>

<!-- TIMELINE -->
<section style="padding:88px 0;background:var(--bg);">
  <div class="container" style="max-width:800px;">
    <div class="text-center mb-5">
      <span style="background:rgba(74,103,65,.1);color:var(--pg);padding:6px 18px;border-radius:50px;font-size:.77rem;font-weight:700;display:inline-block;margin-bottom:14px;">📅 HÀNH TRÌNH</span>
      <h2 style="font-family:'Plus Jakarta Sans',sans-serif;font-size:2rem;font-weight:900;color:var(--td);">Quá trình phát triển</h2>
    </div>
    <?php $timeline=[
      ['Q1/2024','#4A6741','Khởi đầu','Ý tưởng hình thành từ nhu cầu thực tế: quản lý thực tập bằng Excel không còn hiệu quả.'],
      ['Q2/2024','#3a8a58','Nghiên cứu & thiết kế','Phân tích quy trình thực tập, thiết kế 12 bảng dữ liệu và luồng nghiệp vụ 4 role.'],
      ['Q3/2024','#c49a6c','Phát triển','Xây dựng toàn bộ hệ thống: auth, dashboard, modules, messaging và file upload.'],
      ['Q4/2024','#4a9e8a','Thử nghiệm','Beta test với nhóm sinh viên và doanh nghiệp đầu tiên, thu thập phản hồi.'],
      ['2025','#4a6a9a','Ra mắt chính thức','Triển khai rộng rãi với đầy đủ tính năng, bắt đầu phục vụ toàn trường.'],
    ]; foreach($timeline as $i=>[$yr,$c,$t,$d]):?>
    <div style="display:flex;gap:20px;margin-bottom:28px;align-items:flex-start;">
      <div style="display:flex;flex-direction:column;align-items:center;flex-shrink:0;">
        <div style="width:44px;height:44px;border-radius:50%;background:<?=$c?>;display:flex;align-items:center;justify-content:center;color:#fff;font-size:.72rem;font-weight:800;text-align:center;line-height:1.2;"><?=$yr?></div>
        <?php if($i<count($timeline)-1):?>
        <div style="width:2px;background:linear-gradient(<?=$c?>,rgba(141,184,124,.2));height:52px;margin-top:4px;"></div>
        <?php endif;?>
      </div>
      <div style="background:#fff;border-radius:14px;padding:18px 22px;flex:1;border:1px solid rgba(141,184,124,.18);box-shadow:0 2px 10px rgba(74,103,65,.06);">
        <div style="font-weight:800;font-size:.92rem;color:<?=$c?>;margin-bottom:6px;"><?=$t?></div>
        <p style="color:var(--tl);font-size:.82rem;line-height:1.7;margin:0;"><?=$d?></p>
      </div>
    </div>
    <?php endforeach;?>
  </div>
</section>

<!-- CTA -->
<section style="padding:80px 0;background:linear-gradient(135deg,var(--pg3),var(--pg2),#3a6e58);position:relative;overflow:hidden;">
  <div style="position:absolute;inset:0;background-image:radial-gradient(rgba(181,212,168,.08) 1px,transparent 1px);background-size:28px 28px;pointer-events:none;"></div>
  <div class="container text-center" style="max-width:660px;position:relative;">
    <h2 style="font-family:'Plus Jakarta Sans',sans-serif;font-size:2rem;font-weight:900;color:#fff;margin-bottom:14px;">Tham gia ngay hôm nay</h2>
    <p style="color:rgba(255,255,255,.72);font-size:.93rem;line-height:1.75;margin-bottom:32px;">Đăng ký miễn phí và trải nghiệm nền tảng quản lý thực tập hiện đại nhất của ISchool.</p>
    <div style="display:flex;gap:14px;justify-content:center;flex-wrap:wrap;">
      <a href="<?=$base_sys?>/auth/register.php" style="padding:13px 28px;background:linear-gradient(135deg,var(--sm),var(--sg));border-radius:11px;color:var(--pg3);font-weight:800;font-size:.9rem;display:inline-flex;align-items:center;gap:8px;transition:all .22s;box-shadow:0 6px 20px rgba(181,212,168,.3);" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform=''">
        <i class="bi bi-person-plus-fill"></i> Đăng ký ngay
      </a>
    </div>
  </div>
</section>

<!-- FOOTER -->
<footer style="background:var(--pg3);padding:32px 0;text-align:center;">
  <div class="container" style="max-width:1140px;">
    <div style="display:flex;align-items:center;justify-content:center;gap:11px;margin-bottom:16px;">
      <div style="width:36px;height:36px;background:linear-gradient(135deg,var(--sm),var(--sg));border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:.95rem;">🎓</div>
      <span style="font-family:'Plus Jakarta Sans',sans-serif;font-weight:800;color:#fff;font-size:.9rem;">ISchool Internship</span>
    </div>
    <div style="display:flex;align-items:center;justify-content:center;gap:20px;flex-wrap:wrap;margin-bottom:14px;">
      <?php foreach([['Trang chủ',$base_pub.'/index.php'],['Vị trí TT',$base_pub.'/internships.php'],['Giới thiệu',$base_pub.'/about.php'],['Đăng nhập',$base_sys.'/auth/login.php']] as [$l,$u]):?>
      <a href="<?=$u?>" style="color:rgba(255,255,255,.55);font-size:.8rem;transition:color .15s;" onmouseover="this.style.color='var(--sm)'" onmouseout="this.style.color='rgba(255,255,255,.55)'"><?=$l?></a>
      <?php endforeach;?>
    </div>
    <div style="font-size:.73rem;color:rgba(255,255,255,.32);">© 2025 ISchool Internship Management System. All rights reserved.</div>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
