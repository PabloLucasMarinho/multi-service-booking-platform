<?php

namespace App\Models;

use App\Models\Traits\FormatsAttributes;
use App\Models\Traits\ModelsDefaults;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserDetail extends Model
{
  use HasFactory, ModelsDefaults, FormatsAttributes;

  protected $table = 'user_details';
  protected $primaryKey = 'uuid';
  public $incrementing = false;
  protected $keyType = 'string';

  protected $fillable = [
    'user_uuid',
    'document',
    'date_of_birth',
    'phone',
    'address',
    'address_complement',
    'zip_code',
    'neighborhood',
    'city',
    'salary',
    'admission_date',
  ];

  public function user(): BelongsTo
  {
    return $this->belongsTo(User::class, 'user_uuid', 'uuid');
  }

  public function getRouteKeyName(): string
  {
    return 'uuid';
  }
}
