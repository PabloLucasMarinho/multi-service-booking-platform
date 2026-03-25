<?php

namespace App\Http\Requests;

use App\Rules\Cep;
use App\Rules\Cpf;
use App\Rules\DateOfBirth;
use App\Rules\Phone;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends BaseFormRequest
{
  protected array $sanitize = [
    'name' => 'string',
    'document' => 'digits',
    'zip_code' => 'digits',
    'phone' => 'digits',
    'email' => 'lowercase',
    'salary' => 'currency',
  ];

  protected function prepareForValidation(): void
  {
    parent::prepareForValidation();

    foreach (['date_of_birth', 'admission_date'] as $field) {
      $value = $this->input($field);
      if (is_string($value) && preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $value)) {
        try {
          $this->merge([
            $field => Carbon::createFromFormat('d/m/Y', $value)->startOfDay()->format('Y-m-d H:i:s')
          ]);
        } catch (\Throwable) {
          // deixa a validação falhar depois
        }
      }
    }
  }

  /**
   * Determine if the user is authorized to make this request.
   */
  public function authorize(): bool
  {
    return true;
  }

  public function rules(): array
  {
    $user = $this->route('user');

    return [
      'name' => 'required|string|max:255',
      'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($user)],

      'document' => ['required', new Cpf, Rule::unique('user_details', 'document')->ignore($user->details)],
      'date_of_birth' => [
        'required',
        'date',
        'before:today',
        'after:' . now()->subYears(120)->startOfDay()->format('Y-m-d H:i:s'),
      ],
      'phone' => ['required', new Phone],
      'address' => 'required|string|max:100',
      'address_complement' => 'nullable|string|max:50',
      'zip_code' => ['required', new Cep],
      'neighborhood' => 'required|string|max:50',
      'city' => 'required|string|max:50',
      'salary' => 'nullable|numeric|min:0|max:99999999.99',
      'admission_date' => 'required|date|before:tomorrow',
      'role' => 'required|string|in:admin,employee',
    ];
  }

  public function messages(): array
  {
    return [
      'name.required' => 'O :attribute é obrigatório.',
      'email.required' => 'O :attribute é obrigatório.',
      'email.unique' => 'Este :attribute já está cadastrado.',
      'document.required' => 'O :attribute é obrigatório.',
      'document.unique' => 'Este :attribute já está cadastrado.',
      'date_of_birth.required' => 'A :attribute é obrigatória.',
      'phone.required' => 'O :attribute é obrigatório.',
      'address.required' => 'O :attribute é obrigatório',
      'zip_code.required' => 'O :attribute é obrigatório.',
      'admission_date.required' => 'A :attribute é obrigatória.',
    ];
  }

  public function attributes(): array
  {
    return [
      'name' => 'nome',
      'email' => 'e-mail',
      'document' => 'CPF',
      'date_of_birth' => 'data de nascimento',
      'phone' => 'telefone',
      'address' => 'endereço',
      'address_complement' => 'complemento',
      'zip_code' => 'CEP',
      'neighborhood' => 'bairro',
      'city' => 'cidade',
      'salary' => 'salário',
      'admission_date' => 'data de admissão',
      'role' => 'função',
    ];
  }
}
