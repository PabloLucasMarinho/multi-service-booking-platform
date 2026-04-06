<?php

use App\Models\Company;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function makeSettingsUser(string $role): User
{
  $roleModel = Role::firstOrCreate(
    ['name' => $role],
    ['uuid' => str()->uuid()]
  );

  return User::factory()->create([
    'role_uuid'      => $roleModel->uuid,
    'date_of_birth'  => '1990-01-01',
    'phone'          => '21999999999',
    'zip_code'       => '12345678',
    'address'        => 'Rua Teste',
    'neighborhood'   => 'Centro',
    'city'           => 'Rio de Janeiro',
    'state'          => 'RJ',
    'admission_date' => '2025-01-01',
  ]);
}

// ============================================================
// SHOW
// ============================================================

describe('show', function () {
  it('owner pode ver as configurações', function () {
    $owner = makeSettingsUser('owner');

    $this->actingAs($owner)
      ->get(route('settings.index'))
      ->assertOk();
  });

  it('admin não pode ver as configurações', function () {
    $admin = makeSettingsUser('admin');

    $this->actingAs($admin)
      ->get(route('settings.index'))
      ->assertForbidden();
  });

  it('employee não pode ver as configurações', function () {
    $employee = makeSettingsUser('employee');

    $this->actingAs($employee)
      ->get(route('settings.index'))
      ->assertForbidden();
  });

  it('usuário não autenticado é redirecionado', function () {
    $this->get(route('settings.index'))
      ->assertRedirect(route('login'));
  });

  it('exibe configurações existentes da company', function () {
    $owner = makeSettingsUser('owner');
    Company::create([
      'name'                    => 'Empresa Teste',
      'rebooking_reminder_days' => 45,
      'max_discount_percentage' => 20,
    ]);

    $this->actingAs($owner)
      ->get(route('settings.index'))
      ->assertOk()
      ->assertSee('45')
      ->assertSee('20');
  });

  it('exibe formulário sem erros quando company não existe', function () {
    $owner = makeSettingsUser('owner');

    $this->assertDatabaseEmpty('company');

    $this->actingAs($owner)
      ->get(route('settings.index'))
      ->assertOk();
  });

  it('não exibe owners na lista de funcionários', function () {
    $owner = makeSettingsUser('owner');
    $employee = makeSettingsUser('employee');

    $response = $this->actingAs($owner)
      ->get(route('settings.index'))
      ->assertOk();

    // O UUID do employee deve aparecer como valor de checkbox
    $response->assertSee('value="' . $employee->uuid . '"', false);
    // O UUID do owner não deve aparecer como valor de checkbox
    $response->assertDontSee('value="' . $owner->uuid . '"', false);
  });
});

// ============================================================
// STORE
// ============================================================

describe('store', function () {
  it('owner pode salvar as configurações', function () {
    $owner = makeSettingsUser('owner');

    $this->actingAs($owner)
      ->post(route('settings.save'), [
        'rebooking_reminder_days' => 30,
        'max_discount_percentage' => 25,
      ])
      ->assertRedirect(route('settings.index'));

    $this->assertDatabaseHas('company', [
      'rebooking_reminder_days' => 30,
      'max_discount_percentage' => 25,
    ]);
  });

  it('admin não pode salvar as configurações', function () {
    $admin = makeSettingsUser('admin');

    $this->actingAs($admin)
      ->post(route('settings.save'), ['rebooking_reminder_days' => 30])
      ->assertForbidden();
  });

  it('employee não pode salvar as configurações', function () {
    $employee = makeSettingsUser('employee');

    $this->actingAs($employee)
      ->post(route('settings.save'), ['rebooking_reminder_days' => 30])
      ->assertForbidden();
  });

  it('campos opcionais podem ser omitidos', function () {
    $owner = makeSettingsUser('owner');

    $this->actingAs($owner)
      ->post(route('settings.save'), [])
      ->assertRedirect(route('settings.index'));
  });

  it('atualiza company existente sem criar nova', function () {
    $owner = makeSettingsUser('owner');
    Company::create(['name' => 'Empresa Existente', 'id' => 1]);

    $this->actingAs($owner)
      ->post(route('settings.save'), ['max_discount_percentage' => 20]);

    $this->assertDatabaseCount('company', 1);
    $this->assertDatabaseHas('company', ['max_discount_percentage' => 20]);
  });

  // — Validação: rebooking_reminder_days —

  it('lembrete de reagendamento acima de 365 é rejeitado', function () {
    $owner = makeSettingsUser('owner');

    $this->actingAs($owner)
      ->post(route('settings.save'), ['rebooking_reminder_days' => 366])
      ->assertSessionHasErrors('rebooking_reminder_days');
  });

  it('lembrete de reagendamento igual a 0 é rejeitado', function () {
    $owner = makeSettingsUser('owner');

    $this->actingAs($owner)
      ->post(route('settings.save'), ['rebooking_reminder_days' => 0])
      ->assertSessionHasErrors('rebooking_reminder_days');
  });

  it('lembrete de reagendamento não numérico é rejeitado', function () {
    $owner = makeSettingsUser('owner');

    $this->actingAs($owner)
      ->post(route('settings.save'), ['rebooking_reminder_days' => 'abc'])
      ->assertSessionHasErrors('rebooking_reminder_days');
  });

  // — Validação: max_discount_percentage —

  it('teto de desconto acima de 100 é rejeitado', function () {
    $owner = makeSettingsUser('owner');

    $this->actingAs($owner)
      ->post(route('settings.save'), ['max_discount_percentage' => 101])
      ->assertSessionHasErrors('max_discount_percentage');
  });

  it('teto de desconto igual a 0 é rejeitado', function () {
    $owner = makeSettingsUser('owner');

    $this->actingAs($owner)
      ->post(route('settings.save'), ['max_discount_percentage' => 0])
      ->assertSessionHasErrors('max_discount_percentage');
  });

  it('teto de desconto não numérico é rejeitado', function () {
    $owner = makeSettingsUser('owner');

    $this->actingAs($owner)
      ->post(route('settings.save'), ['max_discount_percentage' => 'abc'])
      ->assertSessionHasErrors('max_discount_percentage');
  });

  it('teto de desconto igual a 100 é válido', function () {
    $owner = makeSettingsUser('owner');

    $this->actingAs($owner)
      ->post(route('settings.save'), ['max_discount_percentage' => 100])
      ->assertRedirect(route('settings.index'));

    $this->assertDatabaseHas('company', ['max_discount_percentage' => 100]);
  });

  // — Autorização de desconto por usuário —

  it('marca usuários selecionados como autorizados a aplicar desconto', function () {
    $owner = makeSettingsUser('owner');
    $employee1 = makeSettingsUser('employee');
    $employee2 = makeSettingsUser('employee');

    $this->actingAs($owner)
      ->post(route('settings.save'), [
        'discount_users' => [$employee1->uuid],
      ])
      ->assertRedirect(route('settings.index'));

    expect($employee1->fresh()->can_apply_manual_discount)->toBeTrue();
    expect($employee2->fresh()->can_apply_manual_discount)->toBeFalse();
  });

  it('remove autorização de usuários não selecionados', function () {
    $owner = makeSettingsUser('owner');
    $employee = makeSettingsUser('employee');
    $employee->update(['can_apply_manual_discount' => true]);

    $this->actingAs($owner)
      ->post(route('settings.save'), [])
      ->assertRedirect(route('settings.index'));

    expect($employee->fresh()->can_apply_manual_discount)->toBeFalse();
  });

  it('selecionar todos os employees os autoriza', function () {
    $owner = makeSettingsUser('owner');
    $employee1 = makeSettingsUser('employee');
    $employee2 = makeSettingsUser('employee');

    $this->actingAs($owner)
      ->post(route('settings.save'), [
        'discount_users' => [$employee1->uuid, $employee2->uuid],
      ])
      ->assertRedirect(route('settings.index'));

    expect($employee1->fresh()->can_apply_manual_discount)->toBeTrue();
    expect($employee2->fresh()->can_apply_manual_discount)->toBeTrue();
  });

  it('valor inexistente em discount_users é rejeitado', function () {
    $owner = makeSettingsUser('owner');

    $this->actingAs($owner)
      ->post(route('settings.save'), [
        'discount_users' => [(string)str()->uuid()],
      ])
      ->assertSessionHasErrors('discount_users.*');
  });

  it('owner não é afetado pela atualização de discount_users', function () {
    $owner = makeSettingsUser('owner');
    $otherOwner = makeSettingsUser('owner');

    $this->actingAs($owner)
      ->post(route('settings.save'), [
        'discount_users' => [],
      ])
      ->assertRedirect(route('settings.index'));

    // owners não aparecem na listagem, então seu can_apply_manual_discount não é tocado
    expect($otherOwner->fresh()->can_apply_manual_discount)->toBeFalse();
  });
});
