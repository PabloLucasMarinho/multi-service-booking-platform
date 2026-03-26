<?php

namespace App\Services;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use Illuminate\Support\Facades\DB;

class AppointmentService
{
  public function create(array $data): mixed
  {
    return DB::transaction(function () use ($data) {
      return Appointment::create([
        'scheduled_at' => $data['scheduled_at'],
        'notes' => $data['notes'],
        'client_uuid' => $data['client'],
        'user_uuid' => $data['user'],
        'status' => AppointmentStatus::Scheduled,
      ]);
    });
  }

  public function update(array $data, Appointment $appointment): mixed
  {
    return DB::transaction(function () use ($data, $appointment) {
      $appointment->update([
        'scheduled_at' => $data['scheduled_at'],
        'notes' => $data['notes'],
        'client_uuid' => $data['client'],
        'user_uuid' => $data['user'],
        'status' => $appointment->status,
      ]);

      return $appointment;
    });
  }
}
