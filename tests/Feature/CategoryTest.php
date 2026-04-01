<?php

use App\Models\Category;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function makeCategoryUser(string $role): User
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

// ============================================================
// STORE
// ============================================================

describe('store', function () {
  it('cria categoria com sucesso', function () {
    $admin = makeCategoryUser('admin');

    $this->actingAs($admin)
      ->post(route('categories.store'), ['name' => 'Corte'])
      ->assertOk()
      ->assertJsonStructure(['uuid', 'slug', 'name']);

    $this->assertDatabaseHas('categories', ['name' => 'Corte', 'slug' => 'corte']);
  });

  it('nome é obrigatório', function () {
    $admin = makeCategoryUser('admin');

    $this->actingAs($admin)
      ->post(route('categories.store'), ['name' => ''])
      ->assertSessionHasErrors('name');
  });

  it('nome duplicado é rejeitado', function () {
    $admin = makeCategoryUser('admin');
    Category::create(['name' => 'Corte', 'slug' => 'corte']);

    $this->actingAs($admin)
      ->post(route('categories.store'), ['name' => 'Corte'])
      ->assertSessionHasErrors('name');
  });

  it('nome com mais de 50 caracteres é rejeitado', function () {
    $admin = makeCategoryUser('admin');

    $this->actingAs($admin)
      ->post(route('categories.store'), ['name' => str_repeat('a', 51)])
      ->assertSessionHasErrors('name');
  });

  it('slug é gerado automaticamente', function () {
    $admin = makeCategoryUser('admin');

    $this->actingAs($admin)
      ->post(route('categories.store'), ['name' => 'Corte Degradê']);

    $this->assertDatabaseHas('categories', ['slug' => 'corte-degrade']);
  });

  it('acentos são removidos do nome', function () {
    $admin = makeCategoryUser('admin');

    $this->actingAs($admin)
      ->post(route('categories.store'), ['name' => 'Coloração']);

    $this->assertDatabaseHas('categories', ['name' => 'Coloracao']);
  });

  it('usuário não autenticado é redirecionado', function () {
    $this->post(route('categories.store'), ['name' => 'Corte'])
      ->assertRedirect(route('login'));
  });
});

// ============================================================
// DESTROY
// ============================================================

describe('destroy', function () {
  it('deleta categoria com sucesso', function () {
    $admin = makeCategoryUser('admin');
    $category = Category::create(['name' => 'Corte', 'slug' => 'corte']);

    $this->actingAs($admin)
      ->delete(route('categories.destroy', $category))
      ->assertOk()
      ->assertJson(['success' => true]);

    $this->assertDatabaseMissing('categories', ['uuid' => $category->uuid]);
  });

  it('deletar categoria inexistente retorna 404', function () {
    $admin = makeCategoryUser('admin');

    $this->actingAs($admin)
      ->delete(route('categories.destroy', 'slug-inexistente'))
      ->assertNotFound();
  });

  it('usuário não autenticado é redirecionado', function () {
    $category = Category::create(['name' => 'Corte', 'slug' => 'corte']);

    $this->delete(route('categories.destroy', $category))
      ->assertRedirect(route('login'));
  });
});
