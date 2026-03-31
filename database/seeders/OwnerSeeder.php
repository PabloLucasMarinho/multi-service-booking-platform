<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class OwnerSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    $ownerRoleUuid = Role::where('name', 'owner')->value('uuid');

    User::create([
      'name' => 'Dono',
      'email' => 'owner@email.com',
      'email_verified_at' => now(),
      'password' => bcrypt('Aa123456'),
      'role_uuid' => $ownerRoleUuid,
      'document' => '23710591023',
      'date_of_birth' => '1970-07-17',
      'phone' => '21954654654',
      'address' => 'Rua do Dono',
      'address_number' => '789',
      'address_complement' => 'Apt 604',
      'zip_code' => '98765321',
      'neighborhood' => 'Centro',
      'city' => 'Rio de Janeiro',
      'state' => 'RJ',
      'salary' => 30000.00,
      'admission_date' => '2025-01-01',
    ]);
  }
}
