<?php

namespace App\Http\Controllers;

use App\Enums\PaymentMethod;
use App\Models\Appointment;
use App\Models\AppointmentPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rules\Enum;

class AppointmentPaymentController extends Controller
{
    public function store(Request $request, Appointment $appointment)
    {
        Gate::authorize('update', $appointment);

        abort_if(!$appointment->isEditable(), 403, 'Agendamento não pode ser editado.');
        abort_if($appointment->scheduled_at->isFuture(), 403, 'Agendamento ainda não ocorreu.');

        $request->merge([
            'amount' => str_replace(['.', ','], ['', '.'], $request->input('amount')),
        ]);

        $data = $request->validate([
            'amount'         => ['required', 'numeric', 'min:0.01'],
            'payment_method' => ['required', new Enum(PaymentMethod::class)],
        ]);

        $appointment->payments()->create($data);

        return redirect()
            ->route('appointments.show', $appointment)
            ->with('success', 'Pagamento admitido.');
    }

    public function destroy(AppointmentPayment $appointmentPayment)
    {
        Gate::authorize('update', $appointmentPayment->appointment);

        abort_if(!$appointmentPayment->appointment->isEditable(), 403);

        $appointmentPayment->delete();

        return redirect()
            ->route('appointments.show', $appointmentPayment->appointment)
            ->with('success', 'Pagamento removido.');
    }
}
