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

// Map rendering with Leaflet + Nominatim geocoding (basic, no API key)
window.initMapAndLoadMarkers = async function(appRoot){
  const mapEl = document.getElementById('offerMap');
  if (!mapEl || !window.L) return;
  const map = L.map('offerMap').setView([55.751244, 37.618423], 11);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '&copy; OpenStreetMap' }).addTo(map);
  try{
    const url = (appRoot || '') + '/api/properties.php' + window.location.search;
    const res = await fetch(url);
    const data = await res.json();
    const items = data.items || [];
    for (const it of items) {
      const q = encodeURIComponent(it.address||'');
      if(!q) continue;
      const geoUrl = `https://nominatim.openstreetmap.org/search?format=json&q=${q}&limit=1`;
      const gr = await fetch(geoUrl, { headers: { 'Accept-Language': 'ru' } });
      const gj = await gr.json();
      if (gj && gj[0]) {
        const lat = parseFloat(gj[0].lat), lon = parseFloat(gj[0].lon);
        const m = L.marker([lat, lon]).addTo(map);
        const price = (it.price_per_month||0).toLocaleString('ru-RU');
        m.bindPopup(`<strong>${escapeHtml(it.title||'')}</strong><br>${escapeHtml(it.address||'')}<br>${price} ₽/мес`);
      }
    }
  }catch(e){ /* silent */ }
}

function escapeHtml(s){
  return String(s||'').replace(/[&<>"']/g, c=>({"&":"&amp;","<":"&lt;",">":"&gt;","\"":"&quot;","'":"&#39;"}[c]));
}




