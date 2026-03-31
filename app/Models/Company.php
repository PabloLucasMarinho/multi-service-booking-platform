<?php

namespace App\Models;

use App\Models\Traits\FormatsAttributes;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
  use FormatsAttributes;

  protected $table = 'company';

  protected $fillable = [
    'name',
    'fantasy_name',
    'document',
    'email',
    'phone',
    'zip_code',
    'address',
    'address_number',
    'address_complement',
    'neighborhood',
    'city',
    'state',
  ];
}
