<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Cancelamento confirmado</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      display: flex;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
      margin: 0;
      background: #f5f5f5;
    }

    .card {
      background: white;
      border-radius: 8px;
      padding: 40px;
      text-align: center;
      max-width: 400px;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    h1 {
      color: #333;
      font-size: 22px;
    }

    p {
      color: #666;
    }
  </style>
</head>
<body>
<div class="card">
  <h1>✓ Cancelamento confirmado</h1>
  <p>Olá, <strong>{{ $client->name }}</strong>.</p>
  <p>Você não receberá mais notificações de promoções.</p>
</div>
</body>
</html>
