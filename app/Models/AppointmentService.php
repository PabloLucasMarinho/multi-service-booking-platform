<?php

namespace App\Models;

use App\Enums\DiscountType;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AppointmentService extends Model
{
  use HasUuids, SoftDeletes;

  protected $primaryKey = 'uuid';
  protected $keyType = 'string';
  public $incrementing = false;

  protected $fillable = [
    'appointment_uuid',
    'service_uuid',
    'promotion_uuid',
    'original_price',
    'manual_discount_type',
    'manual_discount_value',
    'manual_discount_amount',
    'promotion_amount_snapshot',
    'final_price'
  ];

  public function appointment(): BelongsTo
  {
    return $this->belongsTo(Appointment::class, 'appointment_uuid', 'uuid');
  }

  public function service(): BelongsTo
  {
    return $this->belongsTo(Service::class, 'service_uuid', 'uuid');
  }

  public function promotion(): BelongsTo
  {
    return $this->belongsTo(Promotion::class, 'promotion_uuid', 'uuid');
  }

  protected $casts = [
    'original_price' => 'decimal:2',
    'manual_discount_type' => DiscountType::class,
    'manual_discount_value' => 'decimal:2',
    'manual_discount_amount' => 'decimal:2',
    'promotion_amount_snapshot' => 'decimal:2',
    'final_price' => 'decimal:2',
  ];

  public function applyDiscount(): void
  {
    $original = $this->original_price ?? 0;

    $promotionDiscount = $this->promotion_amount_snapshot ?? 0;

    $afterPromotion = round(max(0, $original - $promotionDiscount), 2);

    $manualAmount = match ($this->manual_discount_type) {
      DiscountType::Percentage => $afterPromotion * (($this->manual_discount_value ?? 0) / 100),

      DiscountType::Fixed => $this->manual_discount_value ?? 0,

      default => 0
    };

    $manualAmount = min($manualAmount, $afterPromotion);
    $manualAmount = round($manualAmount, 2);

    $this->manual_discount_amount = $manualAmount;

    $finalPrice = round($afterPromotion - $manualAmount, 2);

    $this->final_price = max(0, $finalPrice);
  }

  public function getRouteKeyName(): string
  {
    return 'uuid';
  }
}
