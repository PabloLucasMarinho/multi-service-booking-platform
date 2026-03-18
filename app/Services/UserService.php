<?php

namespace App\Services;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;

class UserService
{
  public function createEmployee(array $data): void
  {
    DB::transaction(function () use ($data) {
      $roleUuid = Role::where('name', $data['role'])->value('uuid')
        ?? throw new \RuntimeException("Função '{$data['role']}' não encontrada.");

      $user = User::create([
        'name' => $data['name'],
        'email' => $data['email'],
        'password' => null,
        'role_uuid' => $roleUuid,
      ]);

      $user->details()->create([
        'document' => $data['document'],
        'date_of_birth' => $data['date_of_birth'],
        'phone' => $data['phone'],
        'zip_code' => $data['zip_code'],
        'address' => $data['address'],
        'address_complement' => $data['address_complement'] ?? null,
        'neighborhood' => $data['neighborhood'],
        'city' => $data['city'],
        'salary' => $data['salary'],
        'admission_date' => $data['admission_date'],
      ]);

      Password::sendResetLink([
        'email' => $user->email,
      ]);
    });
  }

  public function updateEmployee(array $data, User $user): void
  {
    DB::transaction(function () use ($data, $user) {
      $roleUuid = Role::where('name', $data['role'])->value('uuid');
      $details = $user->details;

      $user->update([
        'name' => $data['name'],
        'email' => $data['email'],
        'role_uuid' => $roleUuid,
      ]);

      $details->update([
        'document' => $data['document'],
        'date_of_birth' => $data['date_of_birth'],
        'phone' => $data['phone'],
        'zip_code' => $data['zip_code'],
        'address' => $data['address'],
        'address_complement' => $data['address_complement'],
        'neighborhood' => $data['neighborhood'],
        'city' => $data['city'],
        'salary' => $data['salary'],
        'admission_date' => $data['admission_date'],
      ]);
    });
  }
}
