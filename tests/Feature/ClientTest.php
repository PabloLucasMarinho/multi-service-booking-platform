<?php

use App\Models\Client;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Rules\DateOfBirth;
use App\Rules\Document;
use App\Rules\Phone;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
  $this->adminRole = Role::firstOrCreate(
    ['name' => 'admin'],
    ['uuid' => str()->uuid()]
  );

  $this->employeeRole = Role::firstOrCreate(
    ['name' => 'employee'],
    ['uuid' => str()->uuid()]
  );

  $this->createClientPermission = Permission::firstOrCreate(
    ['name' => 'create-client'],
    ['uuid' => str()->uuid()]
  );

  $this->adminRole->permissions()
    ->syncWithoutDetaching([(string)$this->createClientPermission->uuid]);

  $this->employeeRole->permissions()
    ->syncWithoutDetaching([(string)$this->createClientPermission->uuid]);
});

function actingAsUserWithRole($role)
{
  $user = User::factory()->create([
    'role_uuid' => $role->uuid,
  ]);

  test()->actingAs($user);

  return $user;
}

test('client is stored with correct data', function () {
  $admin = actingAsUserWithRole($this->adminRole);

  $client = Client::factory()->make([
    'user_uuid' => $admin->uuid,
  ]);

  $this->post('/clients', $client->toArray());

  $this->assertDatabaseHas('clients', [
    'name' => $client->name,
    'document' => $client->document,
    'date_of_birth' => $client->date_of_birth,
    'email' => $client->email,
    'phone' => $client->phone,
    'user_uuid' => (string)$admin->uuid,
  ]);
});

test('client is linked to authenticated user', function () {
  $admin = actingAsUserWithRole($this->adminRole);

  $client = Client::factory()->make([
    'user_uuid' => $admin->uuid,
  ]);

  $this->post('/clients', $client->toArray());

  $this->assertDatabaseHas('clients', [
    'user_uuid' => (string)$admin->uuid,
  ]);
});

test('cannot assign client to another user', function () {
  $admin = actingAsUserWithRole($this->adminRole);

  $otherUser = User::factory()->create();

  $client = Client::factory()->make([
    'user_uuid' => $otherUser->uuid,
  ]);

  $this->post('/clients', $client->toArray());

  $this->assertDatabaseHas('clients', [
    'user_uuid' => (string)$admin->uuid,
  ]);
});

test('admin can create a client', function () {
  $admin = actingAsUserWithRole($this->adminRole);

  $client = Client::factory()->make([
    'user_uuid' => $admin->uuid,
  ]);

  $response = $this->post('/clients', $client->toArray());

  $response->assertRedirect('/clients');
  $response->assertSessionHas('success');

  $this->assertDatabaseHas('clients', [
    'user_uuid' => (string)$admin->uuid,
    'document' => $client->document,
  ]);
});

test('employee can create a client', function () {
  $employee = actingAsUserWithRole($this->employeeRole);

  $client = Client::factory()->make([
    'user_uuid' => $employee->uuid,
  ]);

  $response = $this->post('/clients', $client->toArray());

  $response->assertRedirect('/clients');
  $response->assertSessionHas('success');

  $this->assertDatabaseHas('clients', [
    'user_uuid' => (string)$employee->uuid,
    'document' => $client->document,
  ]);
});

test('guest cannot create a client', function () {
  $client = Client::factory()->make();

  $response = $this->post('/clients', $client->toArray());

  $response->assertRedirect('/login');
});

test('user without permission cannot create client', function () {
  $role = Role::factory()->create(['name' => 'viewer']);

  $user = User::factory()->create([
    'role_uuid' => $role->uuid,
  ]);

  $this->actingAs($user);

  $client = Client::factory()->make([
    'user_uuid' => $user->uuid,
  ]);

  $response = $this->post('/clients', $client->toArray());

  $response->assertForbidden();
});

test('cannot create client without required fields', function () {
  actingAsUserWithRole($this->adminRole);

  $response = $this->post('/clients', []);

  $response->assertSessionHasErrors([
    'name',
    'document',
    'date_of_birth',
  ]);
});

test('cannot create client with future date of birth', function () {
  $admin = actingAsUserWithRole($this->adminRole);

  $client = Client::factory()->make([
    'date_of_birth' => now()->addDay()->format('Y-m-d'),
    'user_uuid' => $admin->uuid,
  ]);

  $response = $this->post('/clients', $client->toArray());

  $response->assertSessionHasErrors(['date_of_birth' => 'A data de nascimento não pode ser futura.']);
});

test('cannot create client with invalid date of birth', function () {
  $admin = actingAsUserWithRole($this->adminRole);

  $client = Client::factory()->make([
    'date_of_birth' => 'data-invalida',
    'user_uuid' => $admin->uuid,
  ]);

  $response = $this->post('/clients', $client->toArray());

  $response->assertSessionHasErrors(['date_of_birth' => 'A data de nascimento é inválida.']);
});

test('cannot create client without contact', function () {
  $admin = actingAsUserWithRole($this->adminRole);

  $client = Client::factory()->make([
    'email' => null,
    'phone' => null,
    'user_uuid' => $admin->uuid,
  ]);

  $response = $this->post('/clients', $client->toArray());

  $response->assertSessionHasErrors(['email', 'phone']);
});

test('cannot create client with invalid document', function () {
  $admin = actingAsUserWithRole($this->adminRole);

  $client = Client::factory()->make([
    'document' => '12345678900',
    'user_uuid' => $admin->uuid,
  ]);

  $response = $this->post('/clients', $client->toArray());

  $response->assertSessionHasErrors([
    'document' => 'O CPF informado é inválido.',
  ]);
});

test('cannot create client with duplicated document', function () {
  $admin = actingAsUserWithRole($this->adminRole);

  $existing = Client::factory()->create([
    'user_uuid' => $admin->uuid,
  ]);

  $client = Client::factory()->make([
    'document' => $existing->document,
    'user_uuid' => $admin->uuid,
  ]);

  $response = $this->post('/clients', $client->toArray());

  $response->assertSessionHasErrors(['document']);
});

test('cannot create client with duplicated email', function () {
  $admin = actingAsUserWithRole($this->adminRole);

  $existing = Client::factory()->create([
    'user_uuid' => $admin->uuid,
  ]);

  $client = Client::factory()->make([
    'email' => $existing->email,
    'user_uuid' => $admin->uuid,
  ]);

  $response = $this->post('/clients', $client->toArray());

  $response->assertSessionHasErrors(['email']);
});

test('cannot create client with invalid email', function () {
  $admin = actingAsUserWithRole($this->adminRole);

  $client = Client::factory()->make([
    'email' => 'email-invalido',
    'user_uuid' => $admin->uuid,
  ]);

  $response = $this->post('/clients', $client->toArray());

  $response->assertSessionHasErrors(['email']);
});

test('client name is formatted before saving', function () {
  $admin = actingAsUserWithRole($this->adminRole);

  $client = Client::factory()->make([
    'name' => '   joao    da    silva    ',
    'user_uuid' => $admin->uuid,
  ]);

  $this->post('/clients', $client->toArray());

  $this->assertDatabaseHas('clients', [
    'name' => 'Joao da Silva',
  ]);
});

test('client email is formatted before saving', function () {
  $admin = actingAsUserWithRole($this->adminRole);

  $client = Client::factory()->make([
    'email' => '   JOAO@EMAIL.COM    ',
    'user_uuid' => $admin->uuid,
  ]);

  $this->post('/clients', $client->toArray());

  $this->assertDatabaseHas('clients', [
    'email' => 'joao@email.com',
  ]);
});

test('client document is formatted before saving', function () {
  $admin = actingAsUserWithRole($this->adminRole);

  $client = Client::factory()->make([
    'document' => '    928.066.570-75    ',
    'user_uuid' => $admin->uuid,
  ]);

  $this->post('/clients', $client->toArray());

  $this->assertDatabaseHas('clients', [
    'document' => '92806657075',
  ]);
});

test('client phone is formatted before saving', function () {
  $admin = actingAsUserWithRole($this->adminRole);

  $client = Client::factory()->make([
    'phone' => '    (21) 91234-5678    ',
    'user_uuid' => $admin->uuid,
  ]);

  $this->post('/clients', $client->toArray());

  $this->assertDatabaseHas('clients', [
    'phone' => '21912345678',
  ]);
});

test('date of birth rule fails for future date', function () {
  $rule = new DateOfBirth();

  $failCalled = false;

  $rule->validate('date_of_birth', now()->addDay()->format('Y-m-d'), function () use (&$failCalled) {
    $failCalled = true;
  });

  expect($failCalled)->toBeTrue();
});

test('phone rule fails for missing digits', function () {
  $rule = new Phone();

  $failCalled = false;

  $rule->validate('phone', '(21) 123-4567', function () use (&$failCalled) {
    $failCalled = true;
  });

  expect($failCalled)->toBeTrue();
});

test('phone rule fails for invalid ninth digit', function () {
  $rule = new Phone();

  $failCalled = false;

  $rule->validate('phone', '(21) 81234-5678', function () use (&$failCalled) {
    $failCalled = true;
  });

  expect($failCalled)->toBeTrue();
});

test('phone rule fails for invalid area code', function () {
  $rule = new Phone();

  $failCalled = false;

  $rule->validate('phone', '(100) 91234-5678', function () use (&$failCalled) {
    $failCalled = true;
  });

  expect($failCalled)->toBeTrue();
});

test('null phone is not validated', function () {
  $rule = new Phone();

  $failCalled = false;

  $rule->validate('phone', '', function () use (&$failCalled) {
    $failCalled = true;
  });

  expect($failCalled)->toBeFalse();
});

test('phone rule passes for valid phone', function () {
  $rule = new Phone();

  $failCalled = false;

  $rule->validate('phone', '(21) 91234-5678', function () use (&$failCalled) {
    $failCalled = true;
  });

  expect($failCalled)->toBeFalse();
});

test('document rule passes for valid document', function () {
  $rule = new Document();

  $failCalled = false;

  $rule->validate('document', '928.066.570-75', function () use (&$failCalled) {
    $failCalled = true;
  });

  expect($failCalled)->toBeFalse();
});

test('document rule fails for invalid document', function () {
  $rule = new Document();

  $failCalled = false;

  $rule->validate('document', '900.066.570-75', function () use (&$failCalled) {
    $failCalled = true;
  });

  expect($failCalled)->toBeTrue();
});

test('document rule fails for missing digits', function () {
  $rule = new Document();

  $failCalled = false;

  $rule->validate('document', '92.06.57-75', function () use (&$failCalled) {
    $failCalled = true;
  });

  expect($failCalled)->toBeTrue();
});

test('document rule fails for sequency of same digits', function () {
  $rule = new Document();

  $failCalled = false;

  $rule->validate('document', '999.999.999-99', function () use (&$failCalled) {
    $failCalled = true;
  });

  expect($failCalled)->toBeTrue();
});
