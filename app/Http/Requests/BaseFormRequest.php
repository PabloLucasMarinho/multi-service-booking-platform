<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BaseFormRequest extends FormRequest
{
  protected array $sanitize = [];

  protected function prepareForValidation(): void
  {
    $data = [];

    foreach ($this->sanitize as $field => $type) {
      $value = $this->input($field);

      if (!is_string($value)) {
        continue;
      }

      $sanitized = match ($type) {
        'digits' => preg_replace('/\D/', '', $value),
        'string' => preg_replace('/\s+/', ' ', trim($value)),
        'lowercase' => strtolower(trim($value)),
        default => throw new \InvalidArgumentException(
          "Sanitizer type [$type] not supported."
        ),
      };

      if ($sanitized !== $value) {
        $data[$field] = $sanitized;
      }
    }

    if ($data !== []) {
      $this->merge($data);
    }
  }
}
