<?php

namespace App\Policies;

use App\Models\Client;
use App\Models\User;

class ClientPolicy
{
  public function before(User $user, string $ability): ?bool
  {
    if ($user->role->name === 'owner') {
      return true;
    }

    return null;
  }

  /**
   * Determine whether the user can view any models.
   */
  public function viewAny(User $user): bool
  {
    return in_array($user->role->name, ['admin', 'employee']);
  }

  public function view(User $user, Client $client): bool
  {
    return in_array($user->role->name, ['admin', 'employee']);
  }

  public function create(User $user): bool
  {
    return in_array($user->role->name, ['admin', 'employee']);
  }

  public function update(User $user, Client $client): bool
  {
    return in_array($user->role->name, ['admin', 'employee']);
  }

  /**
   * Determine whether the user can delete the model.
   */
  public function delete(User $user, Client $client): bool
  {
    return false;
  }

  /**
   * Determine whether the user can restore the model.
   */
  public function restore(): bool
  {
    return false;
  }

  /**
   * Determine whether the user can permanently delete the model.
   */
  public function forceDelete(): bool
  {
    return false;
  }
}
