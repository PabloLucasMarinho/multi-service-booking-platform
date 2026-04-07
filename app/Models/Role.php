<?php

namespace App\Models;

use App\Enums\RoleNames;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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

  protected function nameFormatted(): Attribute
  {
    return Attribute::make(
      get: fn() => match ($this->name) {
        RoleNames::Owner->value => 'Proprietário',
        RoleNames::Admin->value => 'Administrador',
        RoleNames::Employee->value => 'Funcionário',
      }
    );
  }
}
