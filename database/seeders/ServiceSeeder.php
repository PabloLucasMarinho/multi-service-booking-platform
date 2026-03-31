<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Service;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ServiceSeeder extends Seeder
{
  public function run(): void
  {
    $services = [
      ['name' => 'Corte Navalhado', 'price' => 45.00, 'categories' => ['Corte', 'Navalha']],
      ['name' => 'Corte Tesoura', 'price' => 35.00, 'categories' => ['Corte', 'Tesoura']],
      ['name' => 'Corte Máquina', 'price' => 25.00, 'categories' => ['Corte', 'Máquina']],
      ['name' => 'Corte Degradê', 'price' => 40.00, 'categories' => ['Corte', 'Degradê']],
      ['name' => 'Corte Infantil', 'price' => 30.00, 'categories' => ['Corte', 'Infantil']],
      ['name' => 'Barba Completa', 'price' => 35.00, 'categories' => ['Barba', 'Navalha']],
      ['name' => 'Aparar Barba', 'price' => 20.00, 'categories' => ['Barba']],
      ['name' => 'Barba Modelada', 'price' => 40.00, 'categories' => ['Barba', 'Modelagem']],
      ['name' => 'Corte + Barba', 'price' => 65.00, 'categories' => ['Corte', 'Barba', 'Combo']],
      ['name' => 'Corte + Barba + Sobrancelha', 'price' => 75.00, 'categories' => ['Corte', 'Barba', 'Sobrancelha', 'Combo']],
      ['name' => 'Sobrancelha Navalha', 'price' => 15.00, 'categories' => ['Sobrancelha', 'Navalha']],
      ['name' => 'Sobrancelha Pinça', 'price' => 10.00, 'categories' => ['Sobrancelha']],
      ['name' => 'Coloração Cabelo', 'price' => 80.00, 'categories' => ['Coloração', 'Cabelo']],
      ['name' => 'Coloração Barba', 'price' => 50.00, 'categories' => ['Coloração', 'Barba']],
      ['name' => 'Luzes', 'price' => 120.00, 'categories' => ['Coloração', 'Cabelo']],
      ['name' => 'Hidratação Capilar', 'price' => 60.00, 'categories' => ['Tratamento', 'Cabelo']],
      ['name' => 'Relaxamento', 'price' => 90.00, 'categories' => ['Tratamento', 'Cabelo']],
      ['name' => 'Progressiva', 'price' => 150.00, 'categories' => ['Tratamento', 'Cabelo']],
      ['name' => 'Platinado', 'price' => 200.00, 'categories' => ['Coloração', 'Cabelo']],
      ['name' => 'Pigmentação de Barba', 'price' => 70.00, 'categories' => ['Barba', 'Coloração']],
    ];

    foreach ($services as $data) {
      $service = Service::create([
        'name' => $data['name'],
        'price' => $data['price'],
      ]);

      $categoryUuids = collect($data['categories'])->map(function ($name) {
        return Category::firstOrCreate(
          ['slug' => Str::slug($name)],
          ['name' => $name]
        )->uuid;
      });

      $service->categories()->attach($categoryUuids);
    }
  }
}
