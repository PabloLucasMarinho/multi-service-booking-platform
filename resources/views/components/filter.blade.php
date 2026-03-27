<div class="card border mb-3">
  <div class="card-header py-2 px-3 d-flex align-items-center">
    <i class="fas fa-filter fa-sm mr-2 text-muted"></i>
    <small class="text-uppercase text-muted font-weight-bold" style="letter-spacing:.05em;">Filtros</small>
  </div>
  <div class="card-body p-3">
    <div class="row align-items-end no-gutters" style="gap:8px; flex-wrap:nowrap;">
      {{ $slot }}
      <div class="col-auto">
        <button type="button" id="btn-clear-filters" class="btn btn-sm btn-outline-secondary">
          <i class="fas fa-times mr-1"></i> Limpar
        </button>
      </div>
    </div>
  </div>
</div>
