<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAppointmentRequest;
use App\Http\Requests\UpdateAppointmentRequest;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\User;
use App\Services\AppointmentService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Throwable;

class AppointmentController extends Controller
{
  public function __construct(
    private AppointmentService $appointmentService
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
      $appointment = $this->appointmentService->create($request->validated());

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
    dd($appointment);
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
      $appointment = $this->appointmentService->update($request->validated(), $appointment);

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
    //
  }
}
