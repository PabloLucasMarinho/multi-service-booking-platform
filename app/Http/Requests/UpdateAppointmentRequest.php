<?php

namespace App\Http\Requests;

use Illuminate\Support\Carbon;

class UpdateAppointmentRequest extends BaseFormRequest
{
  protected array $sanitize = [
    'notes' => 'string',
  ];

  protected function prepareForValidation(): void
  {
    parent::prepareForValidation();

    $date = $this->input('scheduled_at');
    $hour = $this->input('scheduled_hour');

    if (is_string($date) && is_string($hour) &&
      preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $date) &&
      preg_match('/^\d{2}:\d{2}$/', $hour)
    ) {
      try {
        $this->merge([
          'scheduled_at' => Carbon::createFromFormat('d/m/Y H:i', "$date $hour")->format('Y-m-d H:i:s'),
        ]);
      } catch (\Throwable) {
        // deixa a validação falhar depois
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
   * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
   */
  public function rules(): array
  {
    return [
      'scheduled_at' => 'required|date|date_format:Y-m-d H:i:s|after_or_equal:today',
      'notes' => 'nullable|string',
      'client' => 'required|uuid|exists:clients,uuid',
      'user' => 'required|uuid|exists:users,uuid',
    ];
  }

  public function messages(): array
  {
    return [
      'scheduled_at.required' => 'A :attribute é obrigatória.',
      'scheduled_at.date' => 'Dados inválidos.',
      'scheduled_at.date_format' => 'Os dados no campo :attribute são inválidos.',
      'scheduled_at.after_or_equal' => 'Não é possível agendar para uma data no passado.',
      'notes.string' => 'Dados inválidos.',
      'client.required' => 'É obrigatório informar um :attribute.',
      'client.uuid' => 'Dados inválidos.',
      'client.exists' => 'o :attribute informado não está cadastrado.',
      'user.required' => 'É obrigatório informar um :attribute.',
      'user.uuid' => 'Dados inválidos.',
      'user.exists' => 'o :attribute informado não está cadastrado.',
    ];
  }

  public function attributes(): array
  {
    return [
      'scheduled_at' => 'data de agendamento',
      'notes' => 'observação',
      'client' => 'cliente',
      'user' => 'funcionário',
    ];
  }

  protected function passedValidation(): void
  {
    $this->request->remove('scheduled_hour');
  }
}
