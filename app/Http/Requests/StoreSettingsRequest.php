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
      'rebooking_reminder_days'  => 'nullable|integer|min:1|max:365',
      'max_discount_percentage'  => 'nullable|integer|min:1|max:100',
      'discount_users'           => 'nullable|array',
      'discount_users.*'         => 'exists:users,uuid',
    ];
  }

  public function messages(): array
  {
    return [
      'rebooking_reminder_days.integer'  => 'O :attribute deve ser um número inteiro.',
      'rebooking_reminder_days.min'      => 'O :attribute deve ser no mínimo 1 dia.',
      'rebooking_reminder_days.max'      => 'O :attribute deve ser no máximo 365 dias.',
      'max_discount_percentage.integer'  => 'O :attribute deve ser um número inteiro.',
      'max_discount_percentage.min'      => 'O :attribute deve ser no mínimo 1%.',
      'max_discount_percentage.max'      => 'O :attribute não pode ultrapassar 100%.',
      'discount_users.*.exists'         => 'Um dos funcionários selecionados não existe.',
    ];
  }

  public function attributes(): array
  {
    return [
      'rebooking_reminder_days' => 'dias para lembrete de reagendamento',
      'max_discount_percentage' => 'teto de desconto',
    ];
  }
}
