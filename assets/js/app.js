

(function() {
    'use strict';
    

    document.addEventListener('DOMContentLoaded', () => {
        initRecommendationsRefresh();
    });


function initRecommendationsRefresh() {
    const refreshBtn = document.getElementById('refreshRecommendations');
    const container = document.getElementById('recommendationsContainer');
    const btnText = refreshBtn?.querySelector('.btn-text');
    const btnIcon = refreshBtn?.querySelector('i');
    
    if (!refreshBtn || !container) return;
    
    refreshBtn.addEventListener('click', async function() {
        
        const originalText = btnText?.textContent || 'Обновить рекомендации';
        const originalIcon = btnIcon?.className || 'bi bi-arrow-clockwise me-1';
        
        if (btnText) btnText.textContent = 'Загрузка...';
        if (btnIcon) btnIcon.className = 'bi bi-arrow-clockwise me-1 spinning';
        
        refreshBtn.disabled = true;
        refreshBtn.classList.add('loading');
        
        try {
            
            const response = await fetch('api/recommendations.php', {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                }
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            
            if (data.success) {
                
                container.innerHTML = generateRecommendationsHTML(data.recommendations);
                
                
                if (btnText) btnText.textContent = 'Обновлено!';
                if (btnIcon) btnIcon.className = 'bi bi-check-circle me-1';
                refreshBtn.classList.remove('loading');
                refreshBtn.classList.add('success');
                
                
                setTimeout(() => {
                    if (btnText) btnText.textContent = originalText;
                    if (btnIcon) btnIcon.className = originalIcon;
                    refreshBtn.classList.remove('success');
                }, 2000);
                
            } else {
                throw new Error(data.error || 'Неизвестная ошибка');
            }
            
        } catch (error) {
            console.error('Ошибка обновления рекомендаций:', error);
            
           
            if (btnText) btnText.textContent = 'Ошибка';
            if (btnIcon) btnIcon.className = 'bi bi-exclamation-triangle me-1';
            refreshBtn.classList.remove('loading');
            refreshBtn.classList.add('error');
            
            
            setTimeout(() => {
                if (btnText) btnText.textContent = originalText;
                if (btnIcon) btnIcon.className = originalIcon;
                refreshBtn.classList.remove('error');
            }, 3000);
            
        } finally {
            refreshBtn.disabled = false;
        }
    });
}

function generateRecommendationsHTML(recommendations) {
    return recommendations.map(rec => `
        <div class="recommendation-card mb-3">
            <div class="row g-0">
                <div class="col-4">
                    <div class="recommendation-image">
                        <a href="property.php?id=${rec.id}">
                            <img src="${rec.image_url}" 
                                 alt="${rec.title}" 
                                 class="img-fluid rounded">
                        </a>
                    </div>
                </div>
                <div class="col-8">
                    <div class="recommendation-content p-2">
                        <h6 class="recommendation-title mb-1">
                            <a href="property.php?id=${rec.id}" 
                               class="text-decoration-none text-dark">
                                ${rec.title_short}
                            </a>
                        </h6>
                        <div class="recommendation-meta small text-muted mb-1">
                            ${rec.address}
                        </div>
                        <div class="recommendation-meta small text-muted mb-2">
                            ${rec.area_formatted} м² • м. ${rec.metro}
                        </div>
                        <div class="recommendation-price fw-bold text-primary">
                            ${rec.price_formatted} ₽/мес.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `).join('');
}

})();

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
  
  
  slides.forEach(slide => {
    slide.style.transform = 'translate3d(0,0,0)';
  });
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
  
  
  slides.forEach(slide => {
    slide.style.transform = 'translate3d(0,0,0)';
  });
}



function escapeHtml(s){
  return String(s||'').replace(/[&<>"']/g, c=>({"&":"&amp;","<":"&lt;",">":"&gt;","\"":"&quot;","'":"&#39;"}[c]));
}




