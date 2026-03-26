<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Password;

class SendResetPasswordEmail implements ShouldQueue
{
  use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

  public int $tries = 10;
  public int $backoff = 10;

  /**
   * Create a new job instance.
   */
  public function __construct(public string $email)
  {
    //
  }

  /**
   * Execute the job.
   */
  public function handle(): void
  {
    Password::sendResetLink([
      'email' => $this->email,
    ]);
  }
}
