<?php

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\AppointmentService;
use App\Models\Client;
use App\Models\Role;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function makeAppointmentServiceUser(string $role): User
{
  $roleModel = Role::firstOrCreate(
    ['name' => $role],
    ['uuid' => str()->uuid()]
  );

  return User::factory()->create([
    'role_uuid' => $roleModel->uuid,
    'date_of_birth' => '1990-01-01',
    'phone' => '21999999999',
    'zip_code' => '12345678',
    'address' => 'Rua Teste',
    'neighborhood' => 'Centro',
    'city' => 'Rio de Janeiro',
    'state' => 'RJ',
    'admission_date' => '2025-01-01',
  ]);
}

function makeScheduledAppointment(User $user): Appointment
{
  $client = Client::factory()->create(['user_uuid' => $user->uuid]);

  return Appointment::factory()->create([
    'user_uuid' => $user->uuid,
    'client_uuid' => $client->uuid,
    'scheduled_at' => now()->addDay(),
    'status' => AppointmentStatus::Scheduled,
  ]);
}

function makeAppointmentService(Appointment $appointment, Service $service): AppointmentService
{
  $item = new AppointmentService([
    'appointment_uuid' => $appointment->uuid,
    'service_uuid' => $service->uuid,
    'original_price' => $service->price,
    'manual_discount_type' => null,
    'manual_discount_value' => null,
    'promotion_amount_snapshot' => null,
  ]);

  $item->applyDiscount();
  $item->save();

  return $item;
}

// ============================================================
// STORE
// ============================================================

describe('store', function () {
  it('owner pode adicionar serviço ao agendamento', function () {
    $owner = makeAppointmentServiceUser('owner');
    $appointment = makeScheduledAppointment($owner);
    $service = Service::factory()->create();

    $this->actingAs($owner)
      ->post(route('appointment-services.store', $appointment), [
        'service_uuid' => (string)$service->uuid,
      ])
      ->assertRedirect(route('appointments.show', $appointment));

    $this->assertDatabaseHas('appointment_services', [
      'appointment_uuid' => $appointment->uuid,
      'service_uuid' => $service->uuid,
    ]);
  });

  it('admin pode adicionar serviço ao agendamento', function () {
    $admin = makeAppointmentServiceUser('admin');
    $appointment = makeScheduledAppointment($admin);
    $service = Service::factory()->create();

    $this->actingAs($admin)
      ->post(route('appointment-services.store', $appointment), [
        'service_uuid' => (string)$service->uuid,
      ])
      ->assertRedirect(route('appointments.show', $appointment));

    $this->assertDatabaseHas('appointment_services', [
      'appointment_uuid' => $appointment->uuid,
      'service_uuid' => $service->uuid,
    ]);
  });

  it('employee pode adicionar serviço ao próprio agendamento', function () {
    $employee = makeAppointmentServiceUser('employee');
    $appointment = makeScheduledAppointment($employee);
    $service = Service::factory()->create();

    $this->actingAs($employee)
      ->post(route('appointment-services.store', $appointment), [
        'service_uuid' => (string)$service->uuid,
      ])
      ->assertRedirect(route('appointments.show', $appointment));

    $this->assertDatabaseHas('appointment_services', [
      'appointment_uuid' => $appointment->uuid,
      'service_uuid' => $service->uuid,
    ]);
  });

  it('employee não pode adicionar serviço ao agendamento de outro', function () {
    $employee = makeAppointmentServiceUser('employee');
    $other = makeAppointmentServiceUser('employee');
    $appointment = makeScheduledAppointment($other);
    $service = Service::factory()->create();

    $this->actingAs($employee)
      ->post(route('appointment-services.store', $appointment), [
        'service_uuid' => (string)$service->uuid,
      ])
      ->assertForbidden();
  });

  it('serviço é obrigatório', function () {
    $admin = makeAppointmentServiceUser('admin');
    $appointment = makeScheduledAppointment($admin);

    $this->actingAs($admin)
      ->post(route('appointment-services.store', $appointment), [
        'service_uuid' => '',
      ])
      ->assertSessionHasErrors('service_uuid');
  });

  it('serviço inexistente é rejeitado', function () {
    $admin = makeAppointmentServiceUser('admin');
    $appointment = makeScheduledAppointment($admin);

    $this->actingAs($admin)
      ->post(route('appointment-services.store', $appointment), [
        'service_uuid' => str()->uuid(),
      ])
      ->assertSessionHasErrors('service_uuid');
  });

  it('tipo de desconto inválido é rejeitado', function () {
    $admin = makeAppointmentServiceUser('admin');
    $appointment = makeScheduledAppointment($admin);
    $service = Service::factory()->create();

    $this->actingAs($admin)
      ->post(route('appointment-services.store', $appointment), [
        'service_uuid' => (string)$service->uuid,
        'manual_discount_type' => 'invalido',
      ])
      ->assertSessionHasErrors('manual_discount_type');
  });

  it('valor de desconto é obrigatório quando tipo informado', function () {
    $admin = makeAppointmentServiceUser('admin');
    $appointment = makeScheduledAppointment($admin);
    $service = Service::factory()->create();

    $this->actingAs($admin)
      ->post(route('appointment-services.store', $appointment), [
        'service_uuid' => (string)$service->uuid,
        'manual_discount_type' => 'percentage',
        'manual_discount_value' => '',
      ])
      ->assertSessionHasErrors('manual_discount_value');
  });

  it('adiciona serviço com desconto percentual', function () {
    $admin = makeAppointmentServiceUser('admin');
    $appointment = makeScheduledAppointment($admin);
    $service = Service::factory()->create(['price' => 100]);

    $this->actingAs($admin)
      ->post(route('appointment-services.store', $appointment), [
        'service_uuid' => (string)$service->uuid,
        'manual_discount_type' => 'percentage',
        'manual_discount_value' => '10,00',
      ])
      ->assertRedirect(route('appointments.show', $appointment));

    $item = AppointmentService::where('appointment_uuid', $appointment->uuid)->first();
    expect((float)$item->final_price)->toBe(90.0);
  });

  it('adiciona serviço com desconto fixo', function () {
    $admin = makeAppointmentServiceUser('admin');
    $appointment = makeScheduledAppointment($admin);
    $service = Service::factory()->create(['price' => 100]);

    $this->actingAs($admin)
      ->post(route('appointment-services.store', $appointment), [
        'service_uuid' => (string)$service->uuid,
        'manual_discount_type' => 'fixed',
        'manual_discount_value' => '15,00',
      ])
      ->assertRedirect(route('appointments.show', $appointment));

    $item = AppointmentService::where('appointment_uuid', $appointment->uuid)->first();
    expect((float)$item->final_price)->toBe(85.0);
  });
});

// ============================================================
// DESTROY
// ============================================================

describe('destroy', function () {
  it('owner pode remover serviço do agendamento', function () {
    $owner = makeAppointmentServiceUser('owner');
    $appointment = makeScheduledAppointment($owner);
    $service = Service::factory()->create();
    $item = makeAppointmentService($appointment, $service);

    $this->actingAs($owner)
      ->delete(route('appointment-services.destroy', $item))
      ->assertRedirect(route('appointments.show', $appointment));

    $this->assertSoftDeleted('appointment_services', ['uuid' => $item->uuid]);
  });

  it('admin pode remover serviço do agendamento', function () {
    $admin = makeAppointmentServiceUser('admin');
    $appointment = makeScheduledAppointment($admin);
    $service = Service::factory()->create();
    $item = makeAppointmentService($appointment, $service);

    $this->actingAs($admin)
      ->delete(route('appointment-services.destroy', $item))
      ->assertRedirect(route('appointments.show', $appointment));

    $this->assertSoftDeleted('appointment_services', ['uuid' => $item->uuid]);
  });

  it('employee pode remover serviço do próprio agendamento', function () {
    $employee = makeAppointmentServiceUser('employee');
    $appointment = makeScheduledAppointment($employee);
    $service = Service::factory()->create();
    $item = makeAppointmentService($appointment, $service);

    $this->actingAs($employee)
      ->delete(route('appointment-services.destroy', $item))
      ->assertRedirect(route('appointments.show', $appointment));

    $this->assertSoftDeleted('appointment_services', ['uuid' => $item->uuid]);
  });

  it('employee não pode remover serviço do agendamento de outro', function () {
    $employee = makeAppointmentServiceUser('employee');
    $other = makeAppointmentServiceUser('employee');
    $appointment = makeScheduledAppointment($other);
    $service = Service::factory()->create();
    $item = makeAppointmentService($appointment, $service);

    $this->actingAs($employee)
      ->delete(route('appointment-services.destroy', $item))
      ->assertForbidden();
  });
});
