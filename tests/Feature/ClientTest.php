<?php

use App\Models\Client;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
  // Cria roles se não existirem
  $adminRole = Role::firstOrCreate(
    ['name' => 'admin'],
    ['uuid' => str()->uuid()]
  );

  $employeeRole = Role::firstOrCreate(
    ['name' => 'employee'],
    ['uuid' => str()->uuid()]
  );

  // Cria permissions se não existirem
  $createClient = Permission::firstOrCreate(
    ['name' => 'create-client'],
    ['uuid' => str()->uuid()]
  );

  // Associa permissions às roles
  if (!$adminRole->permissions->contains($createClient)) {
    $adminRole->permissions()->attach($createClient);
  }

  if (!$employeeRole->permissions->contains($createClient)) {
    $employeeRole->permissions()->attach($createClient);
  }
});

test('admin can create a client', function () {
  $admin = User::factory()->create([
    'role_uuid' => Role::where('name', 'admin')->first()->uuid,
  ]);

  $this->actingAs($admin);

  $client = Client::factory()->make([
    'user_uuid' => $admin->uuid,
  ]);
  $response = $this->post('/clients', $client->toArray());

  $response->assertRedirect('/clients');
  $this->assertDatabaseHas('clients', [
    'user_uuid' => (string)$admin->uuid,
    'document' => $client->document,
  ]);
});

test('employee can create a client', function () {
  $employee = User::factory()->create([
    'role_uuid' => Role::where('name', 'employee')->first()->uuid,
  ]);

  $this->actingAs($employee);

  $client = Client::factory()->make([
    'user_uuid' => $employee->uuid,
  ]);
  $response = $this->post('/clients', $client->toArray());

  $response->assertRedirect('/clients');
  $this->assertDatabaseHas('clients', [
    'user_uuid' => (string)$employee->uuid,
    'document' => $client->document,
  ]);
});
