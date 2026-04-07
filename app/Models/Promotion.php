<?php

namespace App\Models;

use App\Enums\AppointmentStatus;
use App\Enums\DiscountType;
use App\Models\Traits\FormatsAttributes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

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

  protected static function booted(): void
  {
    static::creating(function (Promotion $promotion) {
      $promotion->created_by = auth()->user()?->uuid ?? null;
      $promotion->updated_by = auth()->user()?->uuid ?? null;
    });

    static::updating(function (Promotion $promotion) {
      $promotion->updated_by = auth()->user()?->uuid ?? null;
    });

    static::deleting(function (Promotion $promotion) {
      $maxDiscount = Company::first()?->max_discount_percentage;

      AppointmentService::where('promotion_uuid', $promotion->uuid)
        ->whereHas('appointment', fn($q) => $q->where('status', AppointmentStatus::Scheduled))
        ->get()
        ->each(function (AppointmentService $item) use ($maxDiscount) {
          $item->promotion_uuid              = null;
          $item->promotion_amount_snapshot   = null;
          $item->applyDiscount($maxDiscount);
          $item->save();
        });
    });
  }

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
    return $this->scopeActiveAt($query, now());
  }

  public function scopeActiveAt(Builder $query, Carbon $date): Builder
  {
    return $query
      ->where(function ($q) use ($date) {
        $q->whereNull('starts_at')
          ->orWhere('starts_at', '<=', $date);
      })
      ->where(function ($q) use ($date) {
        $q->whereNull('ends_at')
          ->orWhere('ends_at', '>=', $date);
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

  protected function typeFormatted(): Attribute
  {
    return Attribute::make(get: fn() => $this->type === DiscountType::Fixed ? 'Fixo' : 'Porcentagem');
  }

  protected function activeFormatted(): Attribute
  {
    return Attribute::make(get: fn() => $this->active === true ? 'Ativo' : 'Inativo');
  }

  protected function valueFormatted(): Attribute
  {
    return Attribute::make(
      get: function () {
        if (!$this->value) return null;
        return $this->type === DiscountType::Percentage
          ? (float)$this->value . '%'
          : 'R$' . number_format((float)$this->value, 2, ',', '.');
      }
    );
  }
}
