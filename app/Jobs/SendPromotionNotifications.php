<?php

namespace App\Jobs;

use App\Mail\PromotionCreatedMail;
use App\Models\Client;
use App\Models\Promotion;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendPromotionNotifications implements ShouldQueue
{
  use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

  /**
   * Create a new job instance.
   */
  public function __construct(
    public Promotion $promotion,
  )
  {
  }

  /**
   * Execute the job.
   */
  public function handle(): void
  {
    Client::where('notifications_enabled', true)
      ->whereNotNull('email')
      ->each(function (Client $client) {
        Mail::to($client->email)
          ->send(new PromotionCreatedMail($this->promotion, $client));
      });
  }
}
