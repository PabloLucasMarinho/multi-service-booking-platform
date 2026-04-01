<?php

namespace App\Policies;

use App\Models\Appointment;
use App\Models\User;

class AppointmentPolicy
{
  public function before(User $user, string $ability): ?bool
  {
    if ($user->role->name === 'owner' || $user->role->name === 'admin') {
      return true;
    }

    return null;
  }

  /**
   * Determine whether the user can view any models.
   */
  public function viewAny(User $user): bool
  {
    return $user->role->name === 'employee';
  }

  /**
   * Determine whether the user can view the model.
   */
  public function view(User $user, Appointment $appointment): bool
  {
    return $user->role->name === 'employee';
  }

  /**
   * Determine whether the user can create models.
   */
  public function create(User $user, mixed $appointment = null): bool
  {
    if (!$appointment instanceof Appointment) {
      return in_array($user->role->name, ['admin', 'employee']);
    }

    if ($user->role->name === 'employee') {
      return (string)$appointment->user_uuid === (string)$user->uuid;
    }

    return false;
  }

  /**
   * Determine whether the user can update the model.
   */
  public function update(User $user, Appointment $appointment): bool
  {
    return (string)$appointment->user_uuid === (string)$user->uuid;
  }

  /**
   * Determine whether the user can delete the model.
   */
  public function delete(User $user, Appointment $appointment): bool
  {
    return false;
  }

  /**
   * Determine whether the user can restore the model.
   */
  public function restore(User $user, Appointment $appointment): bool
  {
    return false;
  }

  /**
   * Determine whether the user can permanently delete the model.
   */
  public function forceDelete(User $user, Appointment $appointment): bool
  {
    return false;
  }
}
