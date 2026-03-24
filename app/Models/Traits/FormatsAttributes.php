<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @mixin Model
 */
trait FormatsAttributes
{
  protected function name(): Attribute
  {
    return Attribute::make(
      set: fn($value) => $this->formatName($value)
    );
  }

  protected function documentFormatted(): Attribute
  {
    return Attribute::make(
      get: fn() => $this->formatCpf($this->document)
    );
  }

  protected function phoneFormatted(): Attribute
  {
    return Attribute::make(
      get: fn() => $this->formatPhone($this->phone)
    );
  }

  protected function dateOfBirthFormatted(): Attribute
  {
    return Attribute::make(
      get: fn() => $this->formatDate($this->date_of_birth)
    );
  }

  protected function admissionDateFormatted(): Attribute
  {
    return Attribute::make(
      get: fn() => $this->formatDate($this->admission_date)
    );
  }

  protected function startsAtFormatted(): Attribute
  {
    return Attribute::make(
      get: fn() => $this->formatDate($this->starts_at)
    );
  }

  protected function endsAtFormatted(): Attribute
  {
    return Attribute::make(
      get: fn() => $this->formatDate($this->ends_at)
    );
  }

  protected function activeFormatted(): Attribute
  {
    return Attribute::make(get: fn() => $this->active === true ? 'Ativo' : 'Inativo');
  }

  protected function salaryFormatted(): Attribute
  {
    return Attribute::make(
      get: fn() => $this->salary
        ? number_format((float)$this->salary, 2, ',', '.')
        : null
    );
  }

  protected function priceFormatted(): Attribute
  {
    return Attribute::make(
      get: fn() => $this->price
        ? number_format((float)$this->price, 2, ',', '.')
        : null
    );
  }

  protected function zipCodeFormatted(): Attribute
  {
    return Attribute::make(
      get: fn() => $this->formatCep($this->zip_code)
    );
  }

  protected function formatName(?string $name): ?string
  {
    if (!$name) {
      return $name;
    }

    $name = mb_convert_case(trim($name), MB_CASE_TITLE, 'UTF-8');

    $lower = [' De ', ' Da ', ' Do ', ' Dos ', ' Das ', ' E '];

    $name = str_replace(
      $lower,
      array_map(fn($w) => mb_strtolower($w, 'UTF-8'), $lower),
      " $name "
    );

    return trim($name);
  }

  private function formatCpf(?string $cpf): ?string
  {
    if (!$cpf || strlen($cpf) !== 11) {
      return $cpf;
    }

    return preg_replace(
      '/(\d{3})(\d{3})(\d{3})(\d{2})/',
      '$1.$2.$3-$4',
      $cpf
    );
  }

  private function formatPhone(?string $phone): ?string
  {
    if (!$phone) {
      return $phone;
    }

    if (strlen($phone) === 11) {
      return preg_replace(
        '/(\d{2})(\d{5})(\d{4})/',
        '($1) $2-$3',
        $phone
      );
    }

    if (strlen($phone) === 10) {
      return preg_replace(
        '/(\d{2})(\d{4})(\d{4})/',
        '($1) $2-$3',
        $phone
      );
    }

    return $phone;
  }

  private function formatDate($date): string
  {
    if (!$date) {
      return '';
    }

    try {
      return Carbon::parse($date)->format('d/m/Y');
    } catch (\Throwable) {
      return (string)$date;
    }
  }

  private function formatCep(?string $cep): ?string
  {
    if (!$cep || strlen($cep) !== 8) {
      return $cep;
    }

    return preg_replace(
      '/(\d{5})(\d{3})/',
      '$1-$2',
      $cep
    );
  }
}
