<?php

namespace App\Http\Controllers;

use App\Models\Client;

class NotificationController extends Controller
{
  public function unsubscribe(string $token)
  {
    $client = Client::where('notification_token', $token)->firstOrFail();

    $client->update(['notifications_enabled' => false]);

    return view('notifications.unsubscribed', compact('client'));
  }
}
