<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <style>
    body {
      font-family: Arial, sans-serif;
      font-size: 12px;
      color: #333;
      margin: 40px;
    }

    h1 {
      font-size: 20px;
      margin-bottom: 4px;
    }

    h2 {
      font-size: 14px;
      color: #555;
      margin: 0 0 4px;
    }

    .header {
      border-bottom: 2px solid #333;
      padding-bottom: 12px;
      margin-bottom: 20px;
    }

    .section {
      margin-bottom: 16px;
    }

    .section-title {
      font-size: 11px;
      text-transform: uppercase;
      letter-spacing: 1px;
      color: #888;
      margin-bottom: 6px;
      border-bottom: 1px solid #eee;
      padding-bottom: 4px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 8px;
    }

    th {
      background: #f5f5f5;
      text-align: left;
      padding: 6px 8px;
      font-size: 11px;
      text-transform: uppercase;
      color: #666;
    }

    td {
      padding: 6px 8px;
      border-bottom: 1px solid #eee;
    }

    .text-right {
      text-align: right;
    }

    .total-row td {
      font-weight: bold;
      font-size: 13px;
      border-top: 2px solid #333;
      border-bottom: none;
    }

    .footer {
      margin-top: 40px;
      border-top: 1px solid #ccc;
      padding-top: 12px;
      font-size: 10px;
      color: #999;
      text-align: center;
    }

    .badge {
      display: inline-block;
      padding: 2px 8px;
      border-radius: 10px;
      font-size: 10px;
    }

    .badge-success {
      background: #d4edda;
      color: #155724;
    }
  </style>
</head>
<body>

<div class="header">
  <h1>{{ $company?->fantasy_name ?? $company?->name ?? 'Estabelecimento' }}</h1>
  @if($company?->document)
    <h2>CNPJ/CPF: {{ $company->document_formatted }}</h2>
  @endif
  @if($company?->phone)
    <h2>Tel: {{ $company->phone_formatted }}</h2>
  @endif
  @if($company?->address)
    <h2>{{ $company->address }}{{ $company->address_number ? ', ' . $company->address_number : '' }}
      — {{ $company->city }}/{{ $company->state }}</h2>
  @endif
</div>

<div style="text-align: center; margin-bottom: 20px;">
  <strong style="font-size: 16px;">RECIBO DE SERVIÇO</strong><br>
  <span style="font-size: 10px; color: #888;">Emitido em {{ now()->format('d/m/Y \à\s H:i') }}</span>
</div>

<div class="section">
  <div class="section-title">Dados do Atendimento</div>
  <table>
    <tr>
      <td><strong>Cliente:</strong> {{ $appointment->client->name }}</td>
      <td><strong>Data:</strong> {{ $appointment->scheduled_at_formatted }}</td>
    </tr>
    <tr>
      <td><strong>Atendido por:</strong> {{ $appointment->user->name }}</td>
      <td><strong>Status:</strong> {{ $appointment->status_formatted }}</td>
    </tr>
  </table>
</div>

<div class="section">
  <div class="section-title">Serviços Realizados</div>
  <table>
    <thead>
    <tr>
      <th>Serviço</th>
      <th class="text-right">Preço</th>
      <th class="text-right">Desconto</th>
      <th class="text-right">Total</th>
    </tr>
    </thead>
    <tbody>
    @foreach($appointment->appointmentServices as $item)
      <tr>
        <td>{{ $item->service->name }}</td>
        <td class="text-right">R$ {{ number_format($item->original_price, 2, ',', '.') }}</td>
        <td class="text-right">
          @php
            $discount = ($item->promotion_amount_snapshot ?? 0) + ($item->manual_discount_amount ?? 0);
          @endphp
          @if($discount > 0)
            - R$ {{ number_format($discount, 2, ',', '.') }}
          @else
            —
          @endif
        </td>
        <td class="text-right">R$ {{ number_format($item->final_price, 2, ',', '.') }}</td>
      </tr>
    @endforeach
    <tr class="total-row">
      <td colspan="3" class="text-right">Total</td>
      <td class="text-right">R$ {{ $appointment->formatted_total }}</td>
    </tr>
    </tbody>
  </table>
</div>

<div class="footer">
  Este recibo não possui valor fiscal. {{ $company?->name ?? '' }}
</div>

</body>
</html>
