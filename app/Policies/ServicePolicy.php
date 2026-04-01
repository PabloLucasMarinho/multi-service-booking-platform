<?php

namespace App\Policies;

use App\Models\Service;
use App\Models\User;

class ServicePolicy
{
  public function before(User $user, string $ability): ?bool
  {
    if ($user->role->name === 'owner') {
      return true;
    }

    return null;
  }

  public function viewAny(User $user): bool
  {
    return true;
  }

  public function create(User $user): bool
  {
    return $user->role->name === 'admin';
  }

  public function update(User $user, Service $service): bool
  {
    if ($user->role->name !== 'admin') {
      return false;
    }

    return (string)$service->created_by === (string)$user->uuid;
  }

  public function delete(User $user, Service $service): bool
  {
    return false;
  }
}
