<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Seeder;

class ClientSeeder extends Seeder
{
  public function run(): void
  {
    $adminUuid = User::where('email', 'admin@email.com')->value('uuid');

    $clients = [
      ['name' => 'João da Silva', 'document' => '52998224725', 'date_of_birth' => '1990-05-15', 'phone' => '21991234567', 'email' => 'joao.silva@gmail.com'],
      ['name' => 'Pedro Oliveira', 'document' => '97633688002', 'date_of_birth' => '1985-08-22', 'phone' => '21992345678', 'email' => 'pedro.oliveira@gmail.com'],
      ['name' => 'Carlos Souza', 'document' => '71428793860', 'date_of_birth' => '1992-03-10', 'phone' => '21993456789', 'email' => null],
      ['name' => 'Lucas Pereira', 'document' => '87748248800', 'date_of_birth' => '1998-11-30', 'phone' => '21994567890', 'email' => 'lucas.pereira@hotmail.com'],
      ['name' => 'Matheus Costa', 'document' => '37999367800', 'date_of_birth' => '2000-07-04', 'phone' => '21995678901', 'email' => null],
      ['name' => 'Gabriel Santos', 'document' => '84574558138', 'date_of_birth' => '1995-02-18', 'phone' => '21996789012', 'email' => 'gabriel.santos@gmail.com'],
      ['name' => 'Rafael Lima', 'document' => '89045642000', 'date_of_birth' => '1988-09-25', 'phone' => '21997890123', 'email' => null],
      ['name' => 'Felipe Rodrigues', 'document' => '61188595074', 'date_of_birth' => '1993-12-08', 'phone' => '21998901234', 'email' => 'felipe.rodrigues@outlook.com'],
      ['name' => 'Bruno Alves', 'document' => '37379081000', 'date_of_birth' => '1987-06-14', 'phone' => '21999012345', 'email' => null],
      ['name' => 'André Ferreira', 'document' => '83291831040', 'date_of_birth' => '1996-04-20', 'phone' => '21991122334', 'email' => 'andre.ferreira@gmail.com'],
    ];

    foreach ($clients as $data) {
      Client::create([
        'user_uuid' => $adminUuid,
        'name' => $data['name'],
        'document' => $data['document'],
        'date_of_birth' => $data['date_of_birth'],
        'phone' => $data['phone'],
        'email' => $data['email'],
      ]);
    }
  }
}
