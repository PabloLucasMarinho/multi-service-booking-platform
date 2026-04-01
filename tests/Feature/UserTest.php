<?php

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

function makeUser(string $role): User
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

function validUserData(array $overrides = []): array
{
  return array_merge([
    'name' => 'João da Silva',
    'email' => 'joao@teste.com',
    'document' => generateUniqueCpf(),
    'date_of_birth' => '01/01/1990',
    'phone' => '21999999999',
    'zip_code' => '12345678',
    'address' => 'Rua Teste',
    'neighborhood' => 'Centro',
    'city' => 'Rio de Janeiro',
    'state' => 'RJ',
    'admission_date' => '01/01/2025',
    'role' => 'employee',
    'salary' => null,
  ], $overrides);
}

beforeEach(function () {
  Queue::fake();

  Role::firstOrCreate(['name' => 'owner'], ['uuid' => str()->uuid()]);
  Role::firstOrCreate(['name' => 'admin'], ['uuid' => str()->uuid()]);
  Role::firstOrCreate(['name' => 'employee'], ['uuid' => str()->uuid()]);
});

// ============================================================
// INDEX
// ============================================================

describe('index', function () {
  it('owner pode listar funcionários', function () {
    $owner = makeUser('owner');

    $this->actingAs($owner)
      ->get(route('users.index'))
      ->assertOk();
  });

  it('admin pode listar funcionários', function () {
    $admin = makeUser('admin');

    $this->actingAs($admin)
      ->get(route('users.index'))
      ->assertOk();
  });

  it('employee não pode listar funcionários', function () {
    $employee = makeUser('employee');

    $this->actingAs($employee)
      ->get(route('users.index'))
      ->assertForbidden();
  });

  it('usuário não autenticado é redirecionado', function () {
    $this->get(route('users.index'))
      ->assertRedirect(route('login'));
  });
});

// ============================================================
// CREATE
// ============================================================

describe('create', function () {
  it('owner pode acessar formulário de cadastro', function () {
    $owner = makeUser('owner');

    $this->actingAs($owner)
      ->get(route('users.create'))
      ->assertOk();
  });

  it('admin pode acessar formulário de cadastro', function () {
    $admin = makeUser('admin');

    $this->actingAs($admin)
      ->get(route('users.create'))
      ->assertOk();
  });

  it('employee não pode acessar formulário de cadastro', function () {
    $employee = makeUser('employee');

    $this->actingAs($employee)
      ->get(route('users.create'))
      ->assertForbidden();
  });
});

// ============================================================
// STORE
// ============================================================

describe('store', function () {
  it('owner pode cadastrar funcionário', function () {
    $owner = makeUser('owner');

    $this->actingAs($owner)
      ->post(route('users.store'), validUserData())
      ->assertRedirect(route('users.index'));

    $this->assertDatabaseHas('users', ['name' => 'João da Silva']);
  });

  it('admin pode cadastrar funcionário', function () {
    $admin = makeUser('admin');

    $this->actingAs($admin)
      ->post(route('users.store'), validUserData())
      ->assertRedirect(route('users.index'));

    $this->assertDatabaseHas('users', ['name' => 'João da Silva']);
  });

  it('employee não pode cadastrar funcionário', function () {
    $employee = makeUser('employee');

    $this->actingAs($employee)
      ->post(route('users.store'), validUserData())
      ->assertForbidden();
  });

  it('nome é obrigatório', function () {
    $admin = makeUser('admin');

    $this->actingAs($admin)
      ->post(route('users.store'), validUserData(['name' => '']))
      ->assertSessionHasErrors('name');
  });

  it('email é obrigatório', function () {
    $admin = makeUser('admin');

    $this->actingAs($admin)
      ->post(route('users.store'), validUserData(['email' => '']))
      ->assertSessionHasErrors('email');
  });

  it('email inválido é rejeitado', function () {
    $admin = makeUser('admin');

    $this->actingAs($admin)
      ->post(route('users.store'), validUserData(['email' => 'email-invalido']))
      ->assertSessionHasErrors('email');
  });

  it('email duplicado é rejeitado', function () {
    $admin = makeUser('admin');
    makeUser('employee')->update(['email' => 'duplicado@teste.com']);

    $this->actingAs($admin)
      ->post(route('users.store'), validUserData(['email' => 'duplicado@teste.com']))
      ->assertSessionHasErrors('email');
  });

  it('cpf é obrigatório', function () {
    $admin = makeUser('admin');

    $this->actingAs($admin)
      ->post(route('users.store'), validUserData(['document' => '']))
      ->assertSessionHasErrors('document');
  });

  it('cpf inválido é rejeitado', function () {
    $admin = makeUser('admin');

    $this->actingAs($admin)
      ->post(route('users.store'), validUserData(['document' => '11111111111']))
      ->assertSessionHasErrors('document');
  });

  it('cpf duplicado é rejeitado', function () {
    $admin = makeUser('admin');
    $document = generateUniqueCpf();
    makeUser('employee')->update(['document' => $document]);

    $this->actingAs($admin)
      ->post(route('users.store'), validUserData(['document' => $document]))
      ->assertSessionHasErrors('document');
  });

  it('data de nascimento é obrigatória', function () {
    $admin = makeUser('admin');

    $this->actingAs($admin)
      ->post(route('users.store'), validUserData(['date_of_birth' => '']))
      ->assertSessionHasErrors('date_of_birth');
  });

  it('data de nascimento futura é rejeitada', function () {
    $admin = makeUser('admin');

    $this->actingAs($admin)
      ->post(route('users.store'), validUserData(['date_of_birth' => now()->addDay()->format('d/m/Y')]))
      ->assertSessionHasErrors('date_of_birth');
  });

  it('estado inválido é rejeitado', function () {
    $admin = makeUser('admin');

    $this->actingAs($admin)
      ->post(route('users.store'), validUserData(['state' => 'XX']))
      ->assertSessionHasErrors('state');
  });

  it('função inválida é rejeitada', function () {
    $admin = makeUser('admin');

    $this->actingAs($admin)
      ->post(route('users.store'), validUserData(['role' => 'superadmin']))
      ->assertSessionHasErrors('role');
  });

  it('detecta usuário soft deleted por email e retorna json', function () {
    $admin = makeUser('admin');
    $deleted = makeUser('employee');
    $deleted->delete();

    $this->actingAs($admin)
      ->post(route('users.store'), validUserData(['email' => $deleted->email]))
      ->assertJson(['deleted_user' => true, 'uuid' => $deleted->uuid]);
  });

  it('detecta usuário soft deleted por documento e retorna json', function () {
    $admin = makeUser('admin');
    $deleted = makeUser('employee');
    $document = $deleted->document;
    $deleted->delete();

    $this->actingAs($admin)
      ->post(route('users.store'), validUserData(['document' => $document]))
      ->assertJson(['deleted_user' => true]);
  });
});

// ============================================================
// SHOW
// ============================================================

describe('show', function () {
  it('owner pode ver qualquer perfil', function () {
    $owner = makeUser('owner');
    $employee = makeUser('employee');

    $this->actingAs($owner)
      ->get(route('users.show', $employee))
      ->assertOk();
  });

  it('admin pode ver perfil de employee', function () {
    $admin = makeUser('admin');
    $employee = makeUser('employee');

    $this->actingAs($admin)
      ->get(route('users.show', $employee))
      ->assertOk();
  });

  it('usuário pode ver o próprio perfil', function () {
    $employee = makeUser('employee');
    $fresh = $employee->fresh();

    $this->actingAs($fresh)
      ->get(route('users.show', ['user' => $fresh->uuid]))
      ->assertOk();
  });

  it('employee não pode ver perfil de outro employee', function () {
    $employee = makeUser('employee');
    $other = makeUser('employee');

    $this->actingAs($employee)
      ->get(route('users.show', $other))
      ->assertForbidden();
  });
});

// ============================================================
// EDIT
// ============================================================

describe('edit', function () {
  it('owner pode editar qualquer usuário', function () {
    $owner = makeUser('owner');
    $employee = makeUser('employee');

    $this->actingAs($owner)
      ->get(route('users.edit', $employee))
      ->assertOk();
  });

  it('admin pode editar employee', function () {
    $admin = makeUser('admin');
    $employee = makeUser('employee');

    $this->actingAs($admin)
      ->get(route('users.edit', $employee))
      ->assertOk();
  });

  it('admin pode editar o próprio cadastro', function () {
    $admin = makeUser('admin');

    $this->actingAs($admin)
      ->get(route('users.edit', $admin))
      ->assertOk();
  });

  it('admin não pode editar outro admin', function () {
    $admin = makeUser('admin');
    $otherAdmin = makeUser('admin');

    $this->actingAs($admin)
      ->get(route('users.edit', $otherAdmin))
      ->assertForbidden();
  });

  it('admin não pode editar owner', function () {
    $admin = makeUser('admin');
    $owner = makeUser('owner');

    $this->actingAs($admin)
      ->get(route('users.edit', $owner))
      ->assertForbidden();
  });

  it('employee não pode editar nenhum usuário', function () {
    $employee = makeUser('employee');
    $other = makeUser('employee');

    $this->actingAs($employee)
      ->get(route('users.edit', $other))
      ->assertForbidden();
  });
});

// ============================================================
// UPDATE
// ============================================================

describe('update', function () {
  it('owner pode atualizar qualquer usuário', function () {
    $owner = makeUser('owner');
    $employee = makeUser('employee');

    $this->actingAs($owner)
      ->put(route('users.update', $employee), validUserData(['name' => 'Nome Atualizado']))
      ->assertRedirect(route('users.index'));

    $this->assertDatabaseHas('users', ['uuid' => $employee->uuid, 'name' => 'Nome Atualizado']);
  });

  it('admin pode atualizar employee', function () {
    $admin = makeUser('admin');
    $employee = makeUser('employee');

    $this->actingAs($admin)
      ->put(route('users.update', $employee), validUserData(['name' => 'Nome Atualizado']))
      ->assertRedirect(route('users.index'));

    $this->assertDatabaseHas('users', ['uuid' => $employee->uuid, 'name' => 'Nome Atualizado']);
  });

  it('admin pode atualizar o próprio cadastro e é redirecionado para show', function () {
    $admin = makeUser('admin');

    $this->actingAs($admin)
      ->put(route('users.update', $admin), validUserData([
        'email' => $admin->email,
        'document' => $admin->document,
        'role' => 'admin',
      ]))
      ->assertRedirect(route('users.show', $admin));
  });

  it('admin não pode atualizar outro admin', function () {
    $admin = makeUser('admin');
    $otherAdmin = makeUser('admin');

    $this->actingAs($admin)
      ->put(route('users.update', $otherAdmin), validUserData())
      ->assertForbidden();
  });

  it('admin não pode atualizar owner', function () {
    $admin = makeUser('admin');
    $owner = makeUser('owner');

    $this->actingAs($admin)
      ->put(route('users.update', $owner), validUserData())
      ->assertForbidden();
  });

  it('employee não pode atualizar nenhum usuário', function () {
    $employee = makeUser('employee');
    $other = makeUser('employee');

    $this->actingAs($employee)
      ->put(route('users.update', $other), validUserData())
      ->assertForbidden();
  });

  it('cpf do próprio usuário não é rejeitado na edição', function () {
    $admin = makeUser('admin');
    $employee = makeUser('employee');

    $this->actingAs($admin)
      ->put(route('users.update', $employee), validUserData([
        'email' => $employee->email,
        'document' => $employee->document,
      ]))
      ->assertRedirect(route('users.index'));
  });

  it('email duplicado de outro usuário é rejeitado', function () {
    $admin = makeUser('admin');
    $employee = makeUser('employee');
    $other = makeUser('employee');

    $this->actingAs($admin)
      ->put(route('users.update', $employee), validUserData(['email' => $other->email]))
      ->assertSessionHasErrors('email');
  });
});

// ============================================================
// DESTROY
// ============================================================

describe('destroy', function () {
  it('owner pode deletar employee', function () {
    $owner = makeUser('owner');
    $employee = makeUser('employee');

    $this->actingAs($owner)
      ->delete(route('users.destroy', $employee))
      ->assertRedirect(route('users.index'));

    $this->assertSoftDeleted('users', ['uuid' => $employee->uuid]);
  });

  it('admin não pode deletar nenhum usuário', function () {
    $admin = makeUser('admin');
    $employee = makeUser('employee');

    $this->actingAs($admin)
      ->delete(route('users.destroy', $employee))
      ->assertForbidden();
  });

  it('employee não pode deletar nenhum usuário', function () {
    $employee = makeUser('employee');
    $other = makeUser('employee');

    $this->actingAs($employee)
      ->delete(route('users.destroy', $other))
      ->assertForbidden();
  });

  it('deletar funcionário cancela agendamentos futuros', function () {
    $owner = makeUser('owner');
    $employee = makeUser('employee');

    $appointment = Appointment::factory()->create([
      'user_uuid' => $employee->uuid,
      'scheduled_at' => now()->addDay(),
      'status' => AppointmentStatus::Scheduled,
    ]);

    $this->actingAs($owner)
      ->delete(route('users.destroy', $employee));

    $this->assertDatabaseHas('appointments', [
      'uuid' => $appointment->uuid,
      'status' => AppointmentStatus::Cancelled->value,
    ]);
  });
});

// ============================================================
// ANONYMIZE
// ============================================================

describe('anonymize', function () {
  it('owner pode anonimizar usuário deletado', function () {
    $owner = makeUser('owner');
    $employee = makeUser('employee');
    $employee->delete();

    $this->actingAs($owner)
      ->delete(route('users.anonymize', $employee->uuid))
      ->assertJson(['success' => true]);

    $this->assertDatabaseHas('users', ['uuid' => $employee->uuid, 'email' => null]);
  });

  it('admin não pode anonimizar usuário', function () {
    $admin = makeUser('admin');
    $employee = makeUser('employee');
    $employee->delete();

    $this->actingAs($admin)
      ->delete(route('users.anonymize', $employee->uuid))
      ->assertForbidden();
  });
});

// ============================================================
// RESTORE
// ============================================================

describe('restore', function () {
  it('owner pode restaurar usuário deletado', function () {
    $owner = makeUser('owner');
    $employee = makeUser('employee');
    $employee->delete();

    $this->actingAs($owner)
      ->put(route('users.restore', $employee->uuid))
      ->assertJson(['success' => true]);

    $this->assertNotSoftDeleted('users', ['uuid' => $employee->uuid]);
  });

  it('admin não pode restaurar usuário', function () {
    $admin = makeUser('admin');
    $employee = makeUser('employee');
    $employee->delete();

    $this->actingAs($admin)
      ->put(route('users.restore', $employee->uuid))
      ->assertForbidden();
  });
});
