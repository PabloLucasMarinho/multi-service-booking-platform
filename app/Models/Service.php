<?php

namespace App\Models;

use App\Models\Traits\FormatsAttributes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
  use HasUuids, HasFactory, SoftDeletes, FormatsAttributes;

  protected $primaryKey = 'uuid';
  protected $keyType = 'string';
  public $incrementing = false;

  protected $fillable = [
    'name',
    'price',
  ];

  protected static function booted(): void
  {
    static::creating(function (Service $service) {
      $service->created_by = auth()->user()?->uuid ?? null;
      $service->updated_by = auth()->user()?->uuid ?? null;
    });

    static::updating(function (Service $service) {
      $service->updated_by = auth()->user()?->uuid ?? null;
    });
  }

  public function appointmentServices(): HasMany
  {
    return $this->hasMany(AppointmentService::class, 'service_uuid', 'uuid');
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

  public function getRouteKeyName(): string
  {
    return 'uuid';
  }
}
