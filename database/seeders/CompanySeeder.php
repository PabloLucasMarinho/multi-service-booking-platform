<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
  public function run(): void
  {
    Company::create([
      'name'                    => 'Barbearia do João LTDA',
      'fantasy_name'            => 'Barbearia do João',
      'document'                => '12345678000199',
      'email'                   => 'contato@barbearia.com',
      'phone'                   => '21912345678',
      'zip_code'                => '21725180',
      'address'                 => 'Rua da Feira',
      'address_number'          => '123',
      'address_complement'      => 'Sala 1',
      'neighborhood'            => 'Realengo',
      'city'                    => 'Rio de Janeiro',
      'state'                   => 'RJ',
      'rebooking_reminder_days' => 30,
      'max_discount_percentage' => 30,
    ]);
  }
}
