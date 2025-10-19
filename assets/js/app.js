// Chat modal handlers
document.getElementById('chatModal')?.addEventListener('show.bs.modal', (e) => {
  const btn = e.relatedTarget;
  const title = btn?.getAttribute('data-prop-title') || '';
  const el = document.getElementById('chatModalSub');
  if (el) el.textContent = title ? ('Объект: ' + title) : '';
});

document.getElementById('chatForm')?.addEventListener('submit', function(ev){
  ev.preventDefault();
  const input = document.getElementById('chatInput');
  const text = input.value.trim();
  if(!text) return;
  const wrap = document.getElementById('chatMessages');
  const div = document.createElement('div');
  div.textContent = 'Вы: ' + text;
  wrap.appendChild(div);
  input.value='';
  wrap.parentElement.scrollTop = wrap.parentElement.scrollHeight;
});

// Lightweight image slider controls
window.slideNext = function(btn){
  const slider = btn.closest('.img-slider');
  if(!slider) return;
  const slides = slider.querySelectorAll('.slide');
  if(slides.length===0) return;
  let idx = 0;
  slides.forEach((s,i)=>{ if(s.classList.contains('active')) idx=i; });
  slides[idx].classList.remove('active');
  const next = (idx+1) % slides.length;
  slides[next].classList.add('active');
}

window.slidePrev = function(btn){
  const slider = btn.closest('.img-slider');
  if(!slider) return;
  const slides = slider.querySelectorAll('.slide');
  if(slides.length===0) return;
  let idx = 0;
  slides.forEach((s,i)=>{ if(s.classList.contains('active')) idx=i; });
  slides[idx].classList.remove('active');
  const prev = (idx-1+slides.length) % slides.length;
  slides[prev].classList.add('active');
}



function escapeHtml(s){
  return String(s||'').replace(/[&<>"']/g, c=>({"&":"&amp;","<":"&lt;",">":"&gt;","\"":"&quot;","'":"&#39;"}[c]));
}




