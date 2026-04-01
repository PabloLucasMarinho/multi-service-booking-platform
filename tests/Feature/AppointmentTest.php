<?php

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function makeAppointmentUser(string $role): User
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

function makeAppointment(User $user, array $overrides = []): Appointment
{
  $client = Client::factory()->create(['user_uuid' => $user->uuid]);

  return Appointment::factory()->create(array_merge([
    'user_uuid' => $user->uuid,
    'client_uuid' => $client->uuid,
    'scheduled_at' => now()->addDay(),
    'status' => AppointmentStatus::Scheduled,
  ], $overrides));
}

function validAppointmentData(User $user, Client $client, array $overrides = []): array
{
  return array_merge([
    'scheduled_at' => now()->addDay()->format('d/m/Y'),
    'scheduled_hour' => '10:00',
    'client' => (string)$client->uuid,
    'user' => (string)$user->uuid,
    'notes' => null,
  ], $overrides);
}

// ============================================================
// INDEX
// ============================================================

describe('index', function () {
  it('owner pode listar agendamentos', function () {
    $owner = makeAppointmentUser('owner');

    $this->actingAs($owner)
      ->get(route('appointments.index'))
      ->assertOk();
  });

  it('admin pode listar agendamentos', function () {
    $admin = makeAppointmentUser('admin');

    $this->actingAs($admin)
      ->get(route('appointments.index'))
      ->assertOk();
  });

  it('employee pode listar agendamentos', function () {
    $employee = makeAppointmentUser('employee');

    $this->actingAs($employee)
      ->get(route('appointments.index'))
      ->assertOk();
  });

  it('usuário não autenticado é redirecionado', function () {
    $this->get(route('appointments.index'))
      ->assertRedirect(route('login'));
  });

  it('filtra por data inicial', function () {
    $owner = makeAppointmentUser('owner');
    makeAppointment($owner, ['scheduled_at' => now()->subDays(10)]);
    makeAppointment($owner, ['scheduled_at' => now()->addDays(5)]);

    $response = $this->actingAs($owner)
      ->get(route('appointments.index', ['from' => now()->format('d/m/Y')]));

    $response->assertOk();
  });

  it('filtra por status', function () {
    $owner = makeAppointmentUser('owner');
    makeAppointment($owner, ['status' => AppointmentStatus::Scheduled]);
    makeAppointment($owner, ['status' => AppointmentStatus::Cancelled]);

    $response = $this->actingAs($owner)
      ->get(route('appointments.index', ['statuses' => ['scheduled']]));

    $response->assertOk();
  });
});

// ============================================================
// CREATE
// ============================================================

describe('create', function () {
  it('owner pode acessar formulário de agendamento', function () {
    $owner = makeAppointmentUser('owner');

    $this->actingAs($owner)
      ->get(route('appointments.create'))
      ->assertOk();
  });

  it('admin pode acessar formulário de agendamento', function () {
    $admin = makeAppointmentUser('admin');

    $this->actingAs($admin)
      ->get(route('appointments.create'))
      ->assertOk();
  });

  it('employee pode acessar formulário de agendamento', function () {
    $employee = makeAppointmentUser('employee');

    $this->actingAs($employee)
      ->get(route('appointments.create'))
      ->assertOk();
  });

  it('pré-seleciona cliente quando passado na query string', function () {
    $owner = makeAppointmentUser('owner');
    $client = Client::factory()->create(['user_uuid' => $owner->uuid]);

    $this->actingAs($owner)
      ->get(route('appointments.create', ['client' => $client->uuid]))
      ->assertOk();
  });
});

// ============================================================
// STORE
// ============================================================

describe('store', function () {
  it('owner pode criar agendamento', function () {
    $owner = makeAppointmentUser('owner');
    $client = Client::factory()->create(['user_uuid' => $owner->uuid]);

    $this->actingAs($owner)
      ->post(route('appointments.store'), validAppointmentData($owner, $client))
      ->assertSessionHasNoErrors()
      ->assertRedirect();

    $this->assertDatabaseHas('appointments', [
      'client_uuid' => $client->uuid,
      'user_uuid' => $owner->uuid,
    ]);
  });

  it('admin pode criar agendamento', function () {
    $admin = makeAppointmentUser('admin');
    $client = Client::factory()->create(['user_uuid' => $admin->uuid]);

    $this->actingAs($admin)
      ->post(route('appointments.store'), validAppointmentData($admin, $client))
      ->assertRedirect();

    $this->assertDatabaseHas('appointments', ['client_uuid' => $client->uuid]);
  });

  it('employee pode criar agendamento', function () {
    $employee = makeAppointmentUser('employee');
    $client = Client::factory()->create(['user_uuid' => $employee->uuid]);

    $this->actingAs($employee)
      ->post(route('appointments.store'), validAppointmentData($employee, $client))
      ->assertRedirect();

    $this->assertDatabaseHas('appointments', ['client_uuid' => $client->uuid]);
  });

  it('data é obrigatória', function () {
    $admin = makeAppointmentUser('admin');
    $client = Client::factory()->create(['user_uuid' => $admin->uuid]);

    $this->actingAs($admin)
      ->post(route('appointments.store'), validAppointmentData($admin, $client, ['scheduled_at' => '']))
      ->assertSessionHasErrors('scheduled_at');
  });

  it('hora é obrigatória', function () {
    $admin = makeAppointmentUser('admin');
    $client = Client::factory()->create(['user_uuid' => $admin->uuid]);

    $this->actingAs($admin)
      ->post(route('appointments.store'), validAppointmentData($admin, $client, ['scheduled_hour' => '']))
      ->assertSessionHasErrors('scheduled_at');
  });

  it('data no passado é rejeitada', function () {
    $admin = makeAppointmentUser('admin');
    $client = Client::factory()->create(['user_uuid' => $admin->uuid]);

    $this->actingAs($admin)
      ->post(route('appointments.store'), validAppointmentData($admin, $client, [
        'scheduled_at' => now()->subDay()->format('d/m/Y'),
      ]))
      ->assertSessionHasErrors('scheduled_at');
  });

  it('cliente é obrigatório', function () {
    $admin = makeAppointmentUser('admin');
    $client = Client::factory()->create(['user_uuid' => $admin->uuid]);

    $this->actingAs($admin)
      ->post(route('appointments.store'), validAppointmentData($admin, $client, ['client' => '']))
      ->assertSessionHasErrors('client');
  });

  it('funcionário é obrigatório', function () {
    $admin = makeAppointmentUser('admin');
    $client = Client::factory()->create(['user_uuid' => $admin->uuid]);

    $this->actingAs($admin)
      ->post(route('appointments.store'), validAppointmentData($admin, $client, ['user' => '']))
      ->assertSessionHasErrors('user');
  });

  it('cliente inexistente é rejeitado', function () {
    $admin = makeAppointmentUser('admin');
    $client = Client::factory()->create(['user_uuid' => $admin->uuid]);

    $this->actingAs($admin)
      ->post(route('appointments.store'), validAppointmentData($admin, $client, [
        'client' => str()->uuid(),
      ]))
      ->assertSessionHasErrors('client');
  });
});

// ============================================================
// SHOW
// ============================================================

describe('show', function () {
  it('owner pode ver agendamento', function () {
    $owner = makeAppointmentUser('owner');
    $appointment = makeAppointment($owner);

    $this->actingAs($owner)
      ->get(route('appointments.show', $appointment))
      ->assertOk();
  });

  it('admin pode ver agendamento', function () {
    $admin = makeAppointmentUser('admin');
    $appointment = makeAppointment($admin);

    $this->actingAs($admin)
      ->get(route('appointments.show', $appointment))
      ->assertOk();
  });

  it('employee pode ver próprio agendamento', function () {
    $employee = makeAppointmentUser('employee');
    $appointment = makeAppointment($employee);

    $this->actingAs($employee)
      ->get(route('appointments.show', $appointment))
      ->assertOk();
  });
});

// ============================================================
// UPDATE
// ============================================================

describe('update', function () {
  it('owner pode atualizar qualquer agendamento', function () {
    $owner = makeAppointmentUser('owner');
    $employee = makeAppointmentUser('employee');
    $appointment = makeAppointment($employee);
    $client = Client::factory()->create(['user_uuid' => $owner->uuid]);

    $this->actingAs($owner)
      ->put(route('appointments.update', $appointment), validAppointmentData($employee, $client))
      ->assertRedirect();
  });

  it('admin pode atualizar qualquer agendamento', function () {
    $admin = makeAppointmentUser('admin');
    $employee = makeAppointmentUser('employee');
    $appointment = makeAppointment($employee);
    $client = Client::factory()->create(['user_uuid' => $admin->uuid]);

    $this->actingAs($admin)
      ->put(route('appointments.update', $appointment), validAppointmentData($employee, $client))
      ->assertRedirect();
  });

  it('employee pode atualizar próprio agendamento', function () {
    $employee = makeAppointmentUser('employee');
    $appointment = makeAppointment($employee);
    $client = Client::factory()->create(['user_uuid' => $employee->uuid]);

    $this->actingAs($employee)
      ->put(route('appointments.update', $appointment), validAppointmentData($employee, $client))
      ->assertRedirect();
  });

  it('employee não pode atualizar agendamento de outro', function () {
    $employee = makeAppointmentUser('employee');
    $other = makeAppointmentUser('employee');
    $appointment = makeAppointment($other);
    $client = Client::factory()->create(['user_uuid' => $employee->uuid]);

    $this->actingAs($employee)
      ->put(route('appointments.update', $appointment), validAppointmentData($employee, $client))
      ->assertForbidden();
  });
});

// ============================================================
// DESTROY (CANCELAR)
// ============================================================

describe('destroy', function () {
  it('owner pode cancelar agendamento', function () {
    $owner = makeAppointmentUser('owner');
    $appointment = makeAppointment($owner);

    $this->actingAs($owner)
      ->delete(route('appointments.destroy', $appointment))
      ->assertRedirect(route('appointments.index'));

    $this->assertDatabaseHas('appointments', [
      'uuid' => $appointment->uuid,
      'status' => AppointmentStatus::Cancelled->value,
    ]);
  });

  it('admin pode cancelar agendamento', function () {
    $admin = makeAppointmentUser('admin');
    $appointment = makeAppointment($admin);

    $this->actingAs($admin)
      ->delete(route('appointments.destroy', $appointment))
      ->assertRedirect(route('appointments.index'));

    $this->assertDatabaseHas('appointments', [
      'uuid' => $appointment->uuid,
      'status' => AppointmentStatus::Cancelled->value,
    ]);
  });

  it('employee não pode cancelar agendamento', function () {
    $employee = makeAppointmentUser('employee');
    $appointment = makeAppointment($employee);

    $this->actingAs($employee)
      ->delete(route('appointments.destroy', $appointment))
      ->assertForbidden();
  });
});

// ============================================================
// COMPLETE
// ============================================================

describe('complete', function () {
  it('owner pode concluir agendamento', function () {
    $owner = makeAppointmentUser('owner');
    $appointment = makeAppointment($owner);

    $this->actingAs($owner)
      ->patch(route('appointments.complete', $appointment))
      ->assertRedirect(route('appointments.show', $appointment));

    $this->assertDatabaseHas('appointments', [
      'uuid' => $appointment->uuid,
      'status' => AppointmentStatus::Completed->value,
    ]);
  });

  it('admin pode concluir agendamento', function () {
    $admin = makeAppointmentUser('admin');
    $appointment = makeAppointment($admin);

    $this->actingAs($admin)
      ->patch(route('appointments.complete', $appointment))
      ->assertRedirect(route('appointments.show', $appointment));

    $this->assertDatabaseHas('appointments', [
      'uuid' => $appointment->uuid,
      'status' => AppointmentStatus::Completed->value,
    ]);
  });

  it('employee pode concluir próprio agendamento', function () {
    $employee = makeAppointmentUser('employee');
    $appointment = makeAppointment($employee);

    $this->actingAs($employee)
      ->patch(route('appointments.complete', $appointment))
      ->assertRedirect(route('appointments.show', $appointment));

    $this->assertDatabaseHas('appointments', [
      'uuid' => $appointment->uuid,
      'status' => AppointmentStatus::Completed->value,
    ]);
  });

  it('employee não pode concluir agendamento de outro', function () {
    $employee = makeAppointmentUser('employee');
    $other = makeAppointmentUser('employee');
    $appointment = makeAppointment($other);

    $this->actingAs($employee)
      ->patch(route('appointments.complete', $appointment))
      ->assertForbidden();
  });
});

// ============================================================
// RESTORE
// ============================================================

describe('restore', function () {
  it('owner pode restaurar agendamento cancelado', function () {
    $owner = makeAppointmentUser('owner');
    $appointment = makeAppointment($owner, [
      'status' => AppointmentStatus::Cancelled,
      'scheduled_at' => now()->addDay(),
    ]);

    $this->actingAs($owner)
      ->patch(route('appointments.restore', $appointment))
      ->assertRedirect(route('appointments.show', $appointment));

    $this->assertDatabaseHas('appointments', [
      'uuid' => $appointment->uuid,
      'status' => AppointmentStatus::Scheduled->value,
    ]);
  });

  it('admin pode restaurar agendamento cancelado', function () {
    $admin = makeAppointmentUser('admin');
    $appointment = makeAppointment($admin, [
      'status' => AppointmentStatus::Cancelled,
      'scheduled_at' => now()->addDay(),
    ]);

    $this->actingAs($admin)
      ->patch(route('appointments.restore', $appointment))
      ->assertRedirect(route('appointments.show', $appointment));

    $this->assertDatabaseHas('appointments', [
      'uuid' => $appointment->uuid,
      'status' => AppointmentStatus::Scheduled->value,
    ]);
  });

  it('employee pode restaurar próprio agendamento cancelado', function () {
    $employee = makeAppointmentUser('employee');
    $appointment = makeAppointment($employee, [
      'status' => AppointmentStatus::Cancelled,
      'scheduled_at' => now()->addDay(),
    ]);

    $this->actingAs($employee)
      ->patch(route('appointments.restore', $appointment))
      ->assertRedirect(route('appointments.show', $appointment));

    $this->assertDatabaseHas('appointments', [
      'uuid' => $appointment->uuid,
      'status' => AppointmentStatus::Scheduled->value,
    ]);
  });
});
