<?php

namespace App\Models;

use App\Enums\DiscountType;
use App\Models\Traits\FormatsAttributes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Promotion extends Model
{
  use HasUuids, SoftDeletes, FormatsAttributes;

  protected $primaryKey = 'uuid';
  protected $keyType = 'string';
  public $incrementing = false;

  protected $fillable = [
    'name',
    'type',
    'value',
    'starts_at',
    'ends_at',
  ];

  public function categories(): BelongsToMany
  {
    return $this->belongsToMany(
      Category::class,
      'promotion_category',
      'promotion_uuid',
      'category_uuid'
    );
  }

  protected $casts = [
    'type' => DiscountType::class,
    'value' => 'decimal:2',
    'starts_at' => 'datetime',
    'ends_at' => 'datetime',
  ];

  public function getRouteKeyName()
  {
    return 'uuid';
  }

  public function getActiveAttribute(): bool
  {
    $now = now();

    return (!$this->starts_at || $this->starts_at <= $now)
      && (!$this->ends_at || $this->ends_at >= $now);
  }

  public function isGlobal(): bool
  {
    return !$this->relationLoaded('categories')
      ? !$this->categories()->exists()
      : $this->categories->isEmpty();
  }

  public function scopeActive(Builder $query): Builder
  {
    $now = now();

    return $query
      ->where(function ($q) use ($now) {
        $q->whereNull('starts_at')
          ->orWhere('starts_at', '<=', $now);
      })
      ->where(function ($q) use ($now) {
        $q->whereNull('ends_at')
          ->orWhere('ends_at', '>=', $now);
      });
  }

  public function applyDiscount(float $price): float
  {
    if (!$this->active) {
      return $price;
    }

    return match ($this->type) {
      DiscountType::Fixed => max(0, $price - $this->value),
      DiscountType::Percentage => $price * (1 - ($this->value / 100)),
    };
  }
}
