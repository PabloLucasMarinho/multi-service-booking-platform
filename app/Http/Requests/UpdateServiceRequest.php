<?php

namespace App\Http\Requests;

class UpdateServiceRequest extends BaseFormRequest
{
  protected array $sanitize = [
    'name' => 'string',
    'price' => 'currency',
  ];

  /**
   * Determine if the user is authorized to make this request.
   */
  public function authorize(): bool
  {
    return true;
  }

  public function rules(): array
  {
    return [
      'name' => 'required|string|max:255',
      'price' => 'required|numeric|min:0|max:99999999.99',
    ];
  }

  public function messages(): array
  {
    return [
      'name.required' => 'O :attribute é obrigatório.',
      'name.string' => 'O :attribute cadastrado é inválido.',
      'name.max' => 'O :attribute é muito longo.',
      'price.required' => 'O :attribute é obrigatório.',
      'price.max' => 'Quantia inválida.',
      'price.min' => 'Quantia inválida.',
      'price.numeric' => 'O :attribute cadastrado é inválido.',
    ];
  }

  public function attributes(): array
  {
    return [
      'name' => 'nome',
      'price' => 'preço',
    ];
  }
}
