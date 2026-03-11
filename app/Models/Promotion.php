<?php

namespace App\Models;

use App\Enums\DiscountType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Promotion extends Model
{
  use SoftDeletes;

  protected $primaryKey = 'uuid';
  protected $keyType = 'string';
  public $incrementing = false;

  protected $fillable = [
    'name',
    'type',
    'value',
    'starts_at',
    'ends_at',
    'active'
  ];

  public function services(): BelongsToMany
  {
    return $this->belongsToMany(
      Service::class,
      'promotion_service',
      'promotion_uuid',
      'service_uuid'
    );
  }

  protected $casts = [
    'type' => DiscountType::class,
    'value' => 'decimal:2',
    'starts_at' => 'datetime',
    'ends_at' => 'datetime',
    'active' => 'boolean',
  ];

  public function isValid(): bool
  {
    $now = now();

    if (!$this->active) {
      return false;
    }

    if ($this->starts_at && $now->lt($this->starts_at)) {
      return false;
    }

    if ($this->ends_at && $now->gt($this->ends_at)) {
      return false;
    }

    return true;
  }

  public function isActive(): bool
  {
    return $this->active;
  }

  public function isGlobal(): bool
  {
    return !$this->relationLoaded('services')
      ? !$this->services()->exists()
      : $this->services->isEmpty();
  }

  public function scopeActive(Builder $query): Builder
  {
    return $query->where('active', true)
      ->where(function ($q) {
        $q->whereNull('starts_at')
          ->orWhere('starts_at', '<=', now());
      })
      ->where(function ($q) {
        $q->whereNull('ends_at')
          ->orWhere('ends_at', '>=', now());
      });
  }
}
