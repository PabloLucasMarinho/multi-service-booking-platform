<?php

namespace Database\Seeders;

use App\Enums\DiscountType;
use App\Models\Category;
use App\Models\Promotion;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PromotionSeeder extends Seeder
{
  public function run(): void
  {
    $promotions = [
      [
        'name' => 'Dia do Corte',
        'type' => DiscountType::Percentage,
        'value' => 15,
        'starts_at' => now()->startOfMonth(),
        'ends_at' => now()->endOfMonth(),
        'categories' => ['Corte'],
      ],
      [
        'name' => 'Semana da Barba',
        'type' => DiscountType::Percentage,
        'value' => 20,
        'starts_at' => now()->startOfWeek(),
        'ends_at' => now()->endOfWeek(),
        'categories' => ['Barba'],
      ],
      [
        'name' => 'Combo Especial',
        'type' => DiscountType::Fixed,
        'value' => 10,
        'starts_at' => now(),
        'ends_at' => now()->addDays(15),
        'categories' => ['Combo'],
      ],
      [
        'name' => 'Promoção Coloração',
        'type' => DiscountType::Percentage,
        'value' => 10,
        'starts_at' => now(),
        'ends_at' => now()->addMonth(),
        'categories' => ['Coloração'],
      ],
      [
        'name' => 'Tratamento Capilar em Dobro',
        'type' => DiscountType::Percentage,
        'value' => 25,
        'starts_at' => now(),
        'ends_at' => now()->addDays(7),
        'categories' => ['Tratamento'],
      ],
    ];

    foreach ($promotions as $data) {
      $promotion = Promotion::create([
        'name' => $data['name'],
        'type' => $data['type'],
        'value' => $data['value'],
        'starts_at' => $data['starts_at'],
        'ends_at' => $data['ends_at'],
      ]);

      $categoryUuids = collect($data['categories'])->map(function ($name) {
        return Category::where('slug', Str::slug($name))->value('uuid');
      })->filter();

      $promotion->categories()->attach($categoryUuids);
    }
  }
}
