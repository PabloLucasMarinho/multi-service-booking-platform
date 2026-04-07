<?php

namespace Database\Factories;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Appointment>
 */
class AppointmentFactory extends Factory
{
  protected $model = Appointment::class;

  public function definition(): array
  {
    return [
      'uuid' => str()->uuid(),
      'user_uuid' => User::factory(),
      'client_uuid' => Client::factory(),
      'scheduled_at' => $this->faker->dateTimeBetween('-1 month', '+1 month'),
      'notes' => $this->faker->optional()->sentence(),
      'status' => AppointmentStatus::Scheduled,
      'tip' => null,
      'closing_discount' => null,
      'discount_authorized_by' => null,
    ];
  }

  public function scheduled(): static
  {
    return $this->state(fn() => ['status' => AppointmentStatus::Scheduled]);
  }

  public function completed(): static
  {
    return $this->state(fn() => ['status' => AppointmentStatus::Completed]);
  }

  public function cancelled(): static
  {
    return $this->state(fn() => ['status' => AppointmentStatus::Cancelled]);
  }

  public function noShow(): static
  {
    return $this->state(fn() => ['status' => AppointmentStatus::NoShow]);
  }

  public function future(): static
  {
    return $this->state(fn() => ['scheduled_at' => now()->addDays(rand(1, 30))]);
  }

  public function past(): static
  {
    return $this->state(fn() => ['scheduled_at' => now()->subDays(rand(1, 30))]);
  }

  public function today(): static
  {
    return $this->state(fn() => ['scheduled_at' => now()]);
  }
}
