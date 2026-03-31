<?php

use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\AppointmentServiceController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PromotionController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/home');

Auth::routes();

Route::middleware('auth')->group(function () {
  Route::get('/home', [HomeController::class, 'index'])->name('home');

  Route::resource('clients', ClientController::class);

  Route::resource('users', UserController::class);
  Route::delete('users/{uuid}/anonymize', [UserController::class, 'anonymize'])
    ->name('users.anonymize');
  Route::put('users/{uuid}/restore', [UserController::class, 'restore'])
    ->name('users.restore');

  Route::resource('services', ServiceController::class);

  Route::post('categories', [CategoryController::class, 'store'])
    ->name('categories.store');
  Route::delete('categories/{category:slug}',
    [CategoryController::class, 'destroy']
  )->name('categories.destroy');

  Route::resource('promotions', PromotionController::class);

  Route::resource('appointments', AppointmentController::class);
  Route::get('monthly', [AppointmentController::class, 'monthly'])
    ->name('appointments.monthly');
  Route::post('appointments/{appointment}/services',
    [AppointmentServiceController::class, 'store']
  )->name('appointment-services.store');
  Route::delete('appointment-services/{appointmentService}',
    [AppointmentServiceController::class, 'destroy']
  )->name('appointment-services.destroy');
  Route::patch('appointments/{appointment}/complete',
    [AppointmentController::class, 'complete']
  )->name('appointments.complete');
  Route::get('appointments/{appointment}/receipt',
    [AppointmentController::class, 'receipt']
  )->name('appointments.receipt');
  Route::patch('appointments/{appointment}/restore',
    [AppointmentController::class, 'restore']
  )->name('appointments.restore');

  Route::get('company', [CompanyController::class, 'show'])->name('company.index');
  Route::post('company', [CompanyController::class, 'store'])->name('company.save');
});
