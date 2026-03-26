<?php

namespace App\Models;

use App\Enums\AppointmentStatus;
use App\Models\Traits\FormatsAttributes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Appointment extends Model
{
  use HasUuids, FormatsAttributes;

  protected $primaryKey = 'uuid';
  protected $keyType = 'string';
  public $incrementing = false;

  protected $fillable = [
    'user_uuid',
    'client_uuid',
    'scheduled_at',
    'notes',
    'status'
  ];

  public function appointmentServices(): HasMany
  {
    return $this->hasMany(AppointmentService::class, 'appointment_uuid', 'uuid');
  }

  public function client(): BelongsTo
  {
    return $this->belongsTo(Client::class, 'client_uuid', 'uuid');
  }

  public function user(): BelongsTo
  {
    return $this->belongsTo(User::class, 'user_uuid', 'uuid');
  }

  protected $casts = [
    'scheduled_at' => 'datetime',
    'status' => AppointmentStatus::class
  ];

  public function getTotalAttribute(): float
  {
    return round($this->appointmentServices->sum(fn($s) => (float)$s->final_price), 2);
  }

  public function getFormattedTotalAttribute(): string
  {
    return number_format($this->total, 2, ',', '.');
  }

  public function getStatusBadgeAttribute(): string
  {
    return match ($this->status) {
      AppointmentStatus::Scheduled => 'badge-primary',
      AppointmentStatus::Completed => 'badge-success',
      AppointmentStatus::Cancelled => 'badge-danger',
      AppointmentStatus::NoShow => 'badge-warning',
    };
  }

  public function getScheduledDateAttribute(): string
  {
    return $this->scheduled_at->format('d/m/Y');
  }

  public function getScheduledTimeAttribute(): string
  {
    return $this->scheduled_at->format('H:i');
  }

  public function getRouteKeyName(): string
  {
    return 'uuid';
  }
}
