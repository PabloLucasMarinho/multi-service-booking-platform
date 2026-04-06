<?php

namespace App\Console\Commands;

use App\Mail\RebookingReminderMail;
use App\Models\Client;
use App\Models\Company;
use App\Services\TwilioService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendRebookingReminders extends Command
{
  protected $signature = 'reminders:rebooking {--test : Usa o valor configurado em segundos ao invés de dias}';
  protected $description = 'Envia lembretes de reagendamento para clientes cujo último agendamento foi há X dias';

  public function handle(TwilioService $twilio): void
  {
    $company = Company::first();
    $reminderDays = $company?->rebooking_reminder_days;

    if (!$reminderDays) {
      $this->info('Lembrete de reagendamento desativado (rebooking_reminder_days não configurado).');
      return;
    }

    $isTest = $this->option('test');

    if ($isTest) {
      $targetDatetime = now()->subSeconds($reminderDays);

      $this->warn("[TESTE] Buscando clientes cujo último agendamento foi antes de {$targetDatetime}");

      $clients = Client::where('notifications_enabled', true)
        ->whereHas('appointments', fn($q) => $q->where('scheduled_at', '<=', $targetDatetime))
        ->whereDoesntHave('appointments', fn($q) => $q->where('scheduled_at', '>', $targetDatetime))
        ->get();
    } else {
      $targetDate = now()->subDays($reminderDays)->toDateString();

      $clients = Client::where('notifications_enabled', true)
        ->whereHas('appointments', fn($q) => $q->whereDate('scheduled_at', $targetDate))
        ->whereDoesntHave('appointments', fn($q) => $q->whereDate('scheduled_at', '>', $targetDate))
        ->get();
    }

    $this->info("Enviando lembretes para {$clients->count()} cliente(s)...");

//    $this->line('--- DEBUG ---');
//
//    // Verifica se o relacionamento existe
//    $anyClient = Client::whereHas('appointments')->first();
//    if ($anyClient) {
//      $appt = $anyClient->appointments()->latest()->first();
//      $this->line("Cliente: {$anyClient->name}");
//      $this->line("scheduled_at: {$appt->scheduled_at}");
//      $this->line("created_at: {$appt->created_at}");
//    } else {
//      $this->line("Nenhum cliente com agendamento encontrado (relacionamento pode estar faltando no model Client)");
//    }
//    $this->line('--- FIM DEBUG ---');


    foreach ($clients as $client) {
      if ($client->email) {
        Mail::to($client->email)->send(new RebookingReminderMail($client));
      }

      if ($client->phone) {
        $twilio->sendSms(
          $client->phone,
          "Ola, {$client->name}! Sentimos sua falta. Que tal agendar uma nova visita? Entre em contato conosco!"
        );
      }
    }

    $this->info('Lembretes enviados com sucesso!');
  }
}
