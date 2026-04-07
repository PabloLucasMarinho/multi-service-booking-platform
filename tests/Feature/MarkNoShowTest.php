<?php

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ============================================================
// HELPERS
// ============================================================

function makeNoShowUser(): User
{
  $role = Role::firstOrCreate(
    ['name' => 'owner'],
    ['uuid' => str()->uuid()]
  );

  return User::factory()->create([
    'role_uuid'     => $role->uuid,
    'date_of_birth' => '1990-01-01',
  ]);
}

function makeNoShowAppointment(array $overrides = []): Appointment
{
  return Appointment::factory()->create(array_merge([
    'user_uuid'   => makeNoShowUser()->uuid,
    'client_uuid' => Client::factory()->create()->uuid,
  ], $overrides));
}

// ============================================================
// TESTES
// ============================================================

describe('appointments:mark-no-show', function () {
  it('marca como no-show agendamentos passados com status scheduled', function () {
    $appointment = makeNoShowAppointment([
      'scheduled_at' => now()->subDay(),
      'status'       => AppointmentStatus::Scheduled,
    ]);

    $this->artisan('appointments:mark-no-show')->assertSuccessful();

    expect($appointment->fresh()->status)->toBe(AppointmentStatus::NoShow);
  });

  it('não altera agendamentos scheduled de hoje', function () {
    $appointment = makeNoShowAppointment([
      'scheduled_at' => now(),
      'status'       => AppointmentStatus::Scheduled,
    ]);

    $this->artisan('appointments:mark-no-show')->assertSuccessful();

    expect($appointment->fresh()->status)->toBe(AppointmentStatus::Scheduled);
  });

  it('não altera agendamentos scheduled futuros', function () {
    $appointment = makeNoShowAppointment([
      'scheduled_at' => now()->addDay(),
      'status'       => AppointmentStatus::Scheduled,
    ]);

    $this->artisan('appointments:mark-no-show')->assertSuccessful();

    expect($appointment->fresh()->status)->toBe(AppointmentStatus::Scheduled);
  });

  it('não altera agendamentos passados já concluídos', function () {
    $appointment = makeNoShowAppointment([
      'scheduled_at' => now()->subDay(),
      'status'       => AppointmentStatus::Completed,
    ]);

    $this->artisan('appointments:mark-no-show')->assertSuccessful();

    expect($appointment->fresh()->status)->toBe(AppointmentStatus::Completed);
  });

  it('não altera agendamentos passados já cancelados', function () {
    $appointment = makeNoShowAppointment([
      'scheduled_at' => now()->subDay(),
      'status'       => AppointmentStatus::Cancelled,
    ]);

    $this->artisan('appointments:mark-no-show')->assertSuccessful();

    expect($appointment->fresh()->status)->toBe(AppointmentStatus::Cancelled);
  });

  it('não altera agendamentos passados já marcados como no-show', function () {
    $appointment = makeNoShowAppointment([
      'scheduled_at' => now()->subDay(),
      'status'       => AppointmentStatus::NoShow,
    ]);

    $this->artisan('appointments:mark-no-show')->assertSuccessful();

    expect($appointment->fresh()->status)->toBe(AppointmentStatus::NoShow);
  });

  it('exibe a quantidade correta de agendamentos atualizados', function () {
    makeNoShowAppointment(['scheduled_at' => now()->subDays(3), 'status' => AppointmentStatus::Scheduled]);
    makeNoShowAppointment(['scheduled_at' => now()->subDays(1), 'status' => AppointmentStatus::Scheduled]);
    makeNoShowAppointment(['scheduled_at' => now()->addDay(),   'status' => AppointmentStatus::Scheduled]);

    $this->artisan('appointments:mark-no-show')
      ->expectsOutput('2 agendamento(s) marcado(s) como no-show.')
      ->assertSuccessful();
  });
});
