<?php

namespace Database\Factories;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
  /**
   * The current password being used by the factory.
   */
  protected static ?string $password;

  /**
   * Define the model's default state.
   *
   * @return array<string, mixed>
   */
  public function definition(): array
  {
    $name = fake()->name();
    $words = explode(' ', $name);
    $initials = mb_strtoupper(mb_substr($words[0], 0, 1) . mb_substr(end($words), 0, 1));

    return [
      'uuid' => str()->uuid(),
      'role_uuid' => Role::firstOrCreate(['name' => 'employee'], ['uuid' => str()->uuid()])->uuid,
      'name' => $name,
      'initials' => $initials,
      'email' => fake()->unique()->safeEmail(),
      'password' => Hash::make('password'),
      'color' => fake()->hexColor(),
      'document' => generateUniqueCpf(),
      'date_of_birth' => '1990-01-01',
      'phone' => '21999999999',
      'zip_code' => '12345678',
      'address' => 'Rua Teste',
      'neighborhood' => 'Centro',
      'city' => 'Rio de Janeiro',
      'state' => 'RJ',
      'admission_date' => '2025-01-01',
    ];
  }

  /**
   * Indicate that the model's email address should be unverified.
   */
  public function unverified(): static
  {
    return $this->state(fn(array $attributes) => [
      'email_verified_at' => null,
    ]);
  }
}
