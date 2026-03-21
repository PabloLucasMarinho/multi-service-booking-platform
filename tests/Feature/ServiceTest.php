<?php

use App\Models\Service;

it('formata o preço corretamente', function () {
  $service = Service::factory()->create(['price' => 35.00]);

  expect($service->price_formatted)->toBe('35,00');
});

it('formata o preço com centavos corretamente', function () {
  $service = Service::factory()->create(['price' => 35.50]);

  expect($service->price_formatted)->toBe('35,50');
});

it('formata o preço com milhar corretamente', function () {
  $service = Service::factory()->create(['price' => 1500.00]);

  expect($service->price_formatted)->toBe('1.500,00');
});
