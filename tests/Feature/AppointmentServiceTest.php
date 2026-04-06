<?php

use App\Enums\AppointmentStatus;
use App\Enums\DiscountType;
use App\Models\Appointment;
use App\Models\AppointmentService;
use App\Models\Client;
use App\Models\Company;
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
    $admin->update(['can_apply_manual_discount' => true]);
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
    $admin->update(['can_apply_manual_discount' => true]);
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

// ============================================================
// APPLY DISCOUNT — TETO
// ============================================================

describe('applyDiscount com teto', function () {
  it('sem teto aplica desconto percentual completo', function () {
    $item = new AppointmentService([
      'original_price'            => 100,
      'manual_discount_type'      => DiscountType::Percentage,
      'manual_discount_value'     => 40,
      'promotion_amount_snapshot' => null,
    ]);
    $item->applyDiscount(null);

    expect((float)$item->final_price)->toBe(60.0);
    expect((float)$item->manual_discount_amount)->toBe(40.0);
  });

  it('desconto manual é limitado quando soma com promoção excede o teto', function () {
    // teto 30% de 100 = R$30; promoção usou R$10; sobra R$20 para manual
    // pediu 40% de 90 (após promo) = R$36, mas limita a R$20
    $item = new AppointmentService([
      'original_price'            => 100,
      'manual_discount_type'      => DiscountType::Percentage,
      'manual_discount_value'     => 40,
      'promotion_amount_snapshot' => 10,
    ]);
    $item->applyDiscount(30);

    expect((float)$item->manual_discount_amount)->toBe(20.0);
    expect((float)$item->final_price)->toBe(70.0);
  });

  it('desconto manual não é alterado quando está dentro do teto', function () {
    // teto 30% de 100 = R$30; pediu 10% = R$10, dentro do limite
    $item = new AppointmentService([
      'original_price'            => 100,
      'manual_discount_type'      => DiscountType::Percentage,
      'manual_discount_value'     => 10,
      'promotion_amount_snapshot' => null,
    ]);
    $item->applyDiscount(30);

    expect((float)$item->manual_discount_amount)->toBe(10.0);
    expect((float)$item->final_price)->toBe(90.0);
  });

  it('promoção que consome todo o teto zera o desconto manual', function () {
    // teto 30% de 100 = R$30; promoção já usou R$30; sobra R$0 para manual
    $item = new AppointmentService([
      'original_price'            => 100,
      'manual_discount_type'      => DiscountType::Percentage,
      'manual_discount_value'     => 20,
      'promotion_amount_snapshot' => 30,
    ]);
    $item->applyDiscount(30);

    expect((float)$item->manual_discount_amount)->toBe(0.0);
    expect((float)$item->final_price)->toBe(70.0);
  });

  it('desconto fixo é limitado pelo teto', function () {
    // teto 30% de 100 = R$30; pediu R$50 fixo, limita a R$30
    $item = new AppointmentService([
      'original_price'            => 100,
      'manual_discount_type'      => DiscountType::Fixed,
      'manual_discount_value'     => 50,
      'promotion_amount_snapshot' => null,
    ]);
    $item->applyDiscount(30);

    expect((float)$item->manual_discount_amount)->toBe(30.0);
    expect((float)$item->final_price)->toBe(70.0);
  });

  it('teto de 100% não limita nenhum desconto', function () {
    $item = new AppointmentService([
      'original_price'            => 100,
      'manual_discount_type'      => DiscountType::Percentage,
      'manual_discount_value'     => 100,
      'promotion_amount_snapshot' => null,
    ]);
    $item->applyDiscount(100);

    expect((float)$item->final_price)->toBe(0.0);
  });

  it('teto é aplicado via HTTP ao adicionar serviço com owner', function () {
    Company::create(['name' => 'Empresa Teste', 'max_discount_percentage' => 30]);

    $owner = makeAppointmentServiceUser('owner');
    $appointment = makeScheduledAppointment($owner);
    $service = Service::factory()->create(['price' => 100]);

    $this->actingAs($owner)
      ->post(route('appointment-services.store', $appointment), [
        'service_uuid'           => (string)$service->uuid,
        'manual_discount_type'   => 'percentage',
        'manual_discount_value'  => '50,00',
      ]);

    $item = AppointmentService::where('appointment_uuid', $appointment->uuid)->first();
    expect((float)$item->manual_discount_amount)->toBe(30.0);
    expect((float)$item->final_price)->toBe(70.0);
  });
});

// ============================================================
// IS DISCOUNT CAPPED
// ============================================================

describe('isDiscountCapped', function () {
  it('retorna false quando não há teto configurado', function () {
    $item = new AppointmentService([
      'original_price'            => 100,
      'manual_discount_type'      => DiscountType::Percentage,
      'manual_discount_value'     => 40,
      'manual_discount_amount'    => 40,
      'promotion_amount_snapshot' => null,
    ]);

    expect($item->isDiscountCapped(null))->toBeFalse();
  });

  it('retorna false quando o desconto está dentro do teto', function () {
    $item = new AppointmentService([
      'original_price'            => 100,
      'manual_discount_type'      => DiscountType::Percentage,
      'manual_discount_value'     => 10,
      'manual_discount_amount'    => 10,
      'promotion_amount_snapshot' => null,
    ]);

    expect($item->isDiscountCapped(30))->toBeFalse();
  });

  it('retorna true quando o desconto foi limitado pelo teto', function () {
    // pediu 40%, mas com promo de R$10 e teto 30%, ficou limitado a R$20
    $item = new AppointmentService([
      'original_price'            => 100,
      'manual_discount_type'      => DiscountType::Percentage,
      'manual_discount_value'     => 40,
      'manual_discount_amount'    => 20,
      'promotion_amount_snapshot' => 10,
    ]);

    expect($item->isDiscountCapped(30))->toBeTrue();
  });

  it('retorna false quando não há desconto manual', function () {
    $item = new AppointmentService([
      'original_price'            => 100,
      'manual_discount_type'      => null,
      'manual_discount_value'     => null,
      'manual_discount_amount'    => null,
      'promotion_amount_snapshot' => null,
    ]);

    expect($item->isDiscountCapped(30))->toBeFalse();
  });

  it('retorna true para desconto fixo limitado pelo teto', function () {
    // pediu R$50 fixo, teto 30% = R$30, ficou limitado a R$30
    $item = new AppointmentService([
      'original_price'            => 100,
      'manual_discount_type'      => DiscountType::Fixed,
      'manual_discount_value'     => 50,
      'manual_discount_amount'    => 30,
      'promotion_amount_snapshot' => null,
    ]);

    expect($item->isDiscountCapped(30))->toBeTrue();
  });
});

// ============================================================
// AUTORIZAÇÃO DE DESCONTO MANUAL
// ============================================================

describe('autorização de desconto manual', function () {
  it('employee sem permissão tem desconto ignorado', function () {
    $employee = makeAppointmentServiceUser('employee');
    // can_apply_manual_discount é false por padrão
    $appointment = makeScheduledAppointment($employee);
    $service = Service::factory()->create(['price' => 100]);

    $this->actingAs($employee)
      ->post(route('appointment-services.store', $appointment), [
        'service_uuid'          => (string)$service->uuid,
        'manual_discount_type'  => 'percentage',
        'manual_discount_value' => '10,00',
      ])
      ->assertRedirect(route('appointments.show', $appointment));

    $item = AppointmentService::where('appointment_uuid', $appointment->uuid)->first();
    expect((float)$item->final_price)->toBe(100.0);
    expect($item->manual_discount_type)->toBeNull();
  });

  it('employee com permissão consegue aplicar desconto', function () {
    $employee = makeAppointmentServiceUser('employee');
    $employee->update(['can_apply_manual_discount' => true]);
    $appointment = makeScheduledAppointment($employee);
    $service = Service::factory()->create(['price' => 100]);

    $this->actingAs($employee)
      ->post(route('appointment-services.store', $appointment), [
        'service_uuid'          => (string)$service->uuid,
        'manual_discount_type'  => 'percentage',
        'manual_discount_value' => '10,00',
      ])
      ->assertRedirect(route('appointments.show', $appointment));

    $item = AppointmentService::where('appointment_uuid', $appointment->uuid)->first();
    expect((float)$item->final_price)->toBe(90.0);
  });

  it('owner sempre pode aplicar desconto independente da flag', function () {
    $owner = makeAppointmentServiceUser('owner');
    $appointment = makeScheduledAppointment($owner);
    $service = Service::factory()->create(['price' => 100]);

    $this->actingAs($owner)
      ->post(route('appointment-services.store', $appointment), [
        'service_uuid'          => (string)$service->uuid,
        'manual_discount_type'  => 'percentage',
        'manual_discount_value' => '10,00',
      ])
      ->assertRedirect(route('appointments.show', $appointment));

    $item = AppointmentService::where('appointment_uuid', $appointment->uuid)->first();
    expect((float)$item->final_price)->toBe(90.0);
  });

  it('admin sem permissão tem desconto ignorado', function () {
    $admin = makeAppointmentServiceUser('admin');
    $appointment = makeScheduledAppointment($admin);
    $service = Service::factory()->create(['price' => 100]);

    $this->actingAs($admin)
      ->post(route('appointment-services.store', $appointment), [
        'service_uuid'          => (string)$service->uuid,
        'manual_discount_type'  => 'fixed',
        'manual_discount_value' => '20,00',
      ])
      ->assertRedirect(route('appointments.show', $appointment));

    $item = AppointmentService::where('appointment_uuid', $appointment->uuid)->first();
    expect((float)$item->final_price)->toBe(100.0);
    expect($item->manual_discount_type)->toBeNull();
  });
});
