<?php

namespace App\Http\Requests;

use App\Enums\BrazilianState;
use App\Enums\RoleNames;
use App\Models\User;
use App\Rules\Cep;
use App\Rules\Document;
use App\Rules\Phone;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class StoreUserRequest extends BaseFormRequest
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

  public function withValidator($validator): void
  {
    $validator->after(function ($validator) {
      $deletedUser = User::onlyTrashed()
        ->where(function ($query) {
          $query->where('email', $this->email)
            ->orWhere('document', $this->document);
        })
        ->first();

      if ($deletedUser) {
        $this->merge(['deleted_user_data' => $deletedUser]);
      }
    });
  }

  public function authorize(): bool
  {
    return true;
  }

  public function rules(): array
  {
    return [
      'name' => 'required|string|max:255',
      'email' => ['required', 'email', Rule::unique('users', 'email')->whereNull('deleted_at')],

      'document' => ['required', new Document, Rule::unique('users', 'document')->whereNull('deleted_at')],
      'date_of_birth' => [
        'required',
        'date',
        'before:today',
        'after:' . now()->subYears(120)->startOfDay()->format('Y-m-d H:i:s'),
      ],
      'phone' => ['required', new Phone],
      'address' => 'required|string|max:100',
      'address_number' => 'nullable|string|max:10',
      'address_complement' => 'nullable|string|max:50',
      'zip_code' => ['required', new Cep],
      'neighborhood' => 'required|string|max:50',
      'city' => 'required|string|max:50',
      'state' => ['required', Rule::enum(BrazilianState::class)],
      'salary' => 'nullable|numeric|min:0|max:99999999.99',
      'admission_date' => 'required|date|before:tomorrow',
      'role' => ['required', Rule::enum(RoleNames::class)],
    ];
  }

  public function messages(): array
  {
    return [
      'name.required' => 'O :attribute é obrigatório.',

      'email.required' => 'O :attribute é obrigatório.',
      'email.email' => 'O :attribute informado é inválido.',
      'email.unique' => 'Este :attribute já está cadastrado.',

      'document.required' => 'O :attribute é obrigatório.',
      'document.unique' => 'Este :attribute já está cadastrado.',

      'date_of_birth.required' => 'A :attribute é obrigatória.',
      'date_of_birth.date' => 'A :attribute informada é inválida.',
      'date_of_birth.before' => 'A :attribute não pode ser uma data futura.',
      'date_of_birth.after' => 'A :attribute informada é inválida.',

      'phone.required' => 'O :attribute é obrigatório.',

      'address.required' => 'O :attribute é obrigatório.',
      'address.max' => 'O :attribute não pode ter mais de 100 caracteres.',

      'zip_code.required' => 'O :attribute é obrigatório.',

      'neighborhood.required' => 'O :attribute é obrigatório.',
      'city.required' => 'A :attribute é obrigatória.',
      'state.required' => 'O :attribute é obrigatório.',
      'state.enum' => 'O :attribute informado é inválido.',

      'salary.numeric' => 'O :attribute informado é inválido.',
      'salary.min' => 'O :attribute não pode ser negativo.',
      'salary.max' => 'O :attribute informado é muito alto.',

      'admission_date.required' => 'A :attribute é obrigatória.',
      'admission_date.date' => 'A :attribute informada é inválida.',
      'admission_date.before' => 'A :attribute não pode ser futura.',

      'role.required' => 'A :attribute é obrigatória.',
      'role.enum' => 'A :attribute informada é inválida.',
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
      'address_number' => 'número',
      'address_complement' => 'complemento',
      'zip_code' => 'CEP',
      'neighborhood' => 'bairro',
      'city' => 'cidade',
      'state' => 'estado',
      'salary' => 'salário',
      'admission_date' => 'data de admissão',
      'role' => 'função',
    ];
  }
}
