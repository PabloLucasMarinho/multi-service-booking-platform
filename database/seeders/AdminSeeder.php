<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    $adminRoleUuid = Role::where('name', 'admin')->value('uuid');

    User::create([
      'name' => 'Administrador',
      'email' => 'admin@email.com',
      'email_verified_at' => now(),
      'password' => bcrypt('Aa123456'),
      'role_uuid' => $adminRoleUuid,
      'document' => '37669498021',
      'date_of_birth' => '1980-04-25',
      'phone' => '21964825973',
      'address' => 'Rua do Administrador',
      'address_number' => '123',
      'address_complement' => 'Casa 2',
      'zip_code' => '12345123',
      'neighborhood' => 'Centro',
      'city' => 'Rio de Janeiro',
      'state' => 'RJ',
      'salary' => 12000.00,
      'admission_date' => '2025-01-01',
    ]);
  }
}
