<?php
// Shared navbar component
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
      <a href="<?=$base_pub?>/index.php"       class="nav-lk <?=_pubActive('/public/index')?>"><i class="bi bi-house-fill"></i> Trang ch&#7911;</a>
      <a href="<?=$base_pub?>/internships.php"  class="nav-lk <?=_pubActive('/public/internships')?>"><i class="bi bi-briefcase-fill"></i> V&#7883; tr&#237; TT</a>
      <a href="<?=$base_pub?>/about.php"        class="nav-lk <?=_pubActive('/public/about')?>"><i class="bi bi-info-circle-fill"></i> Gi&#7899;i thi&#7879;u</a>
    </div>
    <div class="nav-acts">
      <a href="<?=$base_sys?>/auth/login.php"    class="btn-nav-login"><i class="bi bi-box-arrow-in-right"></i> &#272;&#259;ng nh&#7853;p</a>
      <a href="<?=$base_sys?>/auth/register.php" class="btn-nav-reg"><i class="bi bi-person-plus-fill"></i> &#272;&#259;ng k&#253;</a>
    </div>
    <button class="nav-ham" id="navHam" onclick="toggleMobileNav()" aria-label="Menu">
      <i class="bi bi-list" id="hamIcon"></i>
    </button>
  </div>
  <div class="nav-drawer" id="navDrawer">
    <a href="<?=$base_pub?>/index.php"       class="nav-lk <?=_pubActive('/public/index')?>"><i class="bi bi-house-fill"></i> Trang ch&#7911;</a>
    <a href="<?=$base_pub?>/internships.php"  class="nav-lk <?=_pubActive('/public/internships')?>"><i class="bi bi-briefcase-fill"></i> V&#7883; tr&#237; th&#7921;c t&#7853;p</a>
    <a href="<?=$base_pub?>/about.php"        class="nav-lk <?=_pubActive('/public/about')?>"><i class="bi bi-info-circle-fill"></i> Gi&#7899;i thi&#7879;u</a>
    <div style="display:flex;gap:8px;padding-top:10px;border-top:1px solid rgba(164,195,162,.25);margin-top:6px;">
      <a href="<?=$base_sys?>/auth/login.php"    class="btn-nav-login" style="flex:1;justify-content:center;display:flex;align-items:center;gap:6px;"><i class="bi bi-box-arrow-in-right"></i> &#272;&#259;ng nh&#7853;p</a>
      <a href="<?=$base_sys?>/auth/register.php" class="btn-nav-reg"   style="flex:1;justify-content:center;display:flex;align-items:center;gap:6px;"><i class="bi bi-person-plus-fill"></i> &#272;&#259;ng k&#253;</a>
    </div>
  </div>
</nav>

<style>
/* Nav styles dùng chung palette với login/register */
.pub-nav{position:fixed;top:0;left:0;right:0;z-index:1000;
  background:rgba(255,255,255,.92);backdrop-filter:blur(16px);-webkit-backdrop-filter:blur(16px);
  border-bottom:1px solid rgba(164,195,162,.22);
  transition:background .3s,box-shadow .3s;height:56px;}
.pub-nav.scrolled{background:rgba(255,255,255,.98);box-shadow:0 2px 14px rgba(42,63,56,.1);}
.nav-inner{max-width:1200px;margin:0 auto;display:flex;align-items:center;padding:0 24px;height:56px;}
.nav-brand{display:flex;align-items:center;gap:10px;flex-shrink:0;margin-right:28px;text-decoration:none;}
.nav-logo-box{width:36px;height:36px;background:linear-gradient(135deg,#5D7B6F,#A4C3A2);
  border-radius:9px;display:flex;align-items:center;justify-content:center;
  color:#fff;font-size:1rem;box-shadow:0 3px 10px rgba(93,123,111,.3);
  transition:transform .2s;flex-shrink:0;}
.nav-brand:hover .nav-logo-box{transform:rotate(-5deg) scale(1.06)}
.brand-nm{font-family:'Plus Jakarta Sans',sans-serif;font-size:.9rem;font-weight:800;color:#2A3F38;}
.brand-sb{font-size:.57rem;color:#7A9590;}
.nav-links{display:flex;align-items:center;gap:2px;flex:1;}
.nav-lk{display:flex;align-items:center;gap:6px;padding:6px 12px;border-radius:8px;
  color:#4A6058;font-size:.83rem;font-weight:500;transition:all .18s;text-decoration:none;}
.nav-lk i{font-size:.76rem;opacity:.6;}
.nav-lk:hover{background:rgba(164,195,162,.14);color:#5D7B6F;}
.nav-lk.pub-active{background:rgba(93,123,111,.1);color:#5D7B6F;font-weight:700;}
.nav-acts{display:flex;align-items:center;gap:9px;flex-shrink:0;}
.btn-nav-login{padding:7px 16px;border-radius:8px;
  border:1.5px solid rgba(164,195,162,.5);color:#5D7B6F;
  font-size:.82rem;font-weight:700;transition:all .18s;
  background:transparent;display:inline-flex;align-items:center;gap:5px;text-decoration:none;}
.btn-nav-login:hover{background:rgba(164,195,162,.12);color:#3D5A50;}
.btn-nav-reg{padding:7px 16px;border-radius:8px;
  background:linear-gradient(135deg,#5D7B6F,#3D5A50);color:#fff;
  font-size:.82rem;font-weight:700;transition:all .2s;
  display:inline-flex;align-items:center;gap:5px;text-decoration:none;
  box-shadow:0 3px 10px rgba(93,123,111,.25);}
.btn-nav-reg:hover{transform:translateY(-2px);box-shadow:0 5px 16px rgba(93,123,111,.35);color:#fff;}
.nav-ham{display:none;background:none;border:none;font-size:1.4rem;
  color:#5D7B6F;padding:5px;cursor:pointer;border-radius:7px;}
.nav-ham:hover{background:rgba(164,195,162,.14);}
.nav-drawer{display:none;flex-direction:column;gap:2px;padding:10px 14px 14px;
  background:rgba(255,255,255,.99);border-bottom:1px solid rgba(164,195,162,.2);
  box-shadow:0 6px 20px rgba(42,63,56,.08);}
.nav-drawer.open{display:flex;}
@media(max-width:820px){.nav-links,.nav-acts{display:none;}.nav-ham{display:block;}}
</style>

<script>
function toggleMobileNav(){
  var d=document.getElementById('navDrawer'),i=document.getElementById('hamIcon');
  var o=d.classList.toggle('open');
  i.className=o?'bi bi-x-lg':'bi bi-list';
}
window.addEventListener('scroll',function(){
  document.getElementById('pubNav').classList.toggle('scrolled',window.scrollY>20);
});
</script>
