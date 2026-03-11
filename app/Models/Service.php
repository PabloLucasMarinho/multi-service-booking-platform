<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
  use SoftDeletes;

  protected $primaryKey = 'uuid';
  protected $keyType = 'string';
  public $incrementing = false;

  protected $fillable = [
    'name',
    'price',
  ];

  public function appointmentServices(): HasMany
  {
    return $this->hasMany(AppointmentService::class, 'service_uuid', 'uuid');
  }

  public function promotions(): BelongsToMany
  {
    return $this->belongsToMany(
      Promotion::class,
      'promotion_service',
      'service_uuid',
      'promotion_uuid'
    );
  }

  public function categories(): BelongsToMany
  {
    return $this->belongsToMany(
      Category::class,
      'category_service',
      'service_uuid',
      'category_uuid'
    );
  }

  protected $casts = [
    'price' => 'decimal:2',
  ];

  public function getFormattedPriceAttribute(): string
  {
    return number_format($this->price, 2, ',', '.');
  }
}
