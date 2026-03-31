<?php

namespace Database\Seeders;

use App\Enums\DiscountType;
use App\Models\Appointment;
use App\Models\AppointmentService;
use App\Models\Promotion;
use App\Models\Service;
use Illuminate\Database\Seeder;

class AppointmentServiceSeeder extends Seeder
{
  public function run(): void
  {
    $appointments = Appointment::all();
    $services = Service::with('categories')->get();
    $promotions = Promotion::with('categories')->get();

    $discountPatterns = [
      null,
      null,
      null,
      ['type' => DiscountType::Percentage, 'value' => 10],
      ['type' => DiscountType::Percentage, 'value' => 15],
      ['type' => DiscountType::Fixed, 'value' => 5],
      ['type' => DiscountType::Fixed, 'value' => 10],
    ];

    foreach ($appointments as $index => $appointment) {
      $serviceCount = ($index % 3) + 1;
      $shuffledServices = $services->shuffle()->take($serviceCount);

      foreach ($shuffledServices as $service) {
        $promotion = $promotions->first(function ($promo) use ($service) {
          $promoCategoryUuids = $promo->categories->pluck('uuid');
          return $service->categories->pluck('uuid')->intersect($promoCategoryUuids)->isNotEmpty();
        });

        $promotionAmount = null;
        $promotionUuid = null;

        if ($promotion) {
          $promotionAmount = $promotion->type === DiscountType::Percentage
            ? round($service->price * ($promotion->value / 100), 2)
            : min((float)$promotion->value, (float)$service->price);
          $promotionUuid = $promotion->uuid;
        }

        $discount = $discountPatterns[$index % count($discountPatterns)];

        $appointmentService = new AppointmentService([
          'appointment_uuid' => $appointment->uuid,
          'service_uuid' => $service->uuid,
          'promotion_uuid' => $promotionUuid,
          'original_price' => $service->price,
          'manual_discount_type' => $discount['type'] ?? null,
          'manual_discount_value' => $discount['value'] ?? null,
          'promotion_amount_snapshot' => $promotionAmount,
        ]);

        $appointmentService->applyDiscount();
        $appointmentService->save();
      }
    }
  }
}
