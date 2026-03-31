<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RoleSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    $roles = [
      'owner',
      'admin',
      'employee',
    ];

    foreach ($roles as $role) {
      DB::table('roles')->insert([
        'uuid' => (string)Str::uuid(),
        'name' => $role,
        'created_at' => now(),
        'updated_at' => now(),
      ]);
    }
  }
}
