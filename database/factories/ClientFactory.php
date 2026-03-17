<?php

namespace Database\Factories;

use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Client>
 */
class ClientFactory extends Factory
{
  protected $model = Client::class;

  /**
   * Define the model's default state.
   *
   * @return array<string, mixed>
   */
  public function definition(): array
  {
    $name = $this->faker->name();

    $words = explode(' ', $name);
    $initials = mb_strtoupper(mb_substr($words[0], 0, 1) . mb_substr(end($words), 0, 1));

    return [
      'uuid' => str()->uuid(),
      'name' => $name,
      'initials' => $initials,
      'date_of_birth' => $this->faker->date(),
      'document' => generateUniqueCpf(),
      'email' => $this->faker->unique()->safeEmail(),
      'phone' => $this->faker->numerify('219########'),
      'color' => fake()->hexColor(),
    ];
  }
}
