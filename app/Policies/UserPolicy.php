<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
  public function before(User $user, string $ability): ?bool
  {
    if ($user->role->name === 'owner') {
      return true;
    }

    return null;
  }

  private function targetIsOwner(User $model): bool
  {
    return $model->role->name === 'owner';
  }

  public function viewAny(User $user): bool
  {
    return $user->role->name === 'admin';
  }

  public function viewSelf(): bool
  {
    return true;
  }

  public function view(User $user, User $model): bool
  {
    return $user->role->name === 'admin';
  }

  public function create(User $user, mixed $model = null): bool
  {
    return $user->role->name === 'admin';
  }

  public function update(User $authUser, mixed $model = null): bool
  {
    if (!$model instanceof User) {
      return $authUser->role->name === 'admin';
    }

    if ($this->targetIsOwner($model)) {
      return false;
    }

    if ($model->role->name === 'admin' && $authUser->uuid !== $model->uuid) {
      return false;
    }

    return $authUser->role->name === 'admin';
  }

  public function delete(User $user, User $model): bool
  {
    return false;
  }

  public function restore(User $user, User $model): bool
  {
    return false;
  }

  public function forceDelete(User $user, User $model): bool
  {
    return false;
  }
}
