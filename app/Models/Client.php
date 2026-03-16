<?php

namespace App\Models;

use App\Models\Traits\FormatsAttributes;
use App\Models\Traits\UserClientDefaults;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Client extends Model
{
  use HasUuids, FormatsAttributes, UserClientDefaults;

  protected $primaryKey = 'uuid';
  protected $keyType = 'string';
  public $incrementing = false;

  protected $fillable = ['name', 'document', 'date_of_birth', 'email', 'phone', 'user_uuid'];

  protected $casts = ['date_of_birth' => 'date'];

  public function creator(): BelongsTo
  {
    return $this->belongsTo(User::class, 'user_uuid', 'uuid');
  }

  public function getRouteKeyName(): string
  {
    return 'uuid';
  }
}
