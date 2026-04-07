<?php

namespace Database\Seeders;

use App\Enums\AppointmentStatus;
use App\Enums\PaymentMethod;
use App\Models\Appointment;
use App\Models\User;
use Illuminate\Database\Seeder;

class AppointmentPaymentSeeder extends Seeder
{
  public function run(): void
  {
    $adminUuid = User::where('email', 'admin@email.com')->value('uuid');

    // Cenários rotativos de pagamento para agendamentos concluídos
    // Cada cenário: array de parcelas (method, delta) + tip/discount_authorized_by
    // delta = valor a mais/menos em relação ao total
    $scenarios = [
      // 0 - Exato: pagamento único em dinheiro
      ['parcelas' => [['method' => PaymentMethod::Cash,   'delta' => 0]],    'tip' => null, 'auth' => null],
      // 1 - Exato: pagamento único em pix
      ['parcelas' => [['method' => PaymentMethod::Pix,    'delta' => 0]],    'tip' => null, 'auth' => null],
      // 2 - Exato: dois métodos (crédito + dinheiro), split 60/40
      ['parcelas' => [['method' => PaymentMethod::Credit, 'ratio' => 0.6], ['method' => PaymentMethod::Cash, 'ratio' => 0.4]], 'tip' => null, 'auth' => null],
      // 3 - Gorjeta de R$ 5,00 em dinheiro
      ['parcelas' => [['method' => PaymentMethod::Cash,   'delta' => 5]],    'tip' => 5.00, 'auth' => null],
      // 4 - Gorjeta de R$ 10,00: débito + dinheiro
      ['parcelas' => [['method' => PaymentMethod::Debit,  'ratio' => 0.5], ['method' => PaymentMethod::Cash, 'delta' => 10, 'ratio_rest' => true]], 'tip' => 10.00, 'auth' => null],
      // 5 - Desconto de R$ 10,00 em pix, autorizado pelo admin
      ['parcelas' => [['method' => PaymentMethod::Pix,    'delta' => -10]],  'tip' => null, 'auth' => $adminUuid],
      // 6 - Exato: pagamento único em débito
      ['parcelas' => [['method' => PaymentMethod::Debit,  'delta' => 0]],    'tip' => null, 'auth' => null],
      // 7 - Exato: pagamento único em pix
      ['parcelas' => [['method' => PaymentMethod::Pix,    'delta' => 0]],    'tip' => null, 'auth' => null],
      // 8 - Exato: dois métodos (crédito + débito), split 50/50
      ['parcelas' => [['method' => PaymentMethod::Credit, 'ratio' => 0.5], ['method' => PaymentMethod::Debit, 'ratio' => 0.5]], 'tip' => null, 'auth' => null],
      // 9 - Gorjeta de R$ 2,00 em dinheiro
      ['parcelas' => [['method' => PaymentMethod::Cash,   'delta' => 2]],    'tip' => 2.00, 'auth' => null],
      // 10 - Desconto de R$ 5,00 em dinheiro, autorizado pelo admin
      ['parcelas' => [['method' => PaymentMethod::Cash,   'delta' => -5]],   'tip' => null, 'auth' => $adminUuid],
    ];

    $completed = Appointment::where('status', AppointmentStatus::Completed)
      ->with('appointmentServices')
      ->orderBy('scheduled_at')
      ->get();

    foreach ($completed as $i => $appointment) {
      $scenario = $scenarios[$i % count($scenarios)];
      $total    = $appointment->total;

      if ($total <= 0) {
        continue;
      }

      $appointmentUpdates = ['status' => AppointmentStatus::Completed];

      foreach ($scenario['parcelas'] as $parcela) {
        $amount = $this->resolveAmount($parcela, $total, $scenario);
        $amount = max(0.01, round($amount, 2));

        $appointment->payments()->create([
          'amount'         => $amount,
          'payment_method' => $parcela['method']->value,
        ]);
      }

      if ($scenario['tip'] !== null) {
        $appointmentUpdates['tip'] = $scenario['tip'];
      } elseif ($scenario['auth'] !== null) {
        $discount = abs($scenario['parcelas'][0]['delta'] ?? 0);
        $appointmentUpdates['closing_discount']      = $discount;
        $appointmentUpdates['discount_authorized_by'] = $scenario['auth'];
      }

      $appointment->updateQuietly($appointmentUpdates);
    }
  }

  private function resolveAmount(array $parcela, float $total, array $scenario): float
  {
    // Pagamento com delta fixo (a mais ou a menos sobre o total)
    if (isset($parcela['delta']) && !isset($parcela['ratio_rest'])) {
      return $total + $parcela['delta'];
    }

    // Pagamento como fração do total
    if (isset($parcela['ratio'])) {
      return $total * $parcela['ratio'];
    }

    // Parcela restante após as outras (usado no cenário de gorjeta com dois métodos)
    if (isset($parcela['ratio_rest'])) {
      return ($total * 0.5) + ($scenario['tip'] ?? 0);
    }

    return $total;
  }
}
