<?php

namespace Database\Seeders;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Seeder;

class AppointmentSeeder extends Seeder
{
  public function run(): void
  {
    $employeeUuid = User::where('email', 'employee@email.com')->value('uuid');
    $adminUuid = User::where('email', 'admin@email.com')->value('uuid');
    $clients = Client::pluck('uuid')->toArray();

    $appointments = [
      // Passados - concluídos
      ['days' => -30, 'hour' => '09:00', 'user' => $employeeUuid, 'status' => AppointmentStatus::Completed],
      ['days' => -28, 'hour' => '10:30', 'user' => $adminUuid, 'status' => AppointmentStatus::Completed],
      ['days' => -25, 'hour' => '14:00', 'user' => $employeeUuid, 'status' => AppointmentStatus::Completed],
      ['days' => -22, 'hour' => '11:00', 'user' => $adminUuid, 'status' => AppointmentStatus::Completed],
      ['days' => -20, 'hour' => '09:30', 'user' => $employeeUuid, 'status' => AppointmentStatus::Completed],
      ['days' => -18, 'hour' => '15:00', 'user' => $adminUuid, 'status' => AppointmentStatus::Completed],
      ['days' => -15, 'hour' => '16:00', 'user' => $employeeUuid, 'status' => AppointmentStatus::Completed],

      // Passados - cancelados
      ['days' => -27, 'hour' => '13:00', 'user' => $employeeUuid, 'status' => AppointmentStatus::Cancelled],
      ['days' => -21, 'hour' => '10:00', 'user' => $adminUuid, 'status' => AppointmentStatus::Cancelled],
      ['days' => -14, 'hour' => '11:30', 'user' => $employeeUuid, 'status' => AppointmentStatus::Cancelled],

      // Passados - não compareceu
      ['days' => -26, 'hour' => '08:00', 'user' => $adminUuid, 'status' => AppointmentStatus::NoShow],
      ['days' => -19, 'hour' => '17:00', 'user' => $employeeUuid, 'status' => AppointmentStatus::NoShow],
      ['days' => -12, 'hour' => '09:00', 'user' => $adminUuid, 'status' => AppointmentStatus::NoShow],

      // Passados recentes - concluídos
      ['days' => -7, 'hour' => '10:00', 'user' => $employeeUuid, 'status' => AppointmentStatus::Completed],
      ['days' => -5, 'hour' => '14:30', 'user' => $adminUuid, 'status' => AppointmentStatus::Completed],
      ['days' => -3, 'hour' => '11:00', 'user' => $employeeUuid, 'status' => AppointmentStatus::Completed],
      ['days' => -1, 'hour' => '16:00', 'user' => $adminUuid, 'status' => AppointmentStatus::Completed],

      // Hoje - agendados
      ['days' => 0, 'hour' => '09:00', 'user' => $employeeUuid, 'status' => AppointmentStatus::Scheduled],
      ['days' => 0, 'hour' => '11:00', 'user' => $adminUuid, 'status' => AppointmentStatus::Scheduled],
      ['days' => 0, 'hour' => '14:00', 'user' => $employeeUuid, 'status' => AppointmentStatus::Scheduled],
      ['days' => 0, 'hour' => '16:30', 'user' => $adminUuid, 'status' => AppointmentStatus::Scheduled],

      // Futuros próximos - agendados
      ['days' => 1, 'hour' => '09:30', 'user' => $employeeUuid, 'status' => AppointmentStatus::Scheduled],
      ['days' => 2, 'hour' => '10:00', 'user' => $adminUuid, 'status' => AppointmentStatus::Scheduled],
      ['days' => 3, 'hour' => '11:30', 'user' => $employeeUuid, 'status' => AppointmentStatus::Scheduled],
      ['days' => 5, 'hour' => '14:00', 'user' => $adminUuid, 'status' => AppointmentStatus::Scheduled],
      ['days' => 7, 'hour' => '09:00', 'user' => $employeeUuid, 'status' => AppointmentStatus::Scheduled],
      ['days' => 10, 'hour' => '15:00', 'user' => $adminUuid, 'status' => AppointmentStatus::Scheduled],
      ['days' => 14, 'hour' => '10:30', 'user' => $employeeUuid, 'status' => AppointmentStatus::Scheduled],
      ['days' => 20, 'hour' => '11:00', 'user' => $adminUuid, 'status' => AppointmentStatus::Scheduled],
      ['days' => 30, 'hour' => '14:00', 'user' => $employeeUuid, 'status' => AppointmentStatus::Scheduled],
    ];

    foreach ($appointments as $index => $data) {
      $scheduledAt = now()->addDays($data['days'])->setTimeFromTimeString($data['hour']);

      Appointment::create([
        'user_uuid' => $data['user'],
        'client_uuid' => $clients[$index % count($clients)],
        'scheduled_at' => $scheduledAt,
        'notes' => null,
        'status' => $data['status'],
      ]);
    }
  }
}
