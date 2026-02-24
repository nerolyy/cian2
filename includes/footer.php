<footer class="site-footer border-top bg-white">
    <div class="container py-4 d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
        <div class="text-muted small">Кристина Коворк</div>
        <div class="d-flex gap-3 small">
            <a href="#" class="text-muted text-decoration-none">О компании</a>
            <a href="#" class="text-muted text-decoration-none">Помощь</a>
            <a href="#" class="text-muted text-decoration-none">Контакты</a>
        </div>
    </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?php echo htmlspecialchars($appRoot); ?>/assets/js/app.js"></script>
<script>document.addEventListener('DOMContentLoaded', function(){ if(window.initMapAndLoadMarkers){ window.initMapAndLoadMarkers('<?php echo htmlspecialchars($appRoot); ?>'); } });</script>
<div class="modal fade" id="chatModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Сообщение по объекту</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="mb-2 small text-muted" id="chatModalSub"></div>
        <div class="border rounded p-2 mb-2" style="height:200px; overflow:auto; background:#f8f9fa;">
          <div class="text-muted">Чат с агентом. Начните диалог ниже…</div>
          <div id="chatMessages"></div>
        </div>
        <form id="chatForm" class="d-flex gap-2">
          <input class="form-control" type="text" id="chatInput" placeholder="Ваше сообщение" required>
          <button class="btn btn-primary" type="submit">Отправить</button>
        </form>
      </div>
    </div>
  </div>
  
</div>
</body>
</html>


