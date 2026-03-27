<?php

namespace App\Http\Controllers;

use App\Enums\AppointmentStatus;
use App\Http\Requests\StoreAppointmentRequest;
use App\Http\Requests\UpdateAppointmentRequest;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\Service;
use App\Models\User;
use App\Services\BookingService;
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
  public function index()
  {
    Gate::authorize('viewAny', Appointment::class);

    $appointments = Appointment::query()
      ->orderBy('scheduled_at')
      ->get();

    return view('appointments.index', compact('appointments'));
  }

  public function monthly()
  {
    Gate::authorize('viewAny', Appointment::class);

    $appointments = Appointment::select(
      DB::raw('DATE(scheduled_at) as date'),
      DB::raw('COUNT(*) as total')
    )
      ->whereMonth('scheduled_at', now()->month)
      ->whereYear('scheduled_at', now()->year)
      ->groupBy('date')
      ->orderBy('date')
      ->get();

    return view('appointments.monthly', compact('appointments'));
  }

  /**
   * Show the form for creating a new resource.
   */
  public function create()
  {
    Gate::authorize('create', Appointment::class);

    $users = User::select('uuid', 'name')
      ->orderByRaw("uuid = ? DESC", [auth()->user()->uuid])
      ->get();

    $clients = Client::select('uuid', 'name')
      ->orderBy('name')
      ->get();

    return view('appointments.create', compact('clients', 'users'));
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

    $appointment->load(['appointmentServices.service', 'appointmentServices.promotion', 'client', 'user']);

    $addedServiceUuids = $appointment->appointmentServices->pluck('service_uuid');

    $services = Service::select('uuid', 'name', 'price')
      ->whereNotIn('uuid', $addedServiceUuids)
      ->orderBy('name')
      ->get();

    return view('appointments.show', compact('appointment', 'services'));
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

  public function complete(Appointment $appointment)
  {
    Gate::authorize('update', $appointment);

    $appointment->update(['status' => AppointmentStatus::Completed]);

    return redirect()
      ->route('appointments.show', $appointment)
      ->with('success', 'Agendamento concluído.');
  }
}
