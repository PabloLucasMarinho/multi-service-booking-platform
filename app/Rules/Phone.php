<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class Phone implements ValidationRule
{
  /**
   * Run the validation rule.
   *
   * @param \Closure(string, ?string=): PotentiallyTranslatedString $fail
   */
  public function validate(string $attribute, mixed $value, Closure $fail): void
  {
    // Se não foi informado, não valida
    if (blank($value)) {
      return;
    }

    // Remove máscara
    $telefone = preg_replace('/\D/', '', $value);

    // 10 (fixo) ou 11 (celular)
    if (!in_array(strlen($telefone), [10, 11], true)) {
      $fail('O telefone informado é inválido.');
      return;
    }

    // DDD válido (01–99, não começando com 0)
    if (!preg_match('/^[1-9]{2}/', $telefone)) {
      $fail('O telefone informado é inválido.');
      return;
    }

    // Celular deve começar com 9
    if (strlen($telefone) === 11 && $telefone[2] !== '9') {
      $fail('O telefone informado é inválido.');
    }
  }
}
