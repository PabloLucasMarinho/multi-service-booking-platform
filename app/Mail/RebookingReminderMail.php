<?php

namespace App\Mail;

use App\Models\Client;
use App\Models\Company;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RebookingReminderMail extends Mailable
{
  use Queueable, SerializesModels;

  public function __construct(
    public Client $client,
  )
  {
  }

  public function envelope(): Envelope
  {
    $company = Company::first();

    return new Envelope(
      from: new Address(
        $company?->email ?? config('mail.from.address'),
        $company?->fantasy_name ?? $company?->name ?? config('mail.from.name'),
      ),
      subject: 'Está na hora de agendar novamente!',
    );
  }

  public function content(): Content
  {
    return new Content(
      view: 'emails.rebooking-reminder',
    );
  }

  public function attachments(): array
  {
    return [];
  }
}
