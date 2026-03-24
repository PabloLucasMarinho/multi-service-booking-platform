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
    'active'
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
    return !$this->relationLoaded('categories')
      ? !$this->categories()->exists()
      : $this->categories->isEmpty();
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
