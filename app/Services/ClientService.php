<?php

namespace App\Services;

use App\Models\Client;
use Illuminate\Support\Facades\DB;

class ClientService
{
  public function create(array $data, string $userUuid): void
  {
    DB::transaction(function () use ($data, $userUuid) {
      Client::create([
        'name' => $data['name'],
        'date_of_birth' => $data['date_of_birth'],
        'document' => $data['document'],
        'phone' => $data['phone'] ?? null,
        'email' => $data['email'] ?? null,
        'user_uuid' => $userUuid,
      ]);
    });
  }

  public function update(array $data, Client $client): void
  {
    DB::transaction(function () use ($data, $client) {
      $client->update([
        'name' => $data['name'],
        'date_of_birth' => $data['date_of_birth'],
        'document' => $data['document'],
        'phone' => $data['phone'] ?? null,
        'email' => $data['email'] ?? null,
      ]);
    });
  }
}
