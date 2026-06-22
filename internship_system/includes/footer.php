  </div><!-- /main-content -->
</div><!-- /main-wrap -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Animate stat numbers
document.querySelectorAll('.s-num').forEach(el=>{
  const n=parseInt(el.textContent)||0;
  if(!n) return;
  let c=0,step=Math.max(1,Math.ceil(n/35));
  const t=setInterval(()=>{c=Math.min(c+step,n);el.textContent=c;if(c>=n)clearInterval(t)},25);
});
// Skill checkboxes style
document.querySelectorAll('label:has(input[type=checkbox])').forEach(l=>{
  const cb=l.querySelector('input');
  const upd=()=>{l.style.background=cb.checked?'rgba(93,123,111,.12)':'';
    l.style.borderColor=cb.checked?'var(--ds)':'';
    l.style.color=cb.checked?'var(--ds)':''};
  upd(); cb.addEventListener('change',upd);
});
// Auto-close flash after 4s
setTimeout(()=>document.querySelectorAll('.alert.fade.show').forEach(a=>a.classList.remove('show')),4000);
</script>
</body>
</html>
