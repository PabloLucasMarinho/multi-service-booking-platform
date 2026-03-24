<?php

namespace App\Services;

use App\Models\Promotion;
use Illuminate\Support\Facades\DB;

class PromotionService
{
  public function create(array $data): void
  {
    DB::transaction(function () use ($data) {
      Promotion::create([
        'name' => $data['name'],
        'type' => $data['type'],
        'value' => $data['value'],
        'starts_at' => $data['starts_at'],
        'ends_at' => $data['ends_at'],
      ]);
    });
  }

  public function update(array $data, Promotion $promotion): void
  {
    DB::transaction(function () use ($data, $promotion) {
      $promotion->update([
        'name' => $data['name'],
        'type' => $data['type'],
        'value' => $data['value'],
        'starts_at' => $data['starts_at'],
        'ends_at' => $data['ends_at'],
      ]);
    });
  }
}
