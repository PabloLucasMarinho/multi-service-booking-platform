<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
  use HasFactory;

  protected $primaryKey = 'uuid';
  public $incrementing = false;
  protected $keyType = 'string';
  protected $fillable = ['uuid', 'name'];

  public function roles(): BelongsToMany
  {
    return $this->belongsToMany(
      Role::class,
      'role_permission',
      'permission_uuid',
      'role_uuid',
      'uuid',
      'uuid'
    );
  }
}
