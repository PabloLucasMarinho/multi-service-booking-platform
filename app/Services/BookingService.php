<?php

namespace App\Services;

use App\Enums\AppointmentStatus;
use App\Enums\DiscountType;
use App\Http\Requests\StoreAppointmentServiceRequest;
use App\Models\Appointment;
use App\Models\AppointmentService;
use App\Models\Company;
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

      $user = auth()->user();
      $canDiscount = $user->can_apply_manual_discount || $user->role->name === 'owner';

      $appointmentService = new AppointmentService([
        'appointment_uuid' => $appointment->uuid,
        'service_uuid' => $service->uuid,
        'original_price' => $service->price,
        'manual_discount_type' => $canDiscount ? ($request->manual_discount_type ?: null) : null,
        'manual_discount_value' => $canDiscount ? ($request->manual_discount_value ?: null) : null,
      ]);

      $promotion = Promotion::activeAt($appointment->scheduled_at)
        ->where(function ($query) use ($service) {
          $query->whereHas('categories', function ($q) use ($service) {
            $q->whereIn('categories.uuid', $service->categories->pluck('uuid'));
          })
            ->orWhereDoesntHave('categories');
        })
        ->get()
        ->sortByDesc(function ($promo) use ($service) {
          return $promo->type === DiscountType::Percentage
            ? $service->price * ($promo->value / 100)
            : $promo->value;
        })
        ->first();

      if ($promotion) {
        $promotionAmount = $promotion->type === DiscountType::Percentage
          ? round($service->price * ($promotion->value / 100), 2)
          : min($promotion->value, $service->price);

        $appointmentService->promotion_uuid = $promotion->uuid;
        $appointmentService->promotion_amount_snapshot = $promotionAmount;
      }

      $maxDiscount = Company::first()?->max_discount_percentage;
      $appointmentService->applyDiscount($maxDiscount);
      $appointmentService->save();
    });
  }
}
