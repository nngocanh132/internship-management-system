<?php
// Shared navbar component (NO DOCTYPE here — each page has its own)
if(!isset($base_sys)) $base_sys = '/internship-management-system/internship_system';
if(!isset($base_pub)) $base_pub = '/internship-management-system/public';
$cur_pub = $_SERVER['REQUEST_URI'];
if(!function_exists('_pubActive')){
  function _pubActive($p){ global $cur_pub; return (strpos($cur_pub,$p)!==false)?'pub-active':''; }
}
?>
<nav class="pub-nav" id="pubNav">
  <div class="nav-inner">
    <a href="<?=$base_pub?>/index.php" class="nav-brand">
      <div class="nav-logo-box"><i class="bi bi-mortarboard-fill"></i></div>
      <div>
        <div class="brand-nm">ISchool Internship</div>
        <div class="brand-sb">Management System</div>
      </div>
    </a>
    <div class="nav-links">
      <a href="<?=$base_pub?>/index.php"       class="nav-lk <?=_pubActive('/public/index')?>"><i class="bi bi-house-fill"></i> Trang chủ</a>
      <a href="<?=$base_pub?>/internships.php"  class="nav-lk <?=_pubActive('/public/internships')?>"><i class="bi bi-briefcase-fill"></i> Vị trí TT</a>
      <a href="<?=$base_pub?>/about.php"        class="nav-lk <?=_pubActive('/public/about')?>"><i class="bi bi-info-circle-fill"></i> Giới thiệu</a>
    </div>
    <div class="nav-acts">
      <a href="<?=$base_sys?>/auth/login.php"    class="btn-nav-login"><i class="bi bi-box-arrow-in-right"></i> Đăng nhập</a>
      <a href="<?=$base_sys?>/auth/register.php" class="btn-nav-reg"><i class="bi bi-person-plus-fill"></i> Đăng ký</a>
    </div>
    <button class="nav-ham" id="navHam" onclick="toggleMobileNav()" aria-label="Menu">
      <i class="bi bi-list" id="hamIcon"></i>
    </button>
  </div>
  <div class="nav-drawer" id="navDrawer">
    <a href="<?=$base_pub?>/index.php"       class="nav-lk <?=_pubActive('/public/index')?>"><i class="bi bi-house-fill"></i> Trang chủ</a>
    <a href="<?=$base_pub?>/internships.php"  class="nav-lk <?=_pubActive('/public/internships')?>"><i class="bi bi-briefcase-fill"></i> Vị trí thực tập</a>
    <a href="<?=$base_pub?>/about.php"        class="nav-lk <?=_pubActive('/public/about')?>"><i class="bi bi-info-circle-fill"></i> Giới thiệu</a>
    <div style="display:flex;gap:8px;padding-top:10px;border-top:1px solid rgba(141,184,124,.2);margin-top:6px;">
      <a href="<?=$base_sys?>/auth/login.php"    class="btn-nav-login" style="flex:1;text-align:center;justify-content:center;display:flex;align-items:center;gap:6px;"><i class="bi bi-box-arrow-in-right"></i> Đăng nhập</a>
      <a href="<?=$base_sys?>/auth/register.php" class="btn-nav-reg"   style="flex:1;text-align:center;justify-content:center;display:flex;align-items:center;gap:6px;"><i class="bi bi-person-plus-fill"></i> Đăng ký</a>
    </div>
  </div>
</nav>
<script>
function toggleMobileNav(){
  var d=document.getElementById('navDrawer'),i=document.getElementById('hamIcon');
  var o=d.classList.toggle('open');
  i.className=o?'bi bi-x-lg':'bi bi-list';
}
window.addEventListener('scroll',function(){
  document.getElementById('pubNav').classList.toggle('scrolled',window.scrollY>24);
});
</script>
