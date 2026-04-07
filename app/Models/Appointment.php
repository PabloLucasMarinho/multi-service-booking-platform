<?php

namespace App\Models;

use App\Enums\AppointmentStatus;
use App\Models\Traits\FormatsAttributes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Appointment extends Model
{
  use HasUuids, HasFactory, FormatsAttributes;

  protected $primaryKey = 'uuid';
  protected $keyType = 'string';
  public $incrementing = false;

  protected $fillable = [
    'user_uuid',
    'client_uuid',
    'scheduled_at',
    'notes',
    'status',
    'tip',
    'closing_discount',
    'discount_authorized_by',
  ];

  protected static function booted(): void
  {
    static::creating(function (Appointment $appointment) {
      $appointment->created_by = auth()->user()?->uuid ?? null;
      $appointment->updated_by = auth()->user()?->uuid ?? null;
    });

    static::updating(function (Appointment $appointment) {
      $appointment->updated_by = auth()->user()?->uuid ?? null;
    });
  }

  public function appointmentServices(): HasMany
  {
    return $this->hasMany(AppointmentService::class, 'appointment_uuid', 'uuid');
  }

  public function client(): BelongsTo
  {
    return $this->belongsTo(Client::class, 'client_uuid', 'uuid')
      ->withoutGlobalScopes();
  }

  public function user(): BelongsTo
  {
    return $this->belongsTo(User::class, 'user_uuid', 'uuid')
      ->withoutGlobalScopes();
  }

  public function payments(): HasMany
  {
    return $this->hasMany(AppointmentPayment::class, 'appointment_uuid', 'uuid');
  }

  public function createdBy(): BelongsTo
  {
    return $this->belongsTo(User::class, 'created_by', 'uuid')->withTrashed();
  }

  public function updatedBy(): BelongsTo
  {
    return $this->belongsTo(User::class, 'updated_by', 'uuid')->withTrashed();
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

  public function getTotalPaidAttribute(): float
  {
    return round($this->payments->sum(fn($p) => (float)$p->amount), 2);
  }

  public function getBalanceAttribute(): float
  {
    return round($this->total_paid - $this->total, 2);
  }

  public function getStatusColorAttribute(): string
  {
    return match ($this->status) {
      AppointmentStatus::Scheduled => 'primary',
      AppointmentStatus::Completed => 'success',
      AppointmentStatus::Cancelled => 'danger',
      AppointmentStatus::NoShow => 'warning',
    };
  }

  // Remova do trait e adicione no model Appointment
  protected function statusFormatted(): Attribute
  {
    return Attribute::make(
      get: fn() => match ($this->status) {
        AppointmentStatus::Scheduled => 'Agendado',
        AppointmentStatus::Completed => 'Concluído',
        AppointmentStatus::Cancelled => 'Cancelado',
        AppointmentStatus::NoShow => 'Não Compareceu',
      }
    );
  }

  protected function scheduledAtFormatted(): Attribute
  {
    return Attribute::make(
      get: fn() => $this->formatDateTime($this->scheduled_at)
    );
  }

  public function getScheduledDateAttribute(): string
  {
    return $this->scheduled_at->format('d/m/Y');
  }

  public function getScheduledTimeAttribute(): string
  {
    return $this->scheduled_at->format('H:i');
  }

  public function isEditable(): bool
  {
    return $this->status === AppointmentStatus::Scheduled;
  }

  public function canRestore(): bool
  {
    return $this->status === AppointmentStatus::Cancelled
      && $this->scheduled_at->isFuture() || $this->scheduled_at->isToday();
  }

  public function getRouteKeyName(): string
  {
    return 'uuid';
  }
}
