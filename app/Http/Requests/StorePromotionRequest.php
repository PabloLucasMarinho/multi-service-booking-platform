<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class StorePromotionRequest extends BaseFormRequest
{
  protected array $sanitize = [
    'name' => 'string',
    'value' => 'currency',
  ];

  protected function prepareForValidation(): void
  {
    parent::prepareForValidation();

    foreach (['starts_at', 'ends_at'] as $field) {
      $value = $this->input($field);

      if (is_string($value) && preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $value)) {
        try {
          $date = Carbon::createFromFormat('d/m/Y', $value);

          $this->merge([
            $field => $field === 'starts_at'
              ? $date->startOfDay()->format('Y-m-d H:i:s')
              : $date->endOfDay()->format('Y-m-d H:i:s')
          ]);
        } catch (\Throwable) {
          // deixa validar depois
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

  /**
   * Get the validation rules that apply to the request.
   *
   * @return array<string, ValidationRule|array<mixed>|string>
   */
  public function rules(): array
  {
    return [
      'name' => 'required|string',
      'type' => ['required', 'in:fixed,percentage'],
      'value' => [
        'required',
        'numeric',
        'min:0',
        Rule::when(
          $this->input('type') === 'percentage',
          ['max:100']
        )
      ],
      'starts_at' => ['required', 'date'],
      'ends_at' => ['required', 'date', 'after:starts_at'],
      'categories' => ['nullable', 'array'],
      'categories.*' => ['string'],
    ];
  }

  public function messages(): array
  {
    return [
      'name.required' => 'O :attribute é obrigatório.',
      'type.required' => 'O :attribute é obrigatório.',
      'type.in' => 'O :attribute informado é inválido.',
      'value.required' => 'O :attribute é obrigatório.',
      'value.numeric' => 'O :attribute informado é inválido.',
      'value.min' => 'O :attribute não pode ser menor que :min.',
      'value.max' => 'O :attribute não pode ser maior que :max.',
      'starts_at.required' => 'A :attribute é obrigatória.',
      'starts_at.date' => 'A :attribute informada é inválida.',
      'ends_at.required' => 'A :attribute é obrigatória.',
      'ends_at.date' => 'A :attribute informada é inválida.',
      'ends_at.after' => 'A :attribute não pode ser anterior a :starts_at.',
    ];
  }

  public function attributes(): array
  {
    return [
      'name' => 'nome',
      'type' => 'tipo',
      'value' => 'valor',
      'starts_at' => 'data inicial',
      'ends_at' => 'data final',
    ];
  }
}
