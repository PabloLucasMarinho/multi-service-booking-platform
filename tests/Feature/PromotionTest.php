<?php

use App\Enums\AppointmentStatus;
use App\Enums\DiscountType;
use App\Models\Appointment;
use App\Models\AppointmentService;
use App\Models\Category;
use App\Models\Client;
use App\Models\Promotion;
use App\Models\Role;
use App\Models\Service;
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

// ============================================================
// APLICAÇÃO POR PERÍODO (data do agendamento)
// ============================================================

describe('aplicação por período', function () {
  it('promoção vigente na data do agendamento é aplicada ao serviço', function () {
    $owner  = makePromotionUser('owner');
    $client = Client::factory()->create(['user_uuid' => $owner->uuid]);

    // Agendamento para daqui a 5 dias; promoção cobre esse período
    $scheduledAt = now()->addDays(5);
    $appointment = Appointment::factory()->create([
      'user_uuid'    => $owner->uuid,
      'client_uuid'  => $client->uuid,
      'scheduled_at' => $scheduledAt,
      'status'       => AppointmentStatus::Scheduled,
    ]);

    makePromotion([
      'value'     => 20,
      'starts_at' => now()->subDay(),
      'ends_at'   => now()->addDays(10),
    ]);

    $service = Service::factory()->create(['price' => 100]);

    $this->actingAs($owner)
      ->post(route('appointment-services.store', $appointment), [
        'service_uuid' => (string) $service->uuid,
      ]);

    $item = AppointmentService::where('appointment_uuid', $appointment->uuid)->first();
    expect($item->promotion_uuid)->not->toBeNull();
    expect((float) $item->final_price)->toBe(80.0);
  });

  it('promoção encerrada antes da data do agendamento não é aplicada', function () {
    $owner  = makePromotionUser('owner');
    $client = Client::factory()->create(['user_uuid' => $owner->uuid]);

    // Promoção termina hoje; agendamento é amanhã
    makePromotion([
      'starts_at' => now()->subDay(),
      'ends_at'   => now()->endOfDay(),
    ]);

    $appointment = Appointment::factory()->create([
      'user_uuid'    => $owner->uuid,
      'client_uuid'  => $client->uuid,
      'scheduled_at' => now()->addDay(),
      'status'       => AppointmentStatus::Scheduled,
    ]);

    $service = Service::factory()->create(['price' => 100]);

    $this->actingAs($owner)
      ->post(route('appointment-services.store', $appointment), [
        'service_uuid' => (string) $service->uuid,
      ]);

    $item = AppointmentService::where('appointment_uuid', $appointment->uuid)->first();
    expect($item->promotion_uuid)->toBeNull();
    expect((float) $item->final_price)->toBe(100.0);
  });

  it('promoção futura é aplicada quando agendamento cai dentro do período dela', function () {
    $owner  = makePromotionUser('owner');
    $client = Client::factory()->create(['user_uuid' => $owner->uuid]);

    // Promoção começa daqui a 7 dias (inativa hoje); agendamento é em 10 dias
    makePromotion([
      'value'     => 10,
      'starts_at' => now()->addDays(7),
      'ends_at'   => now()->addDays(14),
    ]);

    $appointment = Appointment::factory()->create([
      'user_uuid'    => $owner->uuid,
      'client_uuid'  => $client->uuid,
      'scheduled_at' => now()->addDays(10),
      'status'       => AppointmentStatus::Scheduled,
    ]);

    $service = Service::factory()->create(['price' => 100]);

    $this->actingAs($owner)
      ->post(route('appointment-services.store', $appointment), [
        'service_uuid' => (string) $service->uuid,
      ]);

    $item = AppointmentService::where('appointment_uuid', $appointment->uuid)->first();
    expect($item->promotion_uuid)->not->toBeNull();
    expect((float) $item->final_price)->toBe(90.0);
  });

  it('promoção futura não é aplicada quando agendamento é antes do início dela', function () {
    $owner  = makePromotionUser('owner');
    $client = Client::factory()->create(['user_uuid' => $owner->uuid]);

    // Promoção começa em 10 dias; agendamento é em 3 dias
    makePromotion([
      'starts_at' => now()->addDays(10),
      'ends_at'   => now()->addDays(20),
    ]);

    $appointment = Appointment::factory()->create([
      'user_uuid'    => $owner->uuid,
      'client_uuid'  => $client->uuid,
      'scheduled_at' => now()->addDays(3),
      'status'       => AppointmentStatus::Scheduled,
    ]);

    $service = Service::factory()->create(['price' => 100]);

    $this->actingAs($owner)
      ->post(route('appointment-services.store', $appointment), [
        'service_uuid' => (string) $service->uuid,
      ]);

    $item = AppointmentService::where('appointment_uuid', $appointment->uuid)->first();
    expect($item->promotion_uuid)->toBeNull();
    expect((float) $item->final_price)->toBe(100.0);
  });
});

// ============================================================
// DELEÇÃO EM CASCATA
// ============================================================

describe('deleção em cascata', function () {
  it('deletar promoção remove-a de agendamentos com status scheduled', function () {
    $owner     = makePromotionUser('owner');
    $promotion = makePromotion(['value' => 20]);
    $client    = Client::factory()->create(['user_uuid' => $owner->uuid]);
    $service   = Service::factory()->create(['price' => 100]);

    $appointment = Appointment::factory()->create([
      'user_uuid'    => $owner->uuid,
      'client_uuid'  => $client->uuid,
      'scheduled_at' => now()->addDay(),
      'status'       => AppointmentStatus::Scheduled,
    ]);

    $item = new AppointmentService([
      'appointment_uuid'        => $appointment->uuid,
      'service_uuid'            => $service->uuid,
      'original_price'          => 100,
      'promotion_uuid'          => $promotion->uuid,
      'promotion_amount_snapshot' => 20,
      'manual_discount_type'    => null,
      'manual_discount_value'   => null,
    ]);
    $item->applyDiscount(null);
    $item->save();

    expect((float) $item->final_price)->toBe(80.0);

    $this->actingAs($owner)
      ->delete(route('promotions.destroy', $promotion));

    $item->refresh();
    expect($item->promotion_uuid)->toBeNull();
    expect($item->promotion_amount_snapshot)->toBeNull();
  });

  it('deletar promoção recalcula o preço do serviço sem o desconto', function () {
    $owner     = makePromotionUser('owner');
    $promotion = makePromotion(['value' => 30]);
    $client    = Client::factory()->create(['user_uuid' => $owner->uuid]);
    $service   = Service::factory()->create(['price' => 100]);

    $appointment = Appointment::factory()->create([
      'user_uuid'    => $owner->uuid,
      'client_uuid'  => $client->uuid,
      'scheduled_at' => now()->addDay(),
      'status'       => AppointmentStatus::Scheduled,
    ]);

    $item = new AppointmentService([
      'appointment_uuid'          => $appointment->uuid,
      'service_uuid'              => $service->uuid,
      'original_price'            => 100,
      'promotion_uuid'            => $promotion->uuid,
      'promotion_amount_snapshot' => 30,
      'manual_discount_type'      => null,
      'manual_discount_value'     => null,
    ]);
    $item->applyDiscount(null);
    $item->save();

    $this->actingAs($owner)
      ->delete(route('promotions.destroy', $promotion));

    $item->refresh();
    expect((float) $item->final_price)->toBe(100.0);
  });

  it('deletar promoção não afeta agendamentos já concluídos', function () {
    $owner     = makePromotionUser('owner');
    $promotion = makePromotion(['value' => 25]);
    $client    = Client::factory()->create(['user_uuid' => $owner->uuid]);
    $service   = Service::factory()->create(['price' => 100]);

    $appointment = Appointment::factory()->create([
      'user_uuid'    => $owner->uuid,
      'client_uuid'  => $client->uuid,
      'scheduled_at' => now()->subDay(),
      'status'       => AppointmentStatus::Completed,
    ]);

    $item = new AppointmentService([
      'appointment_uuid'          => $appointment->uuid,
      'service_uuid'              => $service->uuid,
      'original_price'            => 100,
      'promotion_uuid'            => $promotion->uuid,
      'promotion_amount_snapshot' => 25,
      'manual_discount_type'      => null,
      'manual_discount_value'     => null,
    ]);
    $item->applyDiscount(null);
    $item->save();

    $this->actingAs($owner)
      ->delete(route('promotions.destroy', $promotion));

    $item->refresh();
    expect($item->promotion_uuid)->toBe($promotion->uuid);
    expect((float) $item->promotion_amount_snapshot)->toBe(25.0);
    expect((float) $item->final_price)->toBe(75.0);
  });
});
