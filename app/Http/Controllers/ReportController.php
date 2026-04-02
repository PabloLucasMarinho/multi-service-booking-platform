<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\AppointmentService;
use App\Models\Client;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
  public function index(Request $request)
  {
    $from = $request->from
      ? Carbon::createFromFormat('d/m/Y', $request->from)->startOfDay()
      : now()->startOfMonth();

    $to = $request->to
      ? Carbon::createFromFormat('d/m/Y', $request->to)->endOfDay()
      : now()->endOfMonth();

    return view('reports.index', [
      'from' => $from,
      'to' => $to,
      'financial' => $this->financialData($from, $to),
      'operational' => $this->operationalData($from, $to),
      'clients' => $this->clientsData($from, $to),
      'promotions' => $this->promotionsData($from, $to),
    ]);
  }

  private function financialData(Carbon $from, Carbon $to): array
  {
    $completedAppointments = Appointment::whereBetween('scheduled_at', [$from, $to])
      ->where('status', 'completed')
      ->with('appointmentServices')
      ->get();

    $totalRevenue = $completedAppointments->sum(fn($a) => $a->total);
    $totalAppointments = $completedAppointments->count();
    $avgTicket = $totalAppointments > 0 ? $totalRevenue / $totalAppointments : 0;

    $byEmployee = User::whereHas('appointments', fn($q) => $q
      ->whereBetween('scheduled_at', [$from, $to])
      ->where('status', 'completed')
    )
      ->with(['appointments' => fn($q) => $q
        ->whereBetween('scheduled_at', [$from, $to])
        ->where('status', 'completed')
        ->with('appointmentServices')
      ])
      ->get()
      ->map(function ($user) {
        $revenue = $user->appointments->sum(fn($a) => $a->total);
        $count = $user->appointments->count();
        return [
          'name' => $user->name,
          'count' => $count,
          'revenue' => $revenue,
          'avg_ticket' => $count > 0 ? $revenue / $count : 0,
        ];
      });

    return compact('totalRevenue', 'totalAppointments', 'avgTicket', 'byEmployee');
  }

  private function operationalData(Carbon $from, Carbon $to): array
  {
    $appointments = Appointment::whereBetween('scheduled_at', [$from, $to])->get();
    $total = $appointments->count();
    $cancelled = $appointments->where('status', 'cancelled')->count();
    $noShow = $appointments->where('status', 'no_show')->count();

    $cancellationRate = $total > 0 ? round(($cancelled / $total) * 100, 1) : 0;
    $noShowRate = $total > 0 ? round(($noShow / $total) * 100, 1) : 0;

    $topServices = AppointmentService::select('service_uuid', DB::raw('COUNT(*) as total'))
      ->whereHas('appointment', fn($q) => $q
        ->whereBetween('scheduled_at', [$from, $to])
      )
      ->with('service:uuid,name')
      ->groupBy('service_uuid')
      ->orderByDesc('total')
      ->limit(10)
      ->get();

    return compact('total', 'cancelled', 'noShow', 'cancellationRate', 'noShowRate', 'topServices');
  }

  private function clientsData(Carbon $from, Carbon $to): array
  {
    $totalClients = Client::count();

    $newClients = Client::whereBetween('created_at', [$from, $to])->count();

    $inactiveClients = Client::whereDoesntHave('appointments', fn($q) => $q
      ->where('scheduled_at', '>=', now()->subDays(30))
    )->count();

    $topClients = Client::withCount(['appointments as visit_count' => fn($q) => $q
      ->whereBetween('scheduled_at', [$from, $to])
      ->where('status', 'completed')
    ])
      ->withSum(['appointmentServices as total_spent' => fn($q) => $q
        ->whereHas('appointment', fn($q2) => $q2
          ->whereBetween('scheduled_at', [$from, $to])
          ->where('status', 'completed')
        )
      ], 'final_price')
      ->having('visit_count', '>', 0)
      ->orderByDesc('visit_count')
      ->limit(10)
      ->get()
      ->map(function ($client) use ($from, $to) {
        $lastAppointment = $client->appointments()
          ->whereBetween('scheduled_at', [$from, $to])
          ->where('status', 'completed')
          ->latest('scheduled_at')
          ->first();

        return [
          'name' => $client->name,
          'visits' => $client->visit_count,
          'last_visit' => $lastAppointment?->scheduled_at->format('d/m/Y'),
          'total_spent' => $client->total_spent ?? 0,
        ];
      });

    return compact('totalClients', 'newClients', 'inactiveClients', 'topClients');
  }

  private function promotionsData(Carbon $from, Carbon $to): array
  {
    $appointmentServicesWithPromotion = AppointmentService::whereNotNull('promotion_uuid')
      ->whereHas('appointment', fn($q) => $q
        ->whereBetween('scheduled_at', [$from, $to])
        ->where('status', 'completed')
      );

    $totalDiscount = (clone $appointmentServicesWithPromotion)->sum('promotion_amount_snapshot');
    $totalWithPromotion = (clone $appointmentServicesWithPromotion)->count();

    $totalServices = AppointmentService::whereHas('appointment', fn($q) => $q
      ->whereBetween('scheduled_at', [$from, $to])
      ->where('status', 'completed')
    )->count();

    $promotionRate = $totalServices > 0 ? round(($totalWithPromotion / $totalServices) * 100, 1) : 0;

    $byPromotion = AppointmentService::select(
      'promotion_uuid',
      DB::raw('COUNT(*) as uses'),
      DB::raw('SUM(promotion_amount_snapshot) as total_discount')
    )
      ->whereNotNull('promotion_uuid')
      ->whereHas('appointment', fn($q) => $q
        ->whereBetween('scheduled_at', [$from, $to])
        ->where('status', 'completed')
      )
      ->with('promotion:uuid,name')
      ->groupBy('promotion_uuid')
      ->orderByDesc('total_discount')
      ->get();

    return compact('totalDiscount', 'totalWithPromotion', 'promotionRate', 'byPromotion');
  }
}
