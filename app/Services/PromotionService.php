<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Promotion;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PromotionService
{
  public function create(array $data): void
  {
    DB::transaction(function () use ($data) {
      $promotion = Promotion::create([
        'name' => $data['name'],
        'type' => $data['type'],
        'value' => $data['value'],
        'starts_at' => $data['starts_at'],
        'ends_at' => $data['ends_at'],
      ]);

      if (!empty($data['categories'])) {
        $categoryUuids = collect($data['categories'])
          ->map(fn($name) => Str::title(trim($name)))
          ->unique()
          ->map(fn($name) => Category::firstOrCreate(
            ['slug' => Str::slug($name)],
            ['name' => $name]
          )->uuid);

        $promotion->categories()->sync($categoryUuids);
      }
    });
  }

  public function update(array $data, Promotion $promotion): void
  {
    DB::transaction(function () use ($data, $promotion) {
      $promotion->update([
        'name' => $data['name'],
        'type' => $data['type'],
        'value' => $data['value'],
        'starts_at' => $data['starts_at'],
        'ends_at' => $data['ends_at'],
      ]);

      if (!empty($data['categories'])) {
        $categoryUuids = collect($data['categories'])
          ->map(fn($name) => Str::title(trim($name)))
          ->unique()
          ->map(fn($name) => Category::firstOrCreate(
            ['slug' => Str::slug($name)],
            ['name' => $name]
          )->uuid);

        $promotion->categories()->sync($categoryUuids);
      } else {
        $promotion->categories()->detach();
      }
    });
  }
}
