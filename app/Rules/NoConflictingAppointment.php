<?php

namespace App\Rules;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Carbon;

class NoConflictingAppointment implements ValidationRule
{
  public function __construct(
    private ?string $userUuid = null,
    private ?string $excludeUuid = null
  ) {}

  public function validate(string $attribute, mixed $value, Closure $fail): void
  {
    $scheduledAt = Carbon::parse($value)->setSecond(0)->setMicrosecond(0);

    $query = Appointment::where('scheduled_at', $scheduledAt)
      ->where('status', AppointmentStatus::Scheduled);

    if ($this->userUuid) {
      $query->where('user_uuid', $this->userUuid);
    }

    if ($this->excludeUuid) {
      $query->where('uuid', '!=', $this->excludeUuid);
    }

    if ($query->exists()) {
      $fail('Este funcionário já possui um agendamento neste horário.');
    }
  }
}
