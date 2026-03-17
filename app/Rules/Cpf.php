<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class Cpf implements ValidationRule
{
  public function validate(string $attribute, mixed $value, Closure $fail): void
  {
    // Retira os pontos e traços
    $cpf = preg_replace('/\D/', '', $value);

    // Falha se for menor que 11 dígitos
    if (strlen($cpf) !== 11) {
      $fail('O CPF informado é inválido.');
      return;
    }

    // Falha se for uma sequência do mesmo dígito repetido
    if (preg_match('/(\d)\1{10}/', $cpf)) {
      $fail('O CPF informado é inválido.');
      return;
    }

    // Falha se o CPF for inválido
    for ($t = 9; $t < 11; $t++) {
      $d = 0;
      for ($c = 0; $c < $t; $c++) {
        $d += $cpf[$c] * (($t + 1) - $c);
      }
      $d = ((10 * $d) % 11) % 10;

      if ($cpf[$c] != $d) {
        $fail('O CPF informado é inválido.');
        return;
      }
    }
  }
}
