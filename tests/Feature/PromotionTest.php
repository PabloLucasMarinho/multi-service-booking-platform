<?php

use App\Enums\DiscountType;
use App\Models\Category;
use App\Models\Promotion;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function makePromotionUser(string $role): User
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

function makePromotion(array $overrides = []): Promotion
{
  return Promotion::create(array_merge([
    'name' => 'Promoção Teste',
    'type' => DiscountType::Percentage,
    'value' => 10,
    'starts_at' => now()->startOfMonth(),
    'ends_at' => now()->endOfMonth(),
  ], $overrides));
}

function validPromotionData(array $overrides = []): array
{
  return array_merge([
    'name' => 'Promoção Teste',
    'type' => 'percentage',
    'value' => '10,00',
    'starts_at' => now()->format('d/m/Y'),
    'ends_at' => now()->addMonth()->format('d/m/Y'),
  ], $overrides);
}

// ============================================================
// INDEX
// ============================================================

describe('index', function () {
  it('owner pode listar promoções', function () {
    $owner = makePromotionUser('owner');

    $this->actingAs($owner)
      ->get(route('promotions.index'))
      ->assertOk();
  });

  it('admin não pode listar promoções', function () {
    $admin = makePromotionUser('admin');

    $this->actingAs($admin)
      ->get(route('promotions.index'))
      ->assertForbidden();
  });

  it('employee não pode listar promoções', function () {
    $employee = makePromotionUser('employee');

    $this->actingAs($employee)
      ->get(route('promotions.index'))
      ->assertForbidden();
  });

  it('usuário não autenticado é redirecionado', function () {
    $this->get(route('promotions.index'))
      ->assertRedirect(route('login'));
  });
});

// ============================================================
// CREATE
// ============================================================

describe('create', function () {
  it('owner pode acessar formulário de cadastro', function () {
    $owner = makePromotionUser('owner');

    $this->actingAs($owner)
      ->get(route('promotions.create'))
      ->assertOk();
  });

  it('admin não pode acessar formulário de cadastro', function () {
    $admin = makePromotionUser('admin');

    $this->actingAs($admin)
      ->get(route('promotions.create'))
      ->assertForbidden();
  });

  it('employee não pode acessar formulário de cadastro', function () {
    $employee = makePromotionUser('employee');

    $this->actingAs($employee)
      ->get(route('promotions.create'))
      ->assertForbidden();
  });
});

// ============================================================
// STORE
// ============================================================

describe('store', function () {
  it('owner pode cadastrar promoção', function () {
    $owner = makePromotionUser('owner');

    $this->actingAs($owner)
      ->post(route('promotions.store'), validPromotionData())
      ->assertRedirect(route('promotions.index'));

    $this->assertDatabaseHas('promotions', ['name' => 'Promoção Teste']);
  });

  it('admin não pode cadastrar promoção', function () {
    $admin = makePromotionUser('admin');

    $this->actingAs($admin)
      ->post(route('promotions.store'), validPromotionData())
      ->assertForbidden();
  });

  it('employee não pode cadastrar promoção', function () {
    $employee = makePromotionUser('employee');

    $this->actingAs($employee)
      ->post(route('promotions.store'), validPromotionData())
      ->assertForbidden();
  });

  it('nome é obrigatório', function () {
    $owner = makePromotionUser('owner');

    $this->actingAs($owner)
      ->post(route('promotions.store'), validPromotionData(['name' => '']))
      ->assertSessionHasErrors('name');
  });

  it('tipo é obrigatório', function () {
    $owner = makePromotionUser('owner');

    $this->actingAs($owner)
      ->post(route('promotions.store'), validPromotionData(['type' => '']))
      ->assertSessionHasErrors('type');
  });

  it('tipo inválido é rejeitado', function () {
    $owner = makePromotionUser('owner');

    $this->actingAs($owner)
      ->post(route('promotions.store'), validPromotionData(['type' => 'invalido']))
      ->assertSessionHasErrors('type');
  });

  it('valor é obrigatório', function () {
    $owner = makePromotionUser('owner');

    $this->actingAs($owner)
      ->post(route('promotions.store'), validPromotionData(['value' => '']))
      ->assertSessionHasErrors('value');
  });

  it('porcentagem acima de 100 é rejeitada', function () {
    $owner = makePromotionUser('owner');

    $this->actingAs($owner)
      ->post(route('promotions.store'), validPromotionData([
        'type' => 'percentage',
        'value' => '101',
      ]))
      ->assertSessionHasErrors('value');
  });

  it('porcentagem de 100 é aceita', function () {
    $owner = makePromotionUser('owner');

    $this->actingAs($owner)
      ->post(route('promotions.store'), validPromotionData([
        'type' => 'percentage',
        'value' => '100',
      ]))
      ->assertRedirect(route('promotions.index'));
  });

  it('data inicial é obrigatória', function () {
    $owner = makePromotionUser('owner');

    $this->actingAs($owner)
      ->post(route('promotions.store'), validPromotionData(['starts_at' => '']))
      ->assertSessionHasErrors('starts_at');
  });

  it('data final é obrigatória', function () {
    $owner = makePromotionUser('owner');

    $this->actingAs($owner)
      ->post(route('promotions.store'), validPromotionData(['ends_at' => '']))
      ->assertSessionHasErrors('ends_at');
  });

  it('data final anterior à inicial é rejeitada', function () {
    $owner = makePromotionUser('owner');

    $this->actingAs($owner)
      ->post(route('promotions.store'), validPromotionData([
        'starts_at' => now()->format('d/m/Y'),
        'ends_at' => now()->subDay()->format('d/m/Y'),
      ]))
      ->assertSessionHasErrors('ends_at');
  });

  it('cadastro com categorias associa corretamente', function () {
    $owner = makePromotionUser('owner');

    $this->actingAs($owner)
      ->post(route('promotions.store'), validPromotionData([
        'categories' => ['Corte', 'Barba'],
      ]))
      ->assertRedirect(route('promotions.index'));

    $promotion = Promotion::where('name', 'Promoção Teste')->first();
    expect($promotion->categories)->toHaveCount(2);
  });
});

// ============================================================
// EDIT
// ============================================================

describe('edit', function () {
  it('owner pode acessar formulário de edição', function () {
    $owner = makePromotionUser('owner');
    $promotion = makePromotion();

    $this->actingAs($owner)
      ->get(route('promotions.edit', $promotion))
      ->assertOk();
  });

  it('admin não pode acessar formulário de edição', function () {
    $admin = makePromotionUser('admin');
    $promotion = makePromotion();

    $this->actingAs($admin)
      ->get(route('promotions.edit', $promotion))
      ->assertForbidden();
  });

  it('employee não pode acessar formulário de edição', function () {
    $employee = makePromotionUser('employee');
    $promotion = makePromotion();

    $this->actingAs($employee)
      ->get(route('promotions.edit', $promotion))
      ->assertForbidden();
  });
});

// ============================================================
// UPDATE
// ============================================================

describe('update', function () {
  it('owner pode atualizar promoção', function () {
    $owner = makePromotionUser('owner');
    $promotion = makePromotion();

    $this->actingAs($owner)
      ->put(route('promotions.update', $promotion), validPromotionData(['name' => 'Promoção Atualizada']))
      ->assertRedirect(route('promotions.index'));

    $this->assertDatabaseHas('promotions', ['uuid' => $promotion->uuid, 'name' => 'Promoção Atualizada']);
  });

  it('admin não pode atualizar promoção', function () {
    $admin = makePromotionUser('admin');
    $promotion = makePromotion();

    $this->actingAs($admin)
      ->put(route('promotions.update', $promotion), validPromotionData())
      ->assertForbidden();
  });

  it('employee não pode atualizar promoção', function () {
    $employee = makePromotionUser('employee');
    $promotion = makePromotion();

    $this->actingAs($employee)
      ->put(route('promotions.update', $promotion), validPromotionData())
      ->assertForbidden();
  });

  it('atualizar com categorias faz sync', function () {
    $owner = makePromotionUser('owner');
    $promotion = makePromotion();

    $promotion->categories()->attach(
      Category::firstOrCreate(['slug' => 'corte'], ['name' => 'Corte'])->uuid
    );

    $this->actingAs($owner)
      ->put(route('promotions.update', $promotion), validPromotionData([
        'categories' => ['Barba'],
      ]));

    $promotion->refresh();
    expect($promotion->categories)->toHaveCount(1);
    expect($promotion->categories->first()->name)->toBe('Barba');
  });

  it('atualizar sem categorias remove todas', function () {
    $owner = makePromotionUser('owner');
    $promotion = makePromotion();

    $promotion->categories()->attach(
      Category::firstOrCreate(['slug' => 'corte'], ['name' => 'Corte'])->uuid
    );

    $this->actingAs($owner)
      ->put(route('promotions.update', $promotion), validPromotionData());

    $promotion->refresh();
    expect($promotion->categories)->toHaveCount(0);
  });
});

// ============================================================
// DESTROY
// ============================================================

describe('destroy', function () {
  it('owner pode deletar promoção', function () {
    $owner = makePromotionUser('owner');
    $promotion = makePromotion();

    $this->actingAs($owner)
      ->delete(route('promotions.destroy', $promotion))
      ->assertRedirect(route('promotions.index'));

    $this->assertSoftDeleted('promotions', ['uuid' => $promotion->uuid]);
  });

  it('admin não pode deletar promoção', function () {
    $admin = makePromotionUser('admin');
    $promotion = makePromotion();

    $this->actingAs($admin)
      ->delete(route('promotions.destroy', $promotion))
      ->assertForbidden();
  });

  it('employee não pode deletar promoção', function () {
    $employee = makePromotionUser('employee');
    $promotion = makePromotion();

    $this->actingAs($employee)
      ->delete(route('promotions.destroy', $promotion))
      ->assertForbidden();
  });
});
