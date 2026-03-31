<?php

namespace App\Http\Requests;

use App\Rules\Document;

class StoreCompanyRequest extends BaseFormRequest
{
  protected array $sanitize = [
    'name' => 'string',
    'fantasy_name' => 'string',
    'document' => 'digits',
    'phone' => 'digits',
    'zip_code' => 'digits',
    'email' => 'lowercase',
  ];

  public function authorize(): bool
  {
    return true;
  }

  public function rules(): array
  {
    return [
      'name' => 'required|string|max:255',
      'fantasy_name' => 'nullable|string|max:255',
      'document' => ['nullable', 'digits_between:11,14', new Document],
      'email' => 'nullable|email|max:255',
      'phone' => 'nullable|digits_between:10,11',
      'zip_code' => 'nullable|digits:8',
      'address' => 'nullable|string|max:100',
      'address_number' => 'nullable|string|max:10',
      'address_complement' => 'nullable|string|max:50',
      'neighborhood' => 'nullable|string|max:50',
      'city' => 'nullable|string|max:50',
      'state' => 'nullable|string|max:2',
    ];
  }

  public function messages(): array
  {
    return [
      'name.required' => 'O :attribute é obrigatório.',
      'name.max' => 'O :attribute é muito longo.',
      'fantasy_name.max' => 'O :attribute é muito longo.',
      'document.digits_between' => 'O :attribute deve ter 11 (CPF) ou 14 (CNPJ) dígitos.',
      'email.email' => 'O :attribute informado é inválido.',
      'phone.digits_between' => 'O :attribute deve ter 10 ou 11 dígitos.',
      'zip_code.digits' => 'O :attribute deve ter 8 dígitos.',
      'state.max' => 'O :attribute deve ter 2 caracteres (ex: RJ, SP).',
    ];
  }

  public function attributes(): array
  {
    return [
      'name' => 'razão social',
      'fantasy_name' => 'nome fantasia',
      'document' => 'CNPJ/CPF',
      'email' => 'e-mail',
      'phone' => 'telefone',
      'zip_code' => 'CEP',
      'address' => 'endereço',
      'address_number' => 'número',
      'address_complement' => 'complemento',
      'neighborhood' => 'bairro',
      'city' => 'cidade',
      'state' => 'estado',
    ];
  }
}
