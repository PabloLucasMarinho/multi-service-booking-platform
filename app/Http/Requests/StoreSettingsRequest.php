<?php

namespace App\Http\Requests;

class StoreSettingsRequest extends BaseFormRequest
{
  public function authorize(): bool
  {
    return true;
  }

  public function rules(): array
  {
    return [
      'rebooking_reminder_days' => 'nullable|integer|min:1|max:365',
    ];
  }

  public function messages(): array
  {
    return [
      'rebooking_reminder_days.integer' => 'O :attribute deve ser um número inteiro.',
      'rebooking_reminder_days.min' => 'O :attribute deve ser no mínimo 1 dia.',
      'rebooking_reminder_days.max' => 'O :attribute deve ser no máximo 365 dias.',
    ];
  }

  public function attributes(): array
  {
    return [
      'rebooking_reminder_days' => 'dias para lembrete de reagendamento',
    ];
  }
}
