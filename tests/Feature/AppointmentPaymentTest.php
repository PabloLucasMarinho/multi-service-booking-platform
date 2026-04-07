<?php

use App\Enums\AppointmentStatus;
use App\Enums\PaymentMethod;
use App\Models\Appointment;
use App\Models\AppointmentPayment;
use App\Models\AppointmentService;
use App\Models\Client;
use App\Models\Role;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

// ============================================================
// HELPERS
// ============================================================

function makePaymentUser(string $role, array $extra = []): User
{
  $roleModel = Role::firstOrCreate(
    ['name' => $role],
    ['uuid' => str()->uuid()]
  );

  return User::factory()->create(array_merge([
    'role_uuid'       => $roleModel->uuid,
    'date_of_birth'   => '1990-01-01',
    'phone'           => '21999999999',
    'zip_code'        => '12345678',
    'address'         => 'Rua Teste',
    'neighborhood'    => 'Centro',
    'city'            => 'Rio de Janeiro',
    'state'           => 'RJ',
    'admission_date'  => '2025-01-01',
  ], $extra));
}

function makePastScheduledAppointment(User $user): Appointment
{
  $client = Client::factory()->create(['user_uuid' => $user->uuid]);

  return Appointment::factory()->create([
    'user_uuid'    => $user->uuid,
    'client_uuid'  => $client->uuid,
    'scheduled_at' => now()->subHour(),
    'status'       => AppointmentStatus::Scheduled,
  ]);
}

function addServiceToAppointment(Appointment $appointment, float $price = 100.0): void
{
  $service = Service::factory()->create(['price' => $price]);

  $item = new AppointmentService([
    'appointment_uuid'          => $appointment->uuid,
    'service_uuid'              => $service->uuid,
    'original_price'            => $price,
    'manual_discount_type'      => null,
    'manual_discount_value'     => null,
    'promotion_amount_snapshot' => null,
  ]);
  $item->applyDiscount(null);
  $item->save();
}

function addPaymentToAppointment(Appointment $appointment, float $amount, string $method = 'cash'): AppointmentPayment
{
  return $appointment->payments()->create([
    'amount'         => $amount,
    'payment_method' => $method,
  ]);
}

// ============================================================
// STORE — ADMITIR PAGAMENTO
// ============================================================

describe('store - admitir pagamento', function () {
  it('owner pode admitir pagamento em agendamento passado', function () {
    $owner       = makePaymentUser('owner');
    $appointment = makePastScheduledAppointment($owner);

    $this->actingAs($owner)
      ->post(route('appointment-payments.store', $appointment), [
        'amount'         => '100,00',
        'payment_method' => PaymentMethod::Cash->value,
      ])
      ->assertRedirect(route('appointments.show', $appointment));

    $this->assertDatabaseHas('appointment_payments', [
      'appointment_uuid' => $appointment->uuid,
      'payment_method'   => PaymentMethod::Cash->value,
    ]);
  });

  it('admin pode admitir pagamento em agendamento passado', function () {
    $admin       = makePaymentUser('admin');
    $appointment = makePastScheduledAppointment($admin);

    $this->actingAs($admin)
      ->post(route('appointment-payments.store', $appointment), [
        'amount'         => '50,00',
        'payment_method' => PaymentMethod::Pix->value,
      ])
      ->assertRedirect(route('appointments.show', $appointment));

    $this->assertDatabaseHas('appointment_payments', [
      'appointment_uuid' => $appointment->uuid,
      'payment_method'   => PaymentMethod::Pix->value,
    ]);
  });

  it('employee pode admitir pagamento em próprio agendamento passado', function () {
    $employee    = makePaymentUser('employee');
    $appointment = makePastScheduledAppointment($employee);

    $this->actingAs($employee)
      ->post(route('appointment-payments.store', $appointment), [
        'amount'         => '80,00',
        'payment_method' => PaymentMethod::Debit->value,
      ])
      ->assertRedirect(route('appointments.show', $appointment));

    $this->assertDatabaseHas('appointment_payments', [
      'appointment_uuid' => $appointment->uuid,
      'payment_method'   => PaymentMethod::Debit->value,
    ]);
  });

  it('employee não pode admitir pagamento em agendamento de outro', function () {
    $employee    = makePaymentUser('employee');
    $other       = makePaymentUser('employee');
    $appointment = makePastScheduledAppointment($other);

    $this->actingAs($employee)
      ->post(route('appointment-payments.store', $appointment), [
        'amount'         => '100,00',
        'payment_method' => PaymentMethod::Cash->value,
      ])
      ->assertForbidden();
  });

  it('agendamento futuro não aceita pagamento', function () {
    $owner       = makePaymentUser('owner');
    $client      = Client::factory()->create(['user_uuid' => $owner->uuid]);
    $appointment = Appointment::factory()->create([
      'user_uuid'    => $owner->uuid,
      'client_uuid'  => $client->uuid,
      'scheduled_at' => now()->addDay(),
      'status'       => AppointmentStatus::Scheduled,
    ]);

    $this->actingAs($owner)
      ->post(route('appointment-payments.store', $appointment), [
        'amount'         => '100,00',
        'payment_method' => PaymentMethod::Cash->value,
      ])
      ->assertForbidden();
  });

  it('agendamento concluído não aceita novo pagamento', function () {
    $owner       = makePaymentUser('owner');
    $client      = Client::factory()->create(['user_uuid' => $owner->uuid]);
    $appointment = Appointment::factory()->create([
      'user_uuid'    => $owner->uuid,
      'client_uuid'  => $client->uuid,
      'scheduled_at' => now()->subHour(),
      'status'       => AppointmentStatus::Completed,
    ]);

    $this->actingAs($owner)
      ->post(route('appointment-payments.store', $appointment), [
        'amount'         => '100,00',
        'payment_method' => PaymentMethod::Cash->value,
      ])
      ->assertForbidden();
  });

  it('agendamento cancelado não aceita pagamento', function () {
    $owner       = makePaymentUser('owner');
    $client      = Client::factory()->create(['user_uuid' => $owner->uuid]);
    $appointment = Appointment::factory()->create([
      'user_uuid'    => $owner->uuid,
      'client_uuid'  => $client->uuid,
      'scheduled_at' => now()->subHour(),
      'status'       => AppointmentStatus::Cancelled,
    ]);

    $this->actingAs($owner)
      ->post(route('appointment-payments.store', $appointment), [
        'amount'         => '100,00',
        'payment_method' => PaymentMethod::Cash->value,
      ])
      ->assertForbidden();
  });

  it('valor é obrigatório', function () {
    $owner       = makePaymentUser('owner');
    $appointment = makePastScheduledAppointment($owner);

    $this->actingAs($owner)
      ->post(route('appointment-payments.store', $appointment), [
        'amount'         => '',
        'payment_method' => PaymentMethod::Cash->value,
      ])
      ->assertSessionHasErrors('amount');
  });

  it('valor zero é rejeitado', function () {
    $owner       = makePaymentUser('owner');
    $appointment = makePastScheduledAppointment($owner);

    $this->actingAs($owner)
      ->post(route('appointment-payments.store', $appointment), [
        'amount'         => '0,00',
        'payment_method' => PaymentMethod::Cash->value,
      ])
      ->assertSessionHasErrors('amount');
  });

  it('forma de pagamento é obrigatória', function () {
    $owner       = makePaymentUser('owner');
    $appointment = makePastScheduledAppointment($owner);

    $this->actingAs($owner)
      ->post(route('appointment-payments.store', $appointment), [
        'amount'         => '100,00',
        'payment_method' => '',
      ])
      ->assertSessionHasErrors('payment_method');
  });

  it('forma de pagamento inválida é rejeitada', function () {
    $owner       = makePaymentUser('owner');
    $appointment = makePastScheduledAppointment($owner);

    $this->actingAs($owner)
      ->post(route('appointment-payments.store', $appointment), [
        'amount'         => '100,00',
        'payment_method' => 'boleto',
      ])
      ->assertSessionHasErrors('payment_method');
  });

  it('múltiplos pagamentos podem ser admitidos no mesmo agendamento', function () {
    $owner       = makePaymentUser('owner');
    $appointment = makePastScheduledAppointment($owner);

    $this->actingAs($owner)->post(route('appointment-payments.store', $appointment), [
      'amount'         => '60,00',
      'payment_method' => PaymentMethod::Credit->value,
    ]);

    $this->actingAs($owner)->post(route('appointment-payments.store', $appointment), [
      'amount'         => '40,00',
      'payment_method' => PaymentMethod::Pix->value,
    ]);

    expect($appointment->payments()->count())->toBe(2);
    expect((float) $appointment->payments()->sum('amount'))->toBe(100.0);
  });

  it('máscara brasileira com milhar é convertida corretamente', function () {
    $owner       = makePaymentUser('owner');
    $appointment = makePastScheduledAppointment($owner);

    $this->actingAs($owner)
      ->post(route('appointment-payments.store', $appointment), [
        'amount'         => '1.500,00',
        'payment_method' => PaymentMethod::Cash->value,
      ])
      ->assertSessionHasNoErrors();

    expect((float) $appointment->payments()->first()->amount)->toBe(1500.0);
  });
});

// ============================================================
// DESTROY — REMOVER PAGAMENTO
// ============================================================

describe('destroy - remover pagamento', function () {
  it('owner pode remover pagamento', function () {
    $owner       = makePaymentUser('owner');
    $appointment = makePastScheduledAppointment($owner);
    $payment     = addPaymentToAppointment($appointment, 100.0);

    $this->actingAs($owner)
      ->delete(route('appointment-payments.destroy', $payment))
      ->assertRedirect(route('appointments.show', $appointment));

    $this->assertDatabaseMissing('appointment_payments', ['uuid' => $payment->uuid]);
  });

  it('admin pode remover pagamento', function () {
    $admin       = makePaymentUser('admin');
    $appointment = makePastScheduledAppointment($admin);
    $payment     = addPaymentToAppointment($appointment, 50.0);

    $this->actingAs($admin)
      ->delete(route('appointment-payments.destroy', $payment))
      ->assertRedirect(route('appointments.show', $appointment));

    $this->assertDatabaseMissing('appointment_payments', ['uuid' => $payment->uuid]);
  });

  it('employee pode remover pagamento do próprio agendamento', function () {
    $employee    = makePaymentUser('employee');
    $appointment = makePastScheduledAppointment($employee);
    $payment     = addPaymentToAppointment($appointment, 80.0);

    $this->actingAs($employee)
      ->delete(route('appointment-payments.destroy', $payment))
      ->assertRedirect(route('appointments.show', $appointment));

    $this->assertDatabaseMissing('appointment_payments', ['uuid' => $payment->uuid]);
  });

  it('employee não pode remover pagamento de agendamento de outro', function () {
    $employee    = makePaymentUser('employee');
    $other       = makePaymentUser('employee');
    $appointment = makePastScheduledAppointment($other);
    $payment     = addPaymentToAppointment($appointment, 100.0);

    $this->actingAs($employee)
      ->delete(route('appointment-payments.destroy', $payment))
      ->assertForbidden();
  });
});

// ============================================================
// COMPLETE — LÓGICA DE GORJETA E DESCONTO
// ============================================================

describe('complete - lógica de pagamento', function () {
  it('pagamento exato não gera gorjeta nem desconto', function () {
    $owner       = makePaymentUser('owner');
    $appointment = makePastScheduledAppointment($owner);
    addServiceToAppointment($appointment, 100.0);
    addPaymentToAppointment($appointment, 100.0);

    $this->actingAs($owner)
      ->patch(route('appointments.complete', $appointment))
      ->assertRedirect(route('appointments.show', $appointment));

    $this->assertDatabaseHas('appointments', [
      'uuid'             => $appointment->uuid,
      'status'           => AppointmentStatus::Completed->value,
      'tip'              => null,
      'closing_discount' => null,
    ]);
  });

  it('pagamento a mais salva gorjeta correta', function () {
    $owner       = makePaymentUser('owner');
    $appointment = makePastScheduledAppointment($owner);
    addServiceToAppointment($appointment, 100.0);
    addPaymentToAppointment($appointment, 115.0); // R$ 15 de gorjeta

    $this->actingAs($owner)
      ->patch(route('appointments.complete', $appointment))
      ->assertRedirect(route('appointments.show', $appointment));

    $this->assertDatabaseHas('appointments', [
      'uuid'   => $appointment->uuid,
      'status' => AppointmentStatus::Completed->value,
      'tip'    => '15.00',
    ]);
  });

  it('gorjeta com múltiplos pagamentos é calculada corretamente', function () {
    $owner       = makePaymentUser('owner');
    $appointment = makePastScheduledAppointment($owner);
    addServiceToAppointment($appointment, 100.0);
    addPaymentToAppointment($appointment, 60.0, PaymentMethod::Pix->value);
    addPaymentToAppointment($appointment, 50.0, PaymentMethod::Cash->value); // total: 110 → gorjeta 10

    $this->actingAs($owner)
      ->patch(route('appointments.complete', $appointment))
      ->assertRedirect();

    $this->assertDatabaseHas('appointments', [
      'uuid' => $appointment->uuid,
      'tip'  => '10.00',
    ]);
  });

  it('admin pode fechar com desconto sem necessidade de autorização adicional', function () {
    $admin       = makePaymentUser('admin');
    $appointment = makePastScheduledAppointment($admin);
    addServiceToAppointment($appointment, 100.0);
    addPaymentToAppointment($appointment, 80.0); // R$ 20 de desconto

    $this->actingAs($admin)
      ->patch(route('appointments.complete', $appointment))
      ->assertRedirect(route('appointments.show', $appointment));

    $this->assertDatabaseHas('appointments', [
      'uuid'             => $appointment->uuid,
      'status'           => AppointmentStatus::Completed->value,
      'closing_discount' => '20.00',
    ]);
  });

  it('owner pode fechar com desconto sem necessidade de autorização adicional', function () {
    $owner       = makePaymentUser('owner');
    $appointment = makePastScheduledAppointment($owner);
    addServiceToAppointment($appointment, 100.0);
    addPaymentToAppointment($appointment, 70.0); // R$ 30 de desconto

    $this->actingAs($owner)
      ->patch(route('appointments.complete', $appointment))
      ->assertRedirect(route('appointments.show', $appointment));

    $this->assertDatabaseHas('appointments', [
      'uuid'             => $appointment->uuid,
      'closing_discount' => '30.00',
    ]);
  });

  it('employee sem credenciais não pode fechar com desconto', function () {
    $employee    = makePaymentUser('employee');
    $appointment = makePastScheduledAppointment($employee);
    addServiceToAppointment($appointment, 100.0);
    addPaymentToAppointment($appointment, 80.0); // R$ 20 de desconto

    $this->actingAs($employee)
      ->patch(route('appointments.complete', $appointment))
      ->assertRedirect()
      ->assertSessionHas('error');

    $this->assertDatabaseMissing('appointments', [
      'uuid'   => $appointment->uuid,
      'status' => AppointmentStatus::Completed->value,
    ]);
  });

  it('employee com credenciais de admin válidas fecha com desconto e salva authorized_by', function () {
    $admin    = makePaymentUser('admin', ['password' => Hash::make('senha123')]);
    $employee = makePaymentUser('employee');

    $appointment = makePastScheduledAppointment($employee);
    addServiceToAppointment($appointment, 100.0);
    addPaymentToAppointment($appointment, 85.0); // R$ 15 de desconto

    $this->actingAs($employee)
      ->patch(route('appointments.complete', $appointment), [
        'admin_email'    => $admin->email,
        'admin_password' => 'senha123',
      ])
      ->assertRedirect(route('appointments.show', $appointment));

    $this->assertDatabaseHas('appointments', [
      'uuid'                    => $appointment->uuid,
      'status'                  => AppointmentStatus::Completed->value,
      'closing_discount'        => '15.00',
      'discount_authorized_by'  => $admin->uuid,
    ]);
  });

  it('employee com credenciais de outro employee é rejeitado', function () {
    $employee    = makePaymentUser('employee', ['password' => Hash::make('senha123')]);
    $other       = makePaymentUser('employee');
    $appointment = makePastScheduledAppointment($other);
    addServiceToAppointment($appointment, 100.0);
    addPaymentToAppointment($appointment, 80.0);

    $this->actingAs($other)
      ->patch(route('appointments.complete', $appointment), [
        'admin_email'    => $employee->email,
        'admin_password' => 'senha123',
      ])
      ->assertRedirect()
      ->assertSessionHas('error');

    $this->assertDatabaseMissing('appointments', [
      'uuid'   => $appointment->uuid,
      'status' => AppointmentStatus::Completed->value,
    ]);
  });

  it('employee com senha errada de admin é rejeitado', function () {
    $admin    = makePaymentUser('admin', ['password' => Hash::make('correta')]);
    $employee = makePaymentUser('employee');

    $appointment = makePastScheduledAppointment($employee);
    addServiceToAppointment($appointment, 100.0);
    addPaymentToAppointment($appointment, 80.0);

    $this->actingAs($employee)
      ->patch(route('appointments.complete', $appointment), [
        'admin_email'    => $admin->email,
        'admin_password' => 'errada',
      ])
      ->assertRedirect()
      ->assertSessionHas('error');

    $this->assertDatabaseMissing('appointments', [
      'uuid'   => $appointment->uuid,
      'status' => AppointmentStatus::Completed->value,
    ]);
  });

  it('employee com credenciais de owner válidas fecha com desconto', function () {
    $owner    = makePaymentUser('owner', ['password' => Hash::make('owner123')]);
    $employee = makePaymentUser('employee');

    $appointment = makePastScheduledAppointment($employee);
    addServiceToAppointment($appointment, 100.0);
    addPaymentToAppointment($appointment, 90.0); // R$ 10 de desconto

    $this->actingAs($employee)
      ->patch(route('appointments.complete', $appointment), [
        'admin_email'    => $owner->email,
        'admin_password' => 'owner123',
      ])
      ->assertRedirect(route('appointments.show', $appointment));

    $this->assertDatabaseHas('appointments', [
      'uuid'                   => $appointment->uuid,
      'status'                 => AppointmentStatus::Completed->value,
      'closing_discount'       => '10.00',
      'discount_authorized_by' => $owner->uuid,
    ]);
  });
});
