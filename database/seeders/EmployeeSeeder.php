<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class EmployeeSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    $employeeRoleUuid = Role::where('name', 'employee')->value('uuid');

    $user = User::create([
      'name' => 'Funcionário',
      'email' => 'employee@email.com',
      'email_verified_at' => now(),
      'password' => bcrypt('Aa123456'),
      'role_uuid' => $employeeRoleUuid,
    ]);

    $user->details()->create([
      'document' => '31866473042',
      'date_of_birth' => '1990-08-05',
      'phone' => '21959736482',
      'address' => 'Rua do Funcionário, 456',
      'address_complement' => 'BL1 APT 101',
      'zip_code' => '67890321',
      'neighborhood' => 'Realengo',
      'city' => 'Rio de Janeiro',
      'salary' => 1900.00,
      'admission_date' => '2025-06-01',
    ]);
  }
}
