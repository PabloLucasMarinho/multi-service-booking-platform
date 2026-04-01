<?php

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createRole(string $name): Role
{
  return Role::create(['uuid' => str()->uuid(), 'name' => $name]);
}

function createUser(string $role): User
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

function createClient(User $user): Client
{
  return Client::factory()->create([
    'user_uuid' => $user->uuid,
    'date_of_birth' => '1990-01-01',
  ]);
}

function validClientData(array $overrides = []): array
{
  return array_merge([
    'name' => 'João da Silva',
    'document' => generateUniqueCpf(),
    'date_of_birth' => '01/01/1990',
    'phone' => '21999999999',
    'email' => 'joao@teste.com',
  ], $overrides);
}

// ============================================================
// INDEX
// ============================================================

describe('index', function () {
  it('owner pode listar clientes', function () {
    $owner = createUser('owner');
    createClient($owner);

    $this->actingAs($owner)
      ->get(route('clients.index'))
      ->assertOk();
  });

  it('admin pode listar clientes', function () {
    $admin = createUser('admin');

    $this->actingAs($admin)
      ->get(route('clients.index'))
      ->assertOk();
  });

  it('employee pode listar clientes', function () {
    $employee = createUser('employee');

    $this->actingAs($employee)
      ->get(route('clients.index'))
      ->assertOk();
  });

  it('usuário não autenticado é redirecionado', function () {
    $this->get(route('clients.index'))
      ->assertRedirect(route('login'));
  });
});

// ============================================================
// CREATE
// ============================================================

describe('create', function () {
  it('owner pode acessar formulário de cadastro', function () {
    $owner = createUser('owner');

    $this->actingAs($owner)
      ->get(route('clients.create'))
      ->assertOk();
  });

  it('admin pode acessar formulário de cadastro', function () {
    $admin = createUser('admin');

    $this->actingAs($admin)
      ->get(route('clients.create'))
      ->assertOk();
  });

  it('employee pode acessar formulário de cadastro', function () {
    $employee = createUser('employee');

    $this->actingAs($employee)
      ->get(route('clients.create'))
      ->assertOk();
  });
});

// ============================================================
// STORE
// ============================================================

describe('store', function () {
  it('owner pode cadastrar cliente', function () {
    $owner = createUser('owner');

    $this->actingAs($owner)
      ->post(route('clients.store'), validClientData())
      ->assertRedirect(route('clients.index'));

    $this->assertDatabaseHas('clients', ['name' => 'João da Silva']);
  });

  it('admin pode cadastrar cliente', function () {
    $admin = createUser('admin');

    $this->actingAs($admin)
      ->post(route('clients.store'), validClientData())
      ->assertRedirect(route('clients.index'));

    $this->assertDatabaseHas('clients', ['name' => 'João da Silva']);
  });

  it('employee pode cadastrar cliente', function () {
    $employee = createUser('employee');

    $this->actingAs($employee)
      ->post(route('clients.store'), validClientData())
      ->assertRedirect(route('clients.index'));

    $this->assertDatabaseHas('clients', ['name' => 'João da Silva']);
  });

  it('nome é obrigatório', function () {
    $admin = createUser('admin');

    $this->actingAs($admin)
      ->post(route('clients.store'), validClientData(['name' => '']))
      ->assertSessionHasErrors('name');
  });

  it('cpf é obrigatório', function () {
    $admin = createUser('admin');

    $this->actingAs($admin)
      ->post(route('clients.store'), validClientData(['document' => '']))
      ->assertSessionHasErrors('document');
  });

  it('cpf inválido é rejeitado', function () {
    $admin = createUser('admin');

    $this->actingAs($admin)
      ->post(route('clients.store'), validClientData(['document' => '11111111111']))
      ->assertSessionHasErrors('document');
  });

  it('cpf duplicado é rejeitado', function () {
    $admin = createUser('admin');
    $document = generateUniqueCpf();
    createClient($admin)->update(['document' => $document]);

    $this->actingAs($admin)
      ->post(route('clients.store'), validClientData(['document' => $document]))
      ->assertSessionHasErrors('document');
  });

  it('data de nascimento é obrigatória', function () {
    $admin = createUser('admin');

    $this->actingAs($admin)
      ->post(route('clients.store'), validClientData(['date_of_birth' => '']))
      ->assertSessionHasErrors('date_of_birth');
  });

  it('data de nascimento futura é rejeitada', function () {
    $admin = createUser('admin');

    $this->actingAs($admin)
      ->post(route('clients.store'), validClientData(['date_of_birth' => now()->addDay()->format('d/m/Y')]))
      ->assertSessionHasErrors('date_of_birth');
  });

  it('email duplicado é rejeitado', function () {
    $admin = createUser('admin');
    createClient($admin)->update(['email' => 'duplicado@teste.com']);

    $this->actingAs($admin)
      ->post(route('clients.store'), validClientData(['email' => 'duplicado@teste.com']))
      ->assertSessionHasErrors('email');
  });

  it('email e telefone são opcionais mas pelo menos um é obrigatório', function () {
    $admin = createUser('admin');

    $this->actingAs($admin)
      ->post(route('clients.store'), validClientData(['email' => null, 'phone' => null]))
      ->assertSessionHasErrors(['email', 'phone']);
  });

  it('cadastro com apenas telefone é aceito', function () {
    $admin = createUser('admin');

    $this->actingAs($admin)
      ->post(route('clients.store'), validClientData(['email' => null]))
      ->assertRedirect(route('clients.index'));
  });

  it('cadastro com apenas email é aceito', function () {
    $admin = createUser('admin');

    $this->actingAs($admin)
      ->post(route('clients.store'), validClientData(['phone' => null]))
      ->assertRedirect(route('clients.index'));
  });
});

// ============================================================
// EDIT
// ============================================================

describe('edit', function () {
  it('owner pode acessar formulário de edição', function () {
    $owner = createUser('owner');
    $client = createClient($owner);

    $this->actingAs($owner)
      ->get(route('clients.edit', $client))
      ->assertOk();
  });

  it('admin pode acessar formulário de edição', function () {
    $admin = createUser('admin');
    $client = createClient($admin);

    $this->actingAs($admin)
      ->get(route('clients.edit', $client))
      ->assertOk();
  });

  it('employee pode acessar formulário de edição', function () {
    $employee = createUser('employee');
    $client = createClient($employee);

    $this->actingAs($employee)
      ->get(route('clients.edit', $client))
      ->assertOk();
  });
});

// ============================================================
// UPDATE
// ============================================================

describe('update', function () {
  it('owner pode editar cliente', function () {
    $owner = createUser('owner');
    $client = createClient($owner);

    $this->actingAs($owner)
      ->put(route('clients.update', $client), validClientData(['name' => 'Nome Atualizado']))
      ->assertRedirect(route('clients.index'));

    $this->assertDatabaseHas('clients', ['uuid' => $client->uuid, 'name' => 'Nome Atualizado']);
  });

  it('admin pode editar cliente', function () {
    $admin = createUser('admin');
    $client = createClient($admin);

    $this->actingAs($admin)
      ->put(route('clients.update', $client), validClientData(['name' => 'Nome Atualizado']))
      ->assertRedirect(route('clients.index'));

    $this->assertDatabaseHas('clients', ['uuid' => $client->uuid, 'name' => 'Nome Atualizado']);
  });

  it('employee pode editar cliente', function () {
    $employee = createUser('employee');
    $client = createClient($employee);

    $this->actingAs($employee)
      ->put(route('clients.update', $client), validClientData(['name' => 'Nome Atualizado']))
      ->assertRedirect(route('clients.index'));

    $this->assertDatabaseHas('clients', ['uuid' => $client->uuid, 'name' => 'Nome Atualizado']);
  });

  it('cpf duplicado de outro cliente é rejeitado', function () {
    $admin = createUser('admin');
    $document = generateUniqueCpf();
    createClient($admin)->update(['document' => $document]);
    $client = createClient($admin);

    $this->actingAs($admin)
      ->put(route('clients.update', $client), validClientData(['document' => $document]))
      ->assertSessionHasErrors('document');
  });

  it('cpf do próprio cliente não é rejeitado na edição', function () {
    $admin = createUser('admin');
    $client = createClient($admin);

    $this->actingAs($admin)
      ->put(route('clients.update', $client), validClientData(['document' => $client->document]))
      ->assertRedirect(route('clients.index'));
  });

  it('email duplicado de outro cliente é rejeitado', function () {
    $admin = createUser('admin');
    createClient($admin)->update(['email' => 'duplicado@teste.com']);
    $client = createClient($admin);

    $this->actingAs($admin)
      ->put(route('clients.update', $client), validClientData(['email' => 'duplicado@teste.com']))
      ->assertSessionHasErrors('email');
  });
});

// ============================================================
// DESTROY
// ============================================================

describe('destroy', function () {
  it('owner pode deletar cliente', function () {
    $owner = createUser('owner');
    $client = createClient($owner);

    $this->actingAs($owner)
      ->delete(route('clients.destroy', $client))
      ->assertRedirect(route('clients.index'));

    $this->assertSoftDeleted('clients', ['uuid' => $client->uuid]);
  });

  it('admin não pode deletar cliente', function () {
    $admin = createUser('admin');
    $client = createClient($admin);

    $this->actingAs($admin, 'web')
      ->delete(route('clients.destroy', $client))
      ->assertForbidden();
  });

  it('employee não pode deletar cliente', function () {
    $employee = createUser('employee');
    $client = createClient($employee);

    $this->actingAs($employee)
      ->delete(route('clients.destroy', $client))
      ->assertForbidden();
  });

  it('deletar cliente cancela agendamentos futuros', function () {
    $owner = createUser('owner');
    $client = createClient($owner);

    $appointment = Appointment::factory()->create([
      'client_uuid' => $client->uuid,
      'user_uuid' => $owner->uuid,
      'scheduled_at' => now()->addDay(),
      'status' => AppointmentStatus::Scheduled,
    ]);

    $this->actingAs($owner)
      ->delete(route('clients.destroy', $client));

    $this->assertDatabaseHas('appointments', [
      'uuid' => $appointment->uuid,
      'status' => AppointmentStatus::Cancelled->value,
    ]);
  });
});
