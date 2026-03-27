<?php

namespace App\Http\Requests;

class StoreAppointmentServiceRequest extends BaseFormRequest
{
  protected array $sanitize = [
    'manual_discount_value' => 'currency',
  ];

  public function authorize(): bool
  {
    return true;
  }

  public function rules(): array
  {
    return [
      'service_uuid' => 'required|uuid|exists:services,uuid',
      'manual_discount_type' => 'nullable|in:percentage,fixed',
      'manual_discount_value' => 'nullable|numeric|min:0|required_with:manual_discount_type',
    ];
  }

  public function messages(): array
  {
    return [
      'service_uuid.required' => 'Selecione um serviço.',
      'service_uuid.exists' => 'O serviço informado não existe.',
      'manual_discount_type.in' => 'Tipo de desconto inválido.',
      'manual_discount_value.numeric' => 'O valor do desconto deve ser numérico.',
      'manual_discount_value.min' => 'O valor do desconto não pode ser negativo.',
      'manual_discount_value.required_with' => 'Informe o valor do desconto.',
    ];
  }

  public function attributes(): array
  {
    return [
      'service_uuid' => 'serviço',
      'manual_discount_type' => 'tipo de desconto',
      'manual_discount_value' => 'valor do desconto',
    ];
  }
}
