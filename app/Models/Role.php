<?php

namespace App\Models;

use App\Enums\RoleName;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
  use HasFactory;

  protected $primaryKey = 'uuid';
  public $incrementing = false;
  protected $keyType = 'string';

  protected $fillable = ['uuid', 'name'];

  public function users(): HasMany
  {
    return $this->hasMany(User::class, 'role_uuid', 'uuid');
  }

  public function permissions(): BelongsToMany
  {
    return $this->belongsToMany(
      Permission::class,
      'role_permission',
      'role_uuid',
      'permission_uuid',
      'uuid',
      'uuid'
    );
  }

  protected function name(): Attribute
  {
    return Attribute::make(
      get: fn(string $value) => match ($value) {
        'admin' => 'Administrador',
        'employee' => 'Funcionário',
        default => ucfirst($value),
      }
    );
  }
}
