<?php

namespace App\Http\Requests;

use App\Rules\Cpf;
use App\Rules\DateOfBirth;
use App\Rules\Phone;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class StoreClientRequest extends BaseFormRequest
{
  protected array $sanitize = [
    'name' => 'string',
    'document' => 'digits',
    'phone' => 'digits',
    'email' => 'lowercase',
  ];

  protected function prepareForValidation(): void
  {
    parent::prepareForValidation();

    $dateOfBirth = $this->input('date_of_birth');

    if (is_string($dateOfBirth)) {
      try {
        if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $dateOfBirth)) {
          $this->merge(['date_of_birth' => Carbon::createFromFormat(
            'd/m/Y',
            $dateOfBirth
          )->format('Y-m-d')
          ]);
        }
      } catch (\Throwable) {
        // deixa a validação falhar depois
      }
    }
  }

  public function authorize(): bool
  {
    return true;
  }

  public function rules(): array
  {
    return [
      'name' => 'required|string|min:2|max:255',
      'document' => ['required', new Cpf, Rule::unique('clients', 'document')],
      'date_of_birth' => ['required', new DateOfBirth],
      'email' => ['nullable', 'email', 'required_without:phone'],
      'phone' => ['nullable', new Phone, 'required_without:email'],
    ];
  }

  public function messages(): array
  {
    return [
      'name.required' => 'O :attribute é obrigatório.',
      'document.required' => 'O :attribute é obrigatório.',
      'document.unique' => 'Este :attribute já foi cadastrado.',
      'date_of_birth.required' => 'A :attribute é obrigatória.',
      'email.required_without' => 'Informe um :attribute ou telefone.',
      'phone.required_without' => 'Informe um :attribute ou e-mail.',
    ];
  }

  public function attributes(): array
  {
    return [
      'name' => 'nome',
      'document' => 'CPF',
      'date_of_birth' => 'data de nascimento',
      'phone' => 'telefone',
      'email' => 'e-mail',
    ];
  }
}
