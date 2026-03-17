<?php

if (!function_exists('Database\Factories\generateCpf')) {
  function generateCpf(): string
  {
    $n = [];
    for ($i = 0; $i < 9; $i++) {
      $n[$i] = rand(0, 9);
    }

    // primeiro dígito verificador
    $d1 = 0;
    for ($i = 0, $j = 10; $i < 9; $i++, $j--) {
      $d1 += $n[$i] * $j;
    }
    $d1 = $d1 % 11;
    $d1 = $d1 < 2 ? 0 : 11 - $d1;

    // segundo dígito verificador
    $d2 = 0;
    for ($i = 0, $j = 11; $i < 9; $i++, $j--) {
      $d2 += $n[$i] * $j;
    }
    $d2 += $d1 * 2;
    $d2 = $d2 % 11;
    $d2 = $d2 < 2 ? 0 : 11 - $d2;

    return implode('', $n) . $d1 . $d2;
  }
}

function generateUniqueCpf(): string
{
  static $existingCpfs = []; // estática, persiste entre chamadas

  do {
    $cpf = generateCpf();
  } while (in_array($cpf, $existingCpfs));

  $existingCpfs[] = $cpf;

  return $cpf;
}
