<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * @mixin Model
 */
trait ModelsDefaults
{
  protected static function bootModelsDefaults(): void
  {
    static::saving(function (Model $model) {
      if ($model->isDirty('name')) {
        $model->initials = static::generateInitials($model->name);
      }
    });

    static::creating(function (Model $model) {
      if (empty($model->uuid)) {
        $model->uuid = (string)Str::uuid();
      }

      if (in_array('color', $model->getFillable()) && empty($model->color)) {
        $model->color = static::generateRandomColor();
      }
    });
  }

  protected static function generateInitials(string $name): string
  {
    $parts = array_values(
      array_filter(
        preg_split('/\s+/', trim($name))
      )
    );

    if (count($parts) === 1) {
      return strtoupper(
        Str::ascii(
          mb_substr($parts[0], 0, 1)
        )
      );
    }

    return strtoupper(
      Str::ascii(
        mb_substr($parts[0], 0, 1) .
        mb_substr(end($parts), 0, 1)
      )
    );
  }

  protected static function generateRandomColor(): string
  {
    $r = str_pad(dechex(mt_rand(50, 200)), 2, '0', STR_PAD_LEFT);
    $g = str_pad(dechex(mt_rand(50, 200)), 2, '0', STR_PAD_LEFT);
    $b = str_pad(dechex(mt_rand(50, 200)), 2, '0', STR_PAD_LEFT);

    return "#$r$g$b";
  }

  protected function contrastColor(): Attribute
  {
    return Attribute::make(
      get: fn() => $this->color
        ? $this->calculateContrastColor($this->color)
        : 'text-body',
    );
  }

  protected function calculateContrastColor(string $hexColor): string
  {
    $hexColor = str_replace('#', '', $hexColor);
    $r = hexdec(substr($hexColor, 0, 2));
    $g = hexdec(substr($hexColor, 2, 2));
    $b = hexdec(substr($hexColor, 4, 2));

    // Fórmula de luminosidade YIQ
    $yiq = (($r * 299) + ($g * 587) + ($b * 114)) / 1000;

    return ($yiq >= 128) ? 'text-body' : 'text-white';
  }
}
