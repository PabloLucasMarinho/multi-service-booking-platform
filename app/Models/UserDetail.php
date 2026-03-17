<?php

namespace App\Models;

use App\Models\Traits\UserDetailDefaults;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserDetail extends Model
{
  use HasFactory, UserDetailDefaults;

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

  protected $casts = [
    'date_of_birth' => 'date',
    'admission_date' => 'date',
    'salary' => 'decimal:2',
  ];

  protected function salary(): Attribute
  {
    return Attribute::make(
      get: fn($value) => number_format($value, 2, ',', '.'),

      set: fn($value) => (float)str_replace(',', '.', str_replace('.', '', $value))
    );
  }

  public function user(): BelongsTo
  {
    return $this->belongsTo(User::class, 'user_uuid', 'uuid');
  }

  public function getRouteKeyName(): string
  {
    return 'uuid';
  }

  public function getDateOfBirthFormattedAttribute(): string
  {
    return $this->attributes['date_of_birth']
      ? Carbon::parse($this->attributes['date_of_birth'])->format('d/m/Y')
      : '';
  }

  public function getAdmissionDateFormattedAttribute(): string
  {
    return $this->attributes['admission_date']
      ? Carbon::parse($this->attributes['admission_date'])->format('d/m/Y')
      : '';
  }
}
