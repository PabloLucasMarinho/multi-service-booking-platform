<?php

namespace App\Models;

use App\Models\Traits\FormatsAttributes;
use App\Models\Traits\ModelsDefaults;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Client extends Model
{
  use HasUuids, HasFactory, FormatsAttributes, ModelsDefaults;

  protected $primaryKey = 'uuid';
  protected $keyType = 'string';
  public $incrementing = false;

  protected $fillable = ['name', 'document', 'date_of_birth', 'email', 'phone', 'user_uuid'];

  public function creator(): BelongsTo
  {
    return $this->belongsTo(User::class, 'user_uuid', 'uuid');
  }

  public function getRouteKeyName(): string
  {
    return 'uuid';
  }
}
