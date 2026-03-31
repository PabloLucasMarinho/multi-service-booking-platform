<?php

namespace App\Models;

use App\Enums\AppointmentStatus;
use App\Models\Traits\FormatsAttributes;
use App\Models\Traits\ModelsDefaults;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
  use HasUuids, HasFactory, FormatsAttributes, ModelsDefaults, SoftDeletes;

  protected $primaryKey = 'uuid';
  protected $keyType = 'string';
  public $incrementing = false;

  protected $fillable = ['name', 'document', 'date_of_birth', 'email', 'phone', 'user_uuid'];

  protected $casts = [
    'date_of_birth' => 'datetime',
  ];

  protected static function booted(): void
  {
    static::creating(function (Client $client) {
      $client->created_by = auth()->user()?->uuid ?? null;
      $client->updated_by = auth()->user()?->uuid ?? null;
    });

    static::updating(function (Client $client) {
      $client->updated_by = auth()->user()?->uuid ?? null;
    });

    static::deleting(function (Client $client) {
      Appointment::where('client_uuid', $client->uuid)
        ->where('scheduled_at', '>', now())
        ->where('status', AppointmentStatus::Scheduled)
        ->update(['status' => AppointmentStatus::Cancelled]);
    });
  }

  public function creator(): BelongsTo
  {
    return $this->belongsTo(User::class, 'user_uuid', 'uuid');
  }

  public function getRouteKeyName(): string
  {
    return 'uuid';
  }
}
