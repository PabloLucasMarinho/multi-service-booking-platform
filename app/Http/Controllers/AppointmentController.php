<?php

namespace App\Http\Controllers;

use App\Enums\AppointmentStatus;
use App\Enums\RoleNames;
use App\Http\Requests\StoreAppointmentRequest;
use App\Http\Requests\UpdateAppointmentRequest;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\Company;
use App\Models\Service;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Services\BookingService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Throwable;

class AppointmentController extends Controller
{
  public function __construct(
    private BookingService $bookingService
  )
  {
  }

  /**
   * Display a listing of the resource.
   */
  public function index(Request $request)
  {
    Gate::authorize('viewAny', Appointment::class);

    $appointments = Appointment::query()
      ->when($request->from, function ($query) use ($request) {
        $query->whereDate('scheduled_at', '>=', Carbon::createFromFormat('d/m/Y', $request->from));
      })
      ->when($request->to, function ($query) use ($request) {
        $query->whereDate('scheduled_at', '<=', Carbon::createFromFormat('d/m/Y', $request->to));
      })
      ->when($request->input('statuses'), function ($query) use ($request) {
        $query->whereIn('status', $request->input('statuses'));
      })
      ->with(['client', 'user'])
      ->orderBy('scheduled_at', 'asc')
      ->get();

    return view('appointments.index', compact('appointments'));
  }

  public function monthly(Request $request)
  {
    Gate::authorize('viewAny', Appointment::class);

    $month = $request->input('month', now()->month);
    $year = $request->input('year', now()->year);

    $appointments = Appointment::select(
      DB::raw('DATE(scheduled_at) as date'),
      DB::raw('status'),
      DB::raw('COUNT(*) as appointments_count')
    )
      ->whereMonth('scheduled_at', $month)
      ->whereYear('scheduled_at', $year)
      ->groupBy('date', 'status')
      ->orderBy('date')
      ->get();

    $appointmentsByDate = $appointments->groupBy('date')->map(fn($items) => $items->keyBy('status')->map(fn($item) => (int)$item->appointments_count)
    );

    if ($request->ajax()) {
      return response()->json($appointmentsByDate);
    }

    return view('appointments.monthly', compact('appointmentsByDate', 'month', 'year'));
  }

  /**
   * Show the form for creating a new resource.
   */
  public function create(Request $request)
  {
    Gate::authorize('create', Appointment::class);

    $users = User::select('uuid', 'name')
      ->orderByRaw("uuid = ? DESC", [auth()->user()->uuid])
      ->get();

    $clients = Client::select('uuid', 'name')
      ->orderBy('name')
      ->get();

    $selectedClient = $request->input('client');

    return view('appointments.create', compact(
      'clients',
      'selectedClient', 'users'
    ));
  }

  /**
   * Store a newly created resource in storage.
   */
  public function store(StoreAppointmentRequest $request)
  {
    Gate::authorize('create', Appointment::class);

    try {
      $appointment = $this->bookingService->appointmentCreate($request->validated());

      return redirect()
        ->route('appointments.show', $appointment)
        ->with('success', 'Agendamento realizado com sucesso!');
    } catch (Throwable $e) {
      Log::error('Erro ao agendar.', [
        'exception' => $e,
      ]);

      return back()
        ->withInput()
        ->with('error', 'Erro ao agendar. Tente novamente.');
    }
  }

  /**
   * Display the specified resource.
   */
  public function show(Appointment $appointment)
  {
    Gate::authorize('view', $appointment);

    $appointment->load(['appointmentServices.service', 'appointmentServices.promotion', 'client', 'user', 'createdBy.role', 'updatedBy.role', 'payments']);

    $addedServiceUuids = $appointment->appointmentServices->pluck('service_uuid');

    $services = Service::select('uuid', 'name', 'price')
      ->whereNotIn('uuid', $addedServiceUuids)
      ->orderBy('name')
      ->get();

    $company = Company::first();

    return view('appointments.show', compact('appointment', 'services', 'company'));
  }

  /**
   * Show the form for editing the specified resource.
   */
  public function edit(Appointment $appointment)
  {
    Gate::authorize('update', $appointment);

    $users = User::select('uuid', 'name')
      ->orderByRaw("uuid = ? DESC", [auth()->user()->uuid])
      ->get();

    $clients = Client::select('uuid', 'name')
      ->orderBy('name')
      ->get();

    return view('appointments.edit', compact('appointment', 'users', 'clients'));
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(UpdateAppointmentRequest $request, Appointment $appointment)
  {
    Gate::authorize('update', $appointment);

    try {
      $appointment = $this->bookingService->appointmentUpdate($request->validated(), $appointment);

      return redirect()
        ->route('appointments.show', $appointment)
        ->with('success', 'Agendamento atualizado com sucesso!');
    } catch (Throwable $e) {
      Log::error('Erro ao atualizar agendamento.', [
        'exception' => $e,
      ]);

      return back()
        ->withInput()
        ->with('error', 'Erro ao atualizar agendamento. Tente novamente.');
    }
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(Appointment $appointment)
  {
    Gate::authorize('delete', $appointment);

    $appointment->update(['status' => AppointmentStatus::Cancelled]);

    return redirect()
      ->route('appointments.index')
      ->with('success', 'Agendamento cancelado.');
  }

  public function complete(Request $request, Appointment $appointment)
  {
    Gate::authorize('update', $appointment);

    $appointment->load('payments');

    $totalPaid   = $appointment->total_paid;
    $total       = $appointment->total;
    $balance     = round($totalPaid - $total, 2);
    $authUser    = auth()->user();
    $isEmployee  = $authUser->role->name === RoleNames::Employee->value;

    $updates = ['status' => AppointmentStatus::Completed];

    if ($balance > 0) {
      // Gorjeta
      $updates['tip'] = $balance;

    } elseif ($balance < 0) {
      // Desconto no fechamento
      if ($isEmployee) {
        // Funcionário precisa de autorização de admin/owner
        $admin = User::where('email', $request->admin_email)
          ->whereHas('role', fn($q) => $q->whereIn('name', [
            RoleNames::Admin->value,
            RoleNames::Owner->value,
          ]))
          ->first();

        if (!$admin || !Hash::check($request->admin_password, $admin->password)) {
          return back()->with('error', 'Credenciais inválidas ou sem permissão para conceder desconto.');
        }

        $updates['discount_authorized_by'] = $admin->uuid;
      }

      $updates['closing_discount'] = abs($balance);
    }

    $appointment->update($updates);

    return redirect()
      ->route('appointments.show', $appointment)
      ->with('success', 'Agendamento concluído.');
  }

  public function restore(Appointment $appointment)
  {
    Gate::authorize('update', $appointment);

    $appointment->update(['status' => AppointmentStatus::Scheduled]);

    return redirect()
      ->route('appointments.show', $appointment)
      ->with('success', 'Agendamento reativado com sucesso!');
  }

  public function receipt(Appointment $appointment)
  {
    Gate::authorize('view', $appointment);

    $appointment->load([
      'appointmentServices.service',
      'appointmentServices.promotion',
      'client',
      'user',
      'payments',
    ]);

    $company = \App\Models\Company::first();

    $pdf = Pdf::loadView('appointments.receipt', compact('appointment', 'company'));

    return $pdf->download('recibo-' . $appointment->uuid . '.pdf');
  }
}
