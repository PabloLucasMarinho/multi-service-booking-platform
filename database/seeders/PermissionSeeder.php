<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PermissionSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    $permissions = [
      'users.view',
      'users.create',
      'users.update',
      'users.delete',

      'clients.view',
      'clients.create',
      'clients.update',
      'clients.delete',

      'services.view',
      'services.create',
      'services.update',
      'services.delete',

      'appointments.view',
      'appointments.create',
      'appointments.update',
      'appointments.delete',
    ];

    foreach ($permissions as $permission) {
      DB::table('permissions')->insert([
        'uuid' => (string)Str::uuid(),
        'name' => $permission,
        'created_at' => now(),
        'updated_at' => now(),
      ]);
    }
  }
}
