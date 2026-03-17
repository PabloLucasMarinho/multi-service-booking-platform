<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\UserDetail;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserDetail>
 */
class UserDetailFactory extends Factory
{
  /**
   * Define the model's default state.
   *
   * @return array<string, mixed>
   */
  public function definition(): array
  {
    return [
      'uuid' => str()->uuid(),
      'user_uuid' => User::factory(),
      'document' => generateUniqueCpf(),
      'date_of_birth' => fake()->date(),
      'phone' => fake()->phoneNumber(),
      'address' => fake()->streetName(),
      'zip_code' => fake()->postcode(),
      'neighborhood' => 'Bairro Centro',
      'city' => fake()->city(),
      'salary' => fake()->randomFloat(2, 1500, 5000),
      'admission_date' => now(),
    ];
  }
}
