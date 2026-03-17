<?php

namespace App\Rules;

use Carbon\Carbon;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class DateOfBirth implements ValidationRule
{
  public function validate(string $attribute, mixed $value, Closure $fail): void
  {
    try {
      $date = null;

      foreach (['Y-m-d', 'd/m/Y'] as $format) {
        try {
          $date = Carbon::createFromFormat($format, $value);
          break;
        } catch (\Throwable) {
          continue;
        }
      }

      if (!$date) {
        $fail('A data de nascimento é inválida.');
        return;
      }

      if ($date->isFuture()) {
        $fail('A data de nascimento não pode ser futura.');
        return;
      }

      if ($date->lt(now()->subYears(120))) {
        $fail('A data de nascimento é inválida.');
        return;
      }
    } catch (\Exception $e) {
      $fail('A data de nascimento é inválida.');
    }
  }
}
