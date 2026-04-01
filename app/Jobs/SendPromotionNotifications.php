<?php

namespace App\Jobs;

use App\Mail\PromotionCreatedMail;
use App\Models\Client;
use App\Models\Promotion;
use App\Services\TwilioService;
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
    $twilioService = app(TwilioService::class);

    Client::where('notifications_enabled', true)
      ->each(function (Client $client) use ($twilioService) {
        if ($client->email) {
          Mail::to($client->email)
            ->send(new PromotionCreatedMail($this->promotion, $client));
        }

        if ($client->phone) {
          $twilioService->sendSms(
            $client->phone,
            $this->buildSmsMessage($client)
          );
        }
      });
  }

  private function buildSmsMessage(Client $client): string
  {
    $promotion = $this->promotion;
    $value = $promotion->value_formatted;
    $starts = $promotion->starts_at_formatted;
    $ends = $promotion->ends_at_formatted;

    return "Ola, {$client->name}! Nova promocao: {$promotion->name} com {$value} off. Valido ate {$ends}.";
  }
}
