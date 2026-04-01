<?php

use App\Models\Category;
use App\Models\Role;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

function makeServiceUser(string $role): User
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

function makeService(User $user, array $overrides = []): Service
{
  $service = Service::factory()->create($overrides);

  DB::table('services')
    ->where('uuid', $service->uuid)
    ->update(['created_by' => (string)$user->uuid]);

  return $service->fresh();
}

function validServiceData(array $overrides = []): array
{
  return array_merge([
    'name' => 'Corte de Cabelo',
    'price' => '35,00',
  ], $overrides);
}

// ============================================================
// INDEX
// ============================================================

describe('index', function () {
  it('owner pode listar serviços', function () {
    $owner = makeServiceUser('owner');

    $this->actingAs($owner)
      ->get(route('services.index'))
      ->assertOk();
  });

  it('admin pode listar serviços', function () {
    $admin = makeServiceUser('admin');

    $this->actingAs($admin)
      ->get(route('services.index'))
      ->assertOk();
  });

  it('employee pode listar serviços', function () {
    $employee = makeServiceUser('employee');

    $this->actingAs($employee)
      ->get(route('services.index'))
      ->assertOk();
  });

  it('usuário não autenticado é redirecionado', function () {
    $this->get(route('services.index'))
      ->assertRedirect(route('login'));
  });
});

// ============================================================
// STORE
// ============================================================

describe('store', function () {
  it('owner pode cadastrar serviço', function () {
    $owner = makeServiceUser('owner');

    $this->actingAs($owner)
      ->post(route('services.store'), validServiceData())
      ->assertRedirect(route('services.index'));

    $this->assertDatabaseHas('services', ['name' => 'Corte de Cabelo']);
  });

  it('admin pode cadastrar serviço', function () {
    $admin = makeServiceUser('admin');

    $this->actingAs($admin)
      ->post(route('services.store'), validServiceData())
      ->assertRedirect(route('services.index'));

    $this->assertDatabaseHas('services', ['name' => 'Corte de Cabelo']);
  });

  it('employee não pode cadastrar serviço', function () {
    $employee = makeServiceUser('employee');

    $this->actingAs($employee)
      ->post(route('services.store'), validServiceData())
      ->assertForbidden();
  });

  it('nome é obrigatório', function () {
    $admin = makeServiceUser('admin');

    $this->actingAs($admin)
      ->post(route('services.store'), validServiceData(['name' => '']))
      ->assertSessionHasErrors('name');
  });

  it('preço é obrigatório', function () {
    $admin = makeServiceUser('admin');

    $this->actingAs($admin)
      ->post(route('services.store'), validServiceData(['price' => '']))
      ->assertSessionHasErrors('price');
  });

  it('preço negativo é rejeitado', function () {
    $admin = makeServiceUser('admin');

    $this->actingAs($admin)
      ->post(route('services.store'), validServiceData(['price' => '-10']))
      ->assertSessionHasErrors('price');
  });

  it('cadastro com categorias cria e associa categorias', function () {
    $admin = makeServiceUser('admin');

    $this->actingAs($admin)
      ->post(route('services.store'), validServiceData([
        'categories' => ['Corte', 'Navalha'],
      ]))
      ->assertRedirect(route('services.index'));

    $service = Service::where('name', 'Corte de Cabelo')->first();

    expect($service->categories)->toHaveCount(2);
  });

  it('created_by é preenchido com o uuid do usuário autenticado', function () {
    $admin = makeServiceUser('admin');

    $this->actingAs($admin)
      ->post(route('services.store'), validServiceData());

    $service = Service::where('name', 'Corte de Cabelo')->first();

    expect((string)$service->created_by)->toBe((string)$admin->uuid);
  });
});

// ============================================================
// UPDATE
// ============================================================

describe('update', function () {
  it('owner pode atualizar qualquer serviço', function () {
    $owner = makeServiceUser('owner');
    $service = makeService($owner);

    $this->actingAs($owner)
      ->put(route('services.update', $service), validServiceData(['name' => 'Barba']))
      ->assertRedirect(route('services.index'));

    $this->assertDatabaseHas('services', ['uuid' => $service->uuid, 'name' => 'Barba']);
  });

  it('admin pode atualizar serviço criado por ele', function () {
    $admin = makeServiceUser('admin');
    $service = makeService($admin);

    $this->actingAs($admin)
      ->put(route('services.update', $service), validServiceData(['name' => 'Barba']))
      ->assertRedirect(route('services.index'));

    $this->assertDatabaseHas('services', ['uuid' => $service->uuid, 'name' => 'Barba']);
  });

  it('admin não pode atualizar serviço criado por outro admin', function () {
    $admin = makeServiceUser('admin');
    $otherAdmin = makeServiceUser('admin');
    $service = makeService($otherAdmin);

    $this->actingAs($admin)
      ->put(route('services.update', $service), validServiceData())
      ->assertForbidden();
  });

  it('employee não pode atualizar serviço', function () {
    $employee = makeServiceUser('employee');
    $owner = makeServiceUser('owner');
    $service = makeService($owner);

    $this->actingAs($employee)
      ->put(route('services.update', $service), validServiceData())
      ->assertForbidden();
  });

  it('atualizar serviço com novas categorias faz sync', function () {
    $admin = makeServiceUser('admin');
    $service = makeService($admin);

    $service->categories()->attach(
      Category::firstOrCreate(
        ['slug' => 'corte'],
        ['name' => 'Corte']
      )->uuid
    );

    $this->actingAs($admin)
      ->put(route('services.update', $service), validServiceData([
        'categories' => ['Barba'],
      ]));

    $service->refresh();
    expect($service->categories)->toHaveCount(1);
    expect($service->categories->first()->name)->toBe('Barba');
  });

  it('atualizar serviço sem categorias remove todas as categorias', function () {
    $admin = makeServiceUser('admin');
    $service = makeService($admin);

    $service->categories()->attach(
      Category::firstOrCreate(
        ['slug' => 'corte'],
        ['name' => 'Corte']
      )->uuid
    );

    $this->actingAs($admin)
      ->put(route('services.update', $service), validServiceData());

    $service->refresh();
    expect($service->categories)->toHaveCount(0);
  });
});

// ============================================================
// DESTROY
// ============================================================

describe('destroy', function () {
  it('owner pode deletar serviço', function () {
    $owner = makeServiceUser('owner');
    $service = makeService($owner);

    $this->actingAs($owner)
      ->delete(route('services.destroy', $service))
      ->assertRedirect(route('services.index'));

    $this->assertSoftDeleted('services', ['uuid' => $service->uuid]);
  });

  it('admin não pode deletar serviço', function () {
    $admin = makeServiceUser('admin');
    $service = makeService($admin);

    $this->actingAs($admin)
      ->delete(route('services.destroy', $service))
      ->assertForbidden();
  });

  it('employee não pode deletar serviço', function () {
    $employee = makeServiceUser('employee');
    $owner = makeServiceUser('owner');
    $service = makeService($owner);

    $this->actingAs($employee)
      ->delete(route('services.destroy', $service))
      ->assertForbidden();
  });
});
