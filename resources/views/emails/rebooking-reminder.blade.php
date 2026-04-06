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

    .cta-box {
      background: white;
      border: 1px solid #e0e0e0;
      border-radius: 8px;
      padding: 20px;
      margin: 16px 0;
      text-align: center;
    }

    .cta-text {
      font-size: 16px;
      color: #555;
      margin-bottom: 12px;
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
    <h1>Sentimos a sua falta!</h1>
  </div>
  <div class="body">
    <p>Olá, <strong>{{ $client->name }}</strong>!</p>
    <p>Percebemos que já faz algum tempo desde o seu último agendamento. Que tal marcar uma nova visita?</p>

    <div class="cta-box">
      <div class="cta-text">
        Estamos esperando por você. Entre em contato ou acesse nosso sistema para realizar um novo agendamento.
      </div>
    </div>

    <p>Ficamos felizes em atendê-lo novamente em breve!</p>
  </div>

  <div class="footer">
    <p>Você está recebendo este e-mail porque é nosso cliente.</p>
    <a class="unsubscribe"
       href="{{ url('/notifications/unsubscribe/' . $client->notification_token) }}">
      Cancelar recebimento de notificações
    </a>
  </div>
</div>
</body>
</html>
