@props(['model'])

@php
  $tz       = config('app.timezone', 'America/Sao_Paulo');
  $tzLabel  = 'UTC' . \Illuminate\Support\Carbon::now($tz)->format('P');

  $createdBy = $model->createdBy;
  $updatedBy = $model->updatedBy;

  $created = $model->created_at
    ? \Illuminate\Support\Carbon::parse($model->created_at)->setTimezone($tz)
    : null;

  $updated = $model->updated_at
    ? \Illuminate\Support\Carbon::parse($model->updated_at)->setTimezone($tz)
    : null;

  $showUpdated = $updated && $created && !$updated->eq($created);
@endphp

<div class="border-top pt-2 pb-3" style="font-size:11px;color:#868e96;line-height:1.8;">
  @if($createdBy && $created)
    <div>
      <i class="fas fa-plus-circle mr-1"></i>
      Criado por
      <strong class="text-dark">{{ $createdBy->name }}</strong>@if($createdBy->role), {{ $createdBy->role->name_formatted }}@endif
      em {{ $created->format('d/m/Y') }} às {{ $created->format('H:i') }} ({{ $tzLabel }})
    </div>
  @endif

  @if($showUpdated && $updatedBy)
    <div>
      <i class="fas fa-pencil-alt mr-1"></i>
      Modificado por
      <strong class="text-dark">{{ $updatedBy->name }}</strong>@if($updatedBy->role), {{ $updatedBy->role->name_formatted }}@endif
      em {{ $updated->format('d/m/Y') }} às {{ $updated->format('H:i') }} ({{ $tzLabel }})
    </div>
  @endif
</div>
