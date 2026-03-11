<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Category extends Model
{
  protected $primaryKey = 'uuid';
  protected $keyType = 'string';
  public $incrementing = false;

  protected $fillable = [
    'name',
    'slug'
  ];

  protected static function booted(): void
  {
    static::creating(function ($category) {
      if (!$category->uuid) {
        $category->uuid = (string)Str::uuid();
      }

      if (!$category->slug && $category->name) {
        $category->slug = Str::slug($category->name);
      }
    });
  }

  public function services(): BelongsToMany
  {
    return $this->belongsToMany(
      Service::class,
      'category_service',
      'category_uuid',
      'service_uuid'
    );
  }

  public function getRouteKeyName(): string
  {
    return 'slug';
  }
}
