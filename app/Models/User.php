<?php

namespace App\Models;

use App\Enums\AppointmentStatus;
use App\Models\Traits\FormatsAttributes;
use App\Models\Traits\ModelsDefaults;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
  use HasFactory, Notifiable, ModelsDefaults, FormatsAttributes, SoftDeletes;

  protected $primaryKey = 'uuid';
  protected $keyType = 'string';
  public $incrementing = false;

  protected $fillable = [
    'name',
    'email',
    'password',
    'color',
    'role_uuid',
    'document',
    'date_of_birth',
    'phone',
    'zip_code',
    'address',
    'address_number',
    'address_complement',
    'neighborhood',
    'city',
    'state',
    'salary',
    'admission_date',
  ];

  protected $casts = [
    'password' => 'hashed',
    'email_verified_at' => 'datetime',
    'date_of_birth' => 'datetime',
    'admission_date' => 'datetime',
  ];

  protected $hidden = [
    'password',
    'remember_token',
  ];

  protected static function booted(): void
  {
    static::creating(function (User $user) {
      $user->created_by = auth()->user()?->uuid ?? null;
      $user->updated_by = auth()->user()?->uuid ?? null;
    });

    static::updating(function (User $user) {
      $user->updated_by = auth()->user()?->uuid ?? null;
    });

    static::deleting(function (User $user) {
      Appointment::where('user_uuid', $user->uuid)
        ->where('scheduled_at', '>', now())
        ->where('status', AppointmentStatus::Scheduled)
        ->update(['status' => AppointmentStatus::Cancelled]);
    });
  }

  public function getAuthIdentifierName(): string
  {
    return 'uuid';
  }

  public function getRouteKeyName(): string
  {
    return 'uuid';
  }

  public function role(): BelongsTo
  {
    return $this->belongsTo(Role::class, 'role_uuid', 'uuid');
  }

  public function adminlte_desc(): string
  {
    return $this->role->name_formatted ?? '';
  }

  public function adminlte_profile_url(): string
  {
    return route('users.show', $this);
  }
}
