<?php

namespace App\Mail;

use App\Models\Client;
use App\Models\Company;
use App\Models\Promotion;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PromotionCreatedMail extends Mailable
{
  use Queueable, SerializesModels;

  /**
   * Create a new message instance.
   */
  public function __construct(
    public Promotion $promotion,
    public Client    $client,
  )
  {
  }

  /**
   * Get the message envelope.
   */
  public function envelope(): Envelope
  {
    $company = Company::first();

    return new Envelope(
      from: new Address(
        $company?->email ?? config('mail.from.address'),
        $company?->fantasy_name ?? $company?->name ?? config('mail.from.name'),
      ),
      subject: 'Nova promoção: ' . $this->promotion->name,
    );
  }

  /**
   * Get the message content definition.
   */
  public function content(): Content
  {
    return new Content(
      view: 'emails.promotion-created',
    );
  }

  /**
   * Get the attachments for the message.
   *
   * @return array<int, Attachment>
   */
  public function attachments(): array
  {
    return [];
  }
}
