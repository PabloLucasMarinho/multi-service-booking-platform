<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAppointmentServiceRequest;
use App\Models\Appointment;
use App\Models\AppointmentService;
use App\Services\BookingService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Throwable;

class AppointmentServiceController extends Controller
{
  public function __construct(
    private BookingService $bookingService
  )
  {
  }

  public function store(StoreAppointmentServiceRequest $request, Appointment $appointment)
  {
    Gate::authorize('create', $appointment);

    try {
      $this->bookingService->addService($request, $appointment);

      return redirect()
        ->route('appointments.show', $appointment)
        ->with('success', 'Serviço adicionado.');

    } catch (Throwable $e) {
      Log::error('Erro ao adicionar serviço.', ['exception' => $e]);

      return back()->with('error', 'Erro ao adicionar serviço. Tente novamente.');
    }
  }

  public function destroy(AppointmentService $appointmentService)
  {
    Gate::authorize('update', $appointmentService->appointment);

    try {
      $appointment = $appointmentService->appointment;

      $appointmentService->delete();

      return redirect()
        ->route('appointments.show', $appointment)
        ->with('success', 'Serviço removido.');

    } catch (Throwable $e) {
      Log::error('Erro ao remover serviço.', ['exception' => $e]);

      return back()->with('error', 'Erro ao remover serviço. Tente novamente.');
    }
  }
}
