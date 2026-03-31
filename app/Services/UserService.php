<?php

namespace App\Services;

use App\Jobs\SendResetPasswordEmail;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class UserService
{
  public function create(array $data): void
  {
    $user = DB::transaction(function () use ($data) {
      $roleUuid = Role::where('name', $data['role'])->value('uuid')
        ?? throw new RuntimeException("Função '{$data['role']}' não encontrada.");

      $user = User::create([
        'name' => $data['name'],
        'email' => $data['email'],
        'password' => null,
        'role_uuid' => $roleUuid,
        'document' => $data['document'],
        'date_of_birth' => $data['date_of_birth'],
        'phone' => $data['phone'],
        'zip_code' => $data['zip_code'],
        'address' => $data['address'],
        'address_number' => $data['address_number'] ?? null,
        'address_complement' => $data['address_complement'] ?? null,
        'neighborhood' => $data['neighborhood'],
        'city' => $data['city'],
        'state' => $data['state'],
        'salary' => $data['salary'],
        'admission_date' => $data['admission_date'],
      ]);

      return $user;
    });

    SendResetPasswordEmail::dispatch($user->email)->afterCommit();
  }

  public function update(array $data, User $user): User
  {
    return DB::transaction(function () use ($data, $user) {
      $roleUuid = Role::where('name', $data['role'])->value('uuid')
        ?? throw new RuntimeException("Função '{$data['role']}' não encontrada.");

      $user->update([
        'name' => $data['name'],
        'email' => $data['email'],
        'role_uuid' => $roleUuid,
        'document' => $data['document'],
        'date_of_birth' => $data['date_of_birth'],
        'phone' => $data['phone'],
        'zip_code' => $data['zip_code'],
        'address' => $data['address'],
        'address_number' => $data['address_number'] ?? null,
        'address_complement' => $data['address_complement'] ?? null,
        'neighborhood' => $data['neighborhood'],
        'city' => $data['city'],
        'state' => $data['state'],
        'salary' => $data['salary'],
        'admission_date' => $data['admission_date'],
      ]);

      return $user;
    });
  }
}
