<?php

namespace App\Console\Commands;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use Illuminate\Console\Command;

class MarkNoShowAppointments extends Command
{
  protected $signature = 'appointments:mark-no-show';
  protected $description = 'Marca como no-show os agendamentos passados que permanecem com status scheduled';

  public function handle(): void
  {
    $updated = Appointment::where('status', AppointmentStatus::Scheduled)
      ->where('scheduled_at', '<', now()->startOfDay())
      ->update(['status' => AppointmentStatus::NoShow]);

    $this->info("$updated agendamento(s) marcado(s) como no-show.");
  }
}
