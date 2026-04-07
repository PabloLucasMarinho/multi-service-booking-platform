<?php

namespace App\Http\Controllers;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\AppointmentService;
use App\Models\Client;
use App\Models\User;

class HomeController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth');
  }

  public function index()
  {
    $month = now()->month;
    $year = now()->year;
    $user = auth()->user();
    $isOwner = $user->role->name === 'owner';

    $appointmentsQuery = Appointment::query()
      ->whereMonth('scheduled_at', $month)
      ->whereYear('scheduled_at', $year);

    if (!$isOwner) {
      $appointmentsQuery->where('user_uuid', $user->uuid);
    }

    $statusCounts = (clone $appointmentsQuery)
      ->selectRaw('status, COUNT(*) as status_count')
      ->groupBy('status')
      ->get()
      ->mapWithKeys(fn($item) => [$item->status->value => (int)$item->status_count]);

    $todayAppointments = Appointment::query()
      ->whereDate('scheduled_at', today())
      ->when(!$isOwner, fn($q) => $q->where('user_uuid', $user->uuid))
      ->with(['client', 'user'])
      ->orderBy('scheduled_at')
      ->get();

    $nextAppointment = $todayAppointments
      ->where('status', AppointmentStatus::Scheduled)
      ->where('scheduled_at', '>=', now())
      ->first();

    if ($nextAppointment) {
      $todayAppointments = collect([$nextAppointment])
        ->concat($todayAppointments->reject(fn($a) => $a->uuid === $nextAppointment->uuid));
    }

    $tomorrowAppointments = Appointment::query()
      ->whereDate('scheduled_at', today()->addDay())
      ->when(!$isOwner, fn($q) => $q->where('user_uuid', $user->uuid))
      ->with(['client', 'user'])
      ->orderBy('scheduled_at')
      ->get();

    $revenueQuery = AppointmentService::query()
      ->whereHas('appointment', fn($q) => $q
        ->whereMonth('scheduled_at', $month)
        ->whereYear('scheduled_at', $year)
        ->where('status', AppointmentStatus::Completed)
        ->when(!$isOwner, fn($q2) => $q2->where('user_uuid', $user->uuid))
      );

    $monthlyRevenue = $revenueQuery->sum('final_price');

    $myMonthlyRevenue = $isOwner ? AppointmentService::query()
      ->whereHas('appointment', fn($q) => $q
        ->whereMonth('scheduled_at', $month)
        ->whereYear('scheduled_at', $year)
        ->where('status', AppointmentStatus::Completed)
        ->where('user_uuid', $user->uuid)
      )
      ->sum('final_price') : null;

    $totalClients = $isOwner ? Client::count() : null;
    $totalEmployees = $isOwner ? User::whereHas('role', fn($q) => $q->whereIn('name', ['employee', 'admin']))->count() : null;

    return view('home', compact(
      'statusCounts',
      'todayAppointments',
      'nextAppointment',
      'tomorrowAppointments',
      'monthlyRevenue',
      'myMonthlyRevenue',
      'totalClients',
      'totalEmployees',
      'isOwner',
    ));
  }
}
