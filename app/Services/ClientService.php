<?php

namespace App\Services;

use App\Models\Client;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ClientService
{
  public function createClient(array $data): void
  {
    DB::transaction(function () use ($data) {
      $userUuid = Auth::user()->uuid;

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
}
