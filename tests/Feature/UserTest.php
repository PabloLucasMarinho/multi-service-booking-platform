<?php

use App\Models\Role;
use App\Models\User;
use App\Models\UserDetail;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
  $adminRole = Role::firstOrCreate(
    ['name' => 'admin'],
    ['uuid' => str()->uuid()]
  );

  Role::firstOrCreate(
    ['name' => 'employee'],
    ['uuid' => str()->uuid()]
  );

  $this->admin = User::factory()->create(['role_uuid' => $adminRole->uuid]);
  $this->actingAs($this->admin);
});

it('anonimiza um usuário soft deleted', function () {
  $user = User::factory()
    ->has(UserDetail::factory(), 'details')
    ->create();

  $user->delete();

  $response = $this->deleteJson(route('users.anonymize', $user->uuid));

  $response->assertJson(['success' => true]);

  expect($user->fresh()->email)->toBeNull();
  expect($user->details()->withTrashed()->first()->document)->toBeNull();
});

it('retorna 404 ao tentar anonimizar usuário não deletado', function () {
  $user = User::factory()
    ->has(UserDetail::factory(), 'details')
    ->create();

  $this->deleteJson(route('users.anonymize', $user->uuid))
    ->assertNotFound();
});

it('abre modal quando email já pertence a usuário soft deleted', function () {
  $user = User::factory()
    ->has(UserDetail::factory(), 'details')
    ->create();

  $user->delete();

  $response = $this->postJson(route('users.store'), [
    'name' => 'Novo Funcionário',
    'email' => $user->email,
    'document' => generateUniqueCpf(),
    'date_of_birth' => '1990-01-01',
    'phone' => '21991234567',
    'address' => 'Rua Teste, 123',
    'zip_code' => '21725180',
    'neighborhood' => 'Bairro Teste',
    'city' => 'Rio de Janeiro',
    'admission_date' => now()->format('Y-m-d'),
    'role' => 'employee',
  ]);

  $response->assertJson(['deleted_user' => true, 'uuid' => $user->uuid]);
});

it('abre modal quando document já pertence a usuário soft deleted', function () {
  $user = User::factory()
    ->has(UserDetail::factory(), 'details')
    ->create();

  $user->delete();

  $response = $this->postJson(route('users.store'), [
    'name' => 'Novo Funcionário',
    'email' => fake()->unique()->safeEmail(),
    'document' => $user->details()->withTrashed()->first()->document,
    'date_of_birth' => '1990-01-01',
    'phone' => '21991234567',
    'address' => 'Rua Teste, 123',
    'zip_code' => '21725180',
    'neighborhood' => 'Bairro Teste',
    'city' => 'Rio de Janeiro',
    'admission_date' => now()->format('Y-m-d'),
    'role' => 'employee',
  ]);

  $response->assertJson(['deleted_user' => true, 'uuid' => $user->uuid]);
});

it('abre modal quando email e document já pertencem a usuário soft deleted', function () {
  $user = User::factory()
    ->has(UserDetail::factory(), 'details')
    ->create();

  $user->delete();

  $response = $this->postJson(route('users.store'), [
    'name' => 'Novo Funcionário',
    'email' => $user->email,
    'document' => $user->details()->withTrashed()->first()->document,
    'date_of_birth' => '1990-01-01',
    'phone' => '21991234567',
    'address' => 'Rua Teste, 123',
    'zip_code' => '21725180',
    'neighborhood' => 'Bairro Teste',
    'city' => 'Rio de Janeiro',
    'admission_date' => now()->format('Y-m-d'),
    'role' => 'employee',
  ]);

  $response->assertJson(['deleted_user' => true, 'uuid' => $user->uuid]);
});

it('anonimiza e cadastra novo usuário quando email já pertence a usuário soft deleted', function () {
  $user = User::factory()
    ->has(UserDetail::factory(), 'details')
    ->create();

  $user->delete();

  $this->deleteJson(route('users.anonymize', $user->uuid))
    ->assertJson(['success' => true]);

  expect($user->fresh()->email)->toBeNull();
  expect($user->details()->withTrashed()->first()->document)->toBeNull();

  $newDocument = generateUniqueCpf();

  $this->postJson(route('users.store'), [
    'name' => 'Novo Funcionário',
    'email' => fake()->unique()->safeEmail(),
    'document' => $newDocument,
    'date_of_birth' => '1990-01-01',
    'phone' => '21991234567',
    'address' => 'Rua Teste, 123',
    'zip_code' => '21725180',
    'neighborhood' => 'Bairro Teste',
    'city' => 'Rio de Janeiro',
    'salary' => null,
    'admission_date' => now()->format('Y-m-d'),
    'role' => 'employee',
  ])->assertRedirect(route('users.index'));
});

it('anonimiza e cadastra novo usuário quando document já pertence a usuário soft deleted', function () {
  $user = User::factory()
    ->has(UserDetail::factory(), 'details')
    ->create();

  $originalDocument = $user->details->document;

  $user->delete();

  $this->deleteJson(route('users.anonymize', $user->uuid))
    ->assertJson(['success' => true]);

  expect($user->details()->withTrashed()->first()->document)->toBeNull();

  $this->postJson(route('users.store'), [
    'name' => 'Novo Funcionário',
    'email' => fake()->unique()->safeEmail(),
    'document' => $originalDocument,
    'date_of_birth' => '1990-01-01',
    'phone' => '21991234567',
    'address' => 'Rua Teste, 123',
    'zip_code' => '21725180',
    'neighborhood' => 'Bairro Teste',
    'city' => 'Rio de Janeiro',
    'salary' => null,
    'admission_date' => now()->format('Y-m-d'),
    'role' => 'employee',
  ])->assertRedirect(route('users.index'));
});

it('anonimiza e cadastra novo usuário quando email e document já pertencem a usuário soft deleted', function () {
  $user = User::factory()
    ->has(UserDetail::factory(), 'details')
    ->create();

  $originalEmail = $user->email;
  $originalDocument = $user->details->document;

  $user->delete();

  $this->deleteJson(route('users.anonymize', $user->uuid))
    ->assertJson(['success' => true]);

  expect($user->fresh()->email)->toBeNull();
  expect($user->details()->withTrashed()->first()->document)->toBeNull();

  $this->postJson(route('users.store'), [
    'name' => 'Novo Funcionário',
    'email' => $originalEmail,
    'document' => $originalDocument,
    'date_of_birth' => '1990-01-01',
    'phone' => '21991234567',
    'address' => 'Rua Teste, 123',
    'zip_code' => '21725180',
    'neighborhood' => 'Bairro Teste',
    'city' => 'Rio de Janeiro',
    'salary' => null,
    'admission_date' => now()->format('Y-m-d'),
    'role' => 'employee',
  ])->assertRedirect(route('users.index'));
});

it('restaura um usuário soft deleted', function () {
  $user = User::factory()
    ->has(UserDetail::factory(), 'details')
    ->create();

  $user->delete();

  $this->putJson(route('users.restore', $user->uuid))
    ->assertJson(['success' => true]);

  expect($user->fresh()->deleted_at)->toBeNull();
  expect($user->details()->first()->deleted_at)->toBeNull();
});

it('retorna 404 ao tentar restaurar usuário não deletado', function () {
  $user = User::factory()
    ->has(UserDetail::factory(), 'details')
    ->create();

  $this->putJson(route('users.restore', $user->uuid))
    ->assertNotFound();
});

it('retorna 404 ao tentar restaurar usuário inexistente', function () {
  $this->putJson(route('users.restore', str()->uuid()))
    ->assertNotFound();
});
