<?php

namespace App\Services;

use Twilio\Rest\Client;

class TwilioService
{
  private Client $client;

  public function __construct()
  {
    $this->client = new Client(
      config('services.twilio.sid'),
      config('services.twilio.token'),
    );
  }

  public function sendSms(string $to, string $message): void
  {
    $this->client->messages->create(
      '+55' . preg_replace('/\D/', '', $to),
      [
        'from' => config('services.twilio.from'),
        'body' => $message,
      ]
    );
  }
}
