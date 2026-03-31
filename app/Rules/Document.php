<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class Document implements ValidationRule
{
  public function validate(string $attribute, mixed $value, Closure $fail): void
  {
    $document = preg_replace('/\D/', '', $value);

    if (strlen($document) === 11) {
      $this->validateCpf($document, $fail);
    } elseif (strlen($document) === 14) {
      $this->validateCnpj($document, $fail);
    } else {
      $fail('O :attribute informado é inválido.');
    }
  }

  private function validateCpf(string $cpf, Closure $fail): void
  {
    if (preg_match('/(\d)\1{10}/', $cpf)) {
      $fail('O CPF informado é inválido.');
      return;
    }

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

  private function validateCnpj(string $cnpj, Closure $fail): void
  {
    if (preg_match('/(\d)\1{13}/', $cnpj)) {
      $fail('O CNPJ informado é inválido.');
      return;
    }

    for ($t = 12; $t < 14; $t++) {
      $d = 0;
      $mod = $t === 12 ? 5 : 6;

      for ($c = 0; $c < $t; $c++) {
        $d += $cnpj[$c] * $mod;
        $mod = $mod === 2 ? 9 : $mod - 1;
      }

      $d = $d % 11 < 2 ? 0 : 11 - ($d % 11);

      if ($cnpj[$c] != $d) {
        $fail('O CNPJ informado é inválido.');
        return;
      }
    }
  }
}
