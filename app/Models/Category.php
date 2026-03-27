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

      if ($category->name) {
        $category->name = self::removeAccents($category->name);
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

  public function promotions(): BelongsToMany
  {
    return $this->belongsToMany(
      Promotion::class,
      'promotion_category',
      'category_uuid',
      'promotion_uuid'
    );
  }

  public function getRouteKeyName(): string
  {
    return 'slug';
  }

  private static function removeAccents(string $value): string
  {
    $search = ['á', 'à', 'ã', 'â', 'ä', 'é', 'è', 'ê', 'ë', 'í', 'ì', 'î', 'ï', 'ó', 'ò', 'õ', 'ô', 'ö', 'ú', 'ù', 'û', 'ü', 'ç', 'ñ', 'Á', 'À', 'Ã', 'Â', 'Ä', 'É', 'È', 'Ê', 'Ë', 'Í', 'Ì', 'Î', 'Ï', 'Ó', 'Ò', 'Õ', 'Ô', 'Ö', 'Ú', 'Ù', 'Û', 'Ü', 'Ç', 'Ñ'];
    $replace = ['a', 'a', 'a', 'a', 'a', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'c', 'n', 'A', 'A', 'A', 'A', 'A', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'C', 'N'];

    return str_replace($search, $replace, $value);
  }
}
