<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    $this->call([
      RoleSeeder::class,
      AdminSeeder::class,
      EmployeeSeeder::class,
      ClientSeeder::class,
      OwnerSeeder::class,
      ServiceSeeder::class,
      PromotionSeeder::class,
      AppointmentSeeder::class,
      AppointmentServiceSeeder::class,
    ]);
  }
}
