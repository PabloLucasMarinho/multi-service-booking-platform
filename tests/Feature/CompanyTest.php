<?php

use App\Models\Company;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function makeCompanyUser(string $role): User
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

function validCompanyData(array $overrides = []): array
{
  return array_merge([
    'name' => 'Barbearia do João LTDA',
    'fantasy_name' => 'Barbearia do João',
    'document' => '12345678000195',
    'email' => 'contato@barbearia.com',
    'phone' => '21912345678',
    'zip_code' => '21725180',
    'address' => 'Rua Hélio do Amaral',
    'address_number' => '30',
    'address_complement' => 'BL 6 APT 203',
    'neighborhood' => 'Realengo',
    'city' => 'Rio de Janeiro',
    'state' => 'RJ',
  ], $overrides);
}

// ============================================================
// SHOW
// ============================================================

describe('show', function () {
  it('owner pode ver dados da empresa', function () {
    $owner = makeCompanyUser('owner');

    $this->actingAs($owner)
      ->get(route('company.index'))
      ->assertOk();
  });

  it('admin não pode ver dados da empresa', function () {
    $admin = makeCompanyUser('admin');

    $this->actingAs($admin)
      ->get(route('company.index'))
      ->assertForbidden();
  });

  it('employee não pode ver dados da empresa', function () {
    $employee = makeCompanyUser('employee');

    $this->actingAs($employee)
      ->get(route('company.index'))
      ->assertForbidden();
  });

  it('usuário não autenticado é redirecionado', function () {
    $this->get(route('company.index'))
      ->assertRedirect(route('login'));
  });

  it('exibe empresa existente', function () {
    $owner = makeCompanyUser('owner');
    Company::create(validCompanyData());

    $this->actingAs($owner)
      ->get(route('company.index'))
      ->assertOk();
  });

  it('exibe formulário vazio quando empresa não existe', function () {
    $owner = makeCompanyUser('owner');

    $this->assertDatabaseEmpty('company');

    $this->actingAs($owner)
      ->get(route('company.index'))
      ->assertOk();
  });
});

// ============================================================
// STORE
// ============================================================

describe('store', function () {
  it('owner pode salvar dados da empresa', function () {
    $owner = makeCompanyUser('owner');

    $this->actingAs($owner)
      ->post(route('company.save'), validCompanyData())
      ->assertRedirect(route('company.index'));

    $this->assertDatabaseHas('company', ['name' => 'Barbearia do João LTDA']);
  });

  it('admin não pode salvar dados da empresa', function () {
    $admin = makeCompanyUser('admin');

    $this->actingAs($admin)
      ->post(route('company.save'), validCompanyData())
      ->assertForbidden();
  });

  it('employee não pode salvar dados da empresa', function () {
    $employee = makeCompanyUser('employee');

    $this->actingAs($employee)
      ->post(route('company.save'), validCompanyData())
      ->assertForbidden();
  });

  it('razão social é obrigatória', function () {
    $owner = makeCompanyUser('owner');

    $this->actingAs($owner)
      ->post(route('company.save'), validCompanyData(['name' => '']))
      ->assertSessionHasErrors('name');
  });

  it('email inválido é rejeitado', function () {
    $owner = makeCompanyUser('owner');

    $this->actingAs($owner)
      ->post(route('company.save'), validCompanyData(['email' => 'email-invalido']))
      ->assertSessionHasErrors('email');
  });

  it('cnpj inválido é rejeitado', function () {
    $owner = makeCompanyUser('owner');

    $this->actingAs($owner)
      ->post(route('company.save'), validCompanyData(['document' => '11111111111111']))
      ->assertSessionHasErrors('document');
  });

  it('documento com menos de 11 dígitos é rejeitado', function () {
    $owner = makeCompanyUser('owner');

    $this->actingAs($owner)
      ->post(route('company.save'), validCompanyData(['document' => '1234567890']))
      ->assertSessionHasErrors('document');
  });

  it('cep com menos de 8 dígitos é rejeitado', function () {
    $owner = makeCompanyUser('owner');

    $this->actingAs($owner)
      ->post(route('company.save'), validCompanyData(['zip_code' => '1234567']))
      ->assertSessionHasErrors('zip_code');
  });

  it('estado com mais de 2 caracteres é rejeitado', function () {
    $owner = makeCompanyUser('owner');

    $this->actingAs($owner)
      ->post(route('company.save'), validCompanyData(['state' => 'RJJ']))
      ->assertSessionHasErrors('state');
  });

  it('telefone com menos de 10 dígitos é rejeitado', function () {
    $owner = makeCompanyUser('owner');

    $this->actingAs($owner)
      ->post(route('company.save'), validCompanyData(['phone' => '123456789']))
      ->assertSessionHasErrors('phone');
  });

  it('atualiza empresa existente ao invés de criar nova', function () {
    $owner = makeCompanyUser('owner');
    Company::create(array_merge(validCompanyData(), ['id' => 1]));

    $this->actingAs($owner)
      ->post(route('company.save'), validCompanyData(['name' => 'Nome Atualizado']));

    $this->assertDatabaseCount('company', 1);
    $this->assertDatabaseHas('company', ['name' => 'Nome Atualizado']);
  });

  it('campos opcionais podem ser nulos', function () {
    $owner = makeCompanyUser('owner');

    $this->actingAs($owner)
      ->post(route('company.save'), [
        'name' => 'Barbearia Mínima',
      ])
      ->assertRedirect(route('company.index'));

    $this->assertDatabaseHas('company', ['name' => 'Barbearia Mínima']);
  });
});
