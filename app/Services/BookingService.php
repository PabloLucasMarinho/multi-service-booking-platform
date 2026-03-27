<?php

namespace App\Services;

use App\Enums\AppointmentStatus;
use App\Enums\DiscountType;
use App\Http\Requests\StoreAppointmentServiceRequest;
use App\Models\Appointment;
use App\Models\AppointmentService;
use App\Models\Promotion;
use App\Models\Service;
use Illuminate\Support\Facades\DB;

class BookingService
{
  public function appointmentCreate(array $data): mixed
  {
    return DB::transaction(function () use ($data) {
      return Appointment::create([
        'scheduled_at' => $data['scheduled_at'],
        'notes' => $data['notes'],
        'client_uuid' => $data['client'],
        'user_uuid' => $data['user'],
        'status' => AppointmentStatus::Scheduled,
      ]);
    });
  }

  public function appointmentUpdate(array $data, Appointment $appointment): mixed
  {
    return DB::transaction(function () use ($data, $appointment) {
      $appointment->update([
        'scheduled_at' => $data['scheduled_at'],
        'notes' => $data['notes'],
        'client_uuid' => $data['client'],
        'user_uuid' => $data['user'],
        'status' => $appointment->status,
      ]);

      return $appointment;
    });
  }

  public function addService(StoreAppointmentServiceRequest $request, Appointment $appointment): void
  {
    DB::transaction(function () use ($request, $appointment) {
      $service = Service::findOrFail($request->service_uuid);

      $appointmentService = new AppointmentService([
        'appointment_uuid' => $appointment->uuid,
        'service_uuid' => $service->uuid,
        'original_price' => $service->price,
        'manual_discount_type' => $request->manual_discount_type ?: null,
        'manual_discount_value' => $request->manual_discount_value ?: null,
      ]);

      $promotion = Promotion::active()
        ->whereHas('categories', function ($query) use ($service) {
          $query->whereIn('categories.uuid', $service->categories->pluck('uuid'));
        })
        ->orderByDesc('value')
        ->first();

      if ($promotion) {
        $promotionAmount = $promotion->type === DiscountType::Percentage
          ? round($service->price * ($promotion->value / 100), 2)
          : min($promotion->value, $service->price);

        $appointmentService->promotion_uuid = $promotion->uuid;
        $appointmentService->promotion_amount_snapshot = $promotionAmount;
      }

      $appointmentService->applyDiscount();
      $appointmentService->save();
    });
  }
}
