<?php

namespace App\Models;

use App\Models\Traits\UserClientDefaults;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * @property UserDetail $details
 */
class User extends Authenticatable
{
  use HasFactory, Notifiable, UserClientDefaults;

  protected $primaryKey = 'uuid';
  protected $keyType = 'string';
  public $incrementing = false;

  protected $fillable = [
    'name',
    'email',
    'password',
    'role_uuid',
  ];

  protected $hidden = [
    'password',
    'remember_token',
  ];

  protected function casts(): array
  {
    return [
      'email_verified_at' => 'datetime',
      'password' => 'hashed',
    ];
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

  public function permissions()
  {
    return $this->role?->permissions();
  }

  public function details(): HasOne
  {
    return $this->hasOne(UserDetail::class, 'user_uuid', 'uuid');
  }
}
