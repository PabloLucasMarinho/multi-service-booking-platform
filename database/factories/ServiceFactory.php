<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ServiceFactory extends Factory
{
  public function definition(): array
  {
    return [
      'name' => fake()->words(2, true),
      'price' => fake()->randomFloat(2, 10, 500),
    ];
  }
}
