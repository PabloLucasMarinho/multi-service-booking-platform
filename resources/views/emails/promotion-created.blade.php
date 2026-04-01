<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <style>
    body {
      font-family: Arial, sans-serif;
      font-size: 14px;
      color: #333;
      margin: 0;
      padding: 0;
    }

    .container {
      max-width: 600px;
      margin: 0 auto;
      padding: 32px 24px;
    }

    .header {
      background: #1a73e8;
      color: white;
      padding: 24px;
      border-radius: 8px 8px 0 0;
      text-align: center;
    }

    .header h1 {
      margin: 0;
      font-size: 22px;
    }

    .body {
      background: #f9f9f9;
      padding: 24px;
      border-radius: 0 0 8px 8px;
    }

    .promo-box {
      background: white;
      border: 1px solid #e0e0e0;
      border-radius: 8px;
      padding: 20px;
      margin: 16px 0;
    }

    .promo-name {
      font-size: 18px;
      font-weight: bold;
      color: #1a73e8;
    }

    .promo-detail {
      margin: 8px 0;
      font-size: 14px;
      color: #555;
    }

    .cta {
      text-align: center;
      margin: 24px 0;
    }

    .footer {
      text-align: center;
      font-size: 12px;
      color: #999;
      margin-top: 24px;
    }

    .unsubscribe {
      color: #999;
      font-size: 12px;
    }
  </style>
</head>
<body>
<div class="container">
  <div class="header">
    <h1>🎉 Nova Promoção Disponível!</h1>
  </div>
  <div class="body">
    <p>Olá, <strong>{{ $client->name }}</strong>!</p>
    <p>Temos uma novidade especial para você:</p>

    <div class="promo-box">
      <div class="promo-name">{{ $promotion->name }}</div>
      <div class="promo-detail">
        <strong>Desconto:</strong> {{ $promotion->value_formatted }}
      </div>
      <div class="promo-detail">
        <strong>Válido de:</strong> {{ $promotion->starts_at_formatted }}
        <strong>até</strong> {{ $promotion->ends_at_formatted }}
      </div>
      @if(!$promotion->isGlobal())
        <div class="promo-detail">
          <strong>Categorias:</strong>
          {{ $promotion->categories->pluck('name')->join(', ') }}
        </div>
      @endif
    </div>

    <p>Aproveite e agende agora mesmo!</p>
  </div>

  <div class="footer">
    <p>Você está recebendo este e-mail porque é nosso cliente.</p>
    <a class="unsubscribe"
       href="{{ url('/notifications/unsubscribe/' . $client->notification_token) }}">
      Cancelar recebimento de promoções
    </a>
  </div>
</div>
</body>
</html>
