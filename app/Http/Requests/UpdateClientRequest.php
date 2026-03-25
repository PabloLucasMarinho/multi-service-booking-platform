<?php

namespace App\Http\Requests;

use App\Rules\Cpf;
use App\Rules\DateOfBirth;
use App\Rules\Phone;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class UpdateClientRequest extends BaseFormRequest
{
  protected array $sanitize = [
    'name' => 'string',
    'document' => 'digits',
    'phone' => 'digits',
    'email' => 'lowercase',
  ];

  protected function prepareForValidation(): void
  {
    $dateOfBirth = $this->input('date_of_birth');

    if (is_string($dateOfBirth)) {
      try {
        if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $dateOfBirth)) {
          $this->merge(['date_of_birth' => Carbon::createFromFormat(
            'd/m/Y',
            $dateOfBirth
          )->startOfDay()->format('Y-m-d H:i:s')
          ]);
        }
      } catch (\Throwable) {
        // deixa a validação falhar depois
      }
    }

    parent::prepareForValidation();
  }

  public function authorize(): bool
  {
    return true;
  }

  public function rules(): array
  {
    $client = $this->route('client');

    return [
      'name' => 'required|string|min:2|max:255',
      'document' => ['required', new Cpf, Rule::unique('clients', 'document')->ignore($client)],
      'date_of_birth' => [
        'required',
        'date',
        'before:today',
        'after:' . now()->subYears(120)->startOfDay()->format('Y-m-d H:i:s'),
      ],
      'email' => ['nullable', 'email', 'required_without:phone', Rule::unique('clients', 'email')->ignore($client)],
      'phone' => ['nullable', 'required_without:email', new Phone]
    ];
  }

  public function messages(): array
  {
    return [
      'name.required' => 'O :attribute é obrigatório.',
      'document.required' => 'O :attribute é obrigatório.',
      'document.unique' => 'Já existe um cliente cadastrado com esse :attribute.',
      'date_of_birth.date' => 'A :attribute é inválida.',
      'date_of_birth.required' => 'A :attribute é obrigatória.',
      'email.required_without' => 'Informe um :attribute ou telefone.',
      'email.unique' => 'Já existe um cliente cadastrado com esse :attribute.',
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
