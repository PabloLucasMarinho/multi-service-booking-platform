<div class="col-md-{{ $colSize ?? 12 }}">
  <label>{{ $label ?? 'Tags' }}</label>
  <div class="input-group mb-2">
    <input type="text" id="{{ $id }}-input" class="form-control" placeholder="{{ $placeholder ?? 'Nova tag...' }}">
    <div class="input-group-append">
      <button type="button" class="btn btn-primary btn-add-tag" data-target="{{ $id }}">
        <i class="fas fa-plus"></i>
      </button>
    </div>
  </div>

  <div id="{{ $id }}-list" class="mt-2"></div>
  <div id="{{ $id }}-hidden"></div>
</div>
