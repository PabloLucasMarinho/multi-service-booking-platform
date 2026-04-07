<?php

use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\AppointmentPaymentController;
use App\Http\Controllers\AppointmentServiceController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\PromotionController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/home');

Auth::routes();

Route::middleware('auth')->group(function () {
  Route::get('/home', [HomeController::class, 'index'])->name('home');

  // CLIENTS
  Route::resource('clients', ClientController::class);

  // USERS
  Route::resource('users', UserController::class);
  Route::delete('users/{uuid}/anonymize', [UserController::class, 'anonymize'])
    ->name('users.anonymize');
  Route::put('users/{uuid}/restore', [UserController::class, 'restore'])
    ->name('users.restore');

  //SERVICES
  Route::resource('services', ServiceController::class);

  //CATEGORIES
  Route::post('categories', [CategoryController::class, 'store'])
    ->name('categories.store');
  Route::delete('categories/{category:slug}',
    [CategoryController::class, 'destroy']
  )->name('categories.destroy');

  // PROMOTIONS
  Route::resource('promotions', PromotionController::class);

  // APPOINTMENTS
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
  Route::post('appointments/{appointment}/payments',
    [AppointmentPaymentController::class, 'store']
  )->name('appointment-payments.store');
  Route::delete('appointment-payments/{appointmentPayment}',
    [AppointmentPaymentController::class, 'destroy']
  )->name('appointment-payments.destroy');
  Route::get('appointments/{appointment}/receipt',
    [AppointmentController::class, 'receipt']
  )->name('appointments.receipt');
  Route::patch('appointments/{appointment}/restore',
    [AppointmentController::class, 'restore']
  )->name('appointments.restore');

  // COMPANY
  Route::get('company', [CompanyController::class, 'show'])->name('company.index');
  Route::post('company', [CompanyController::class, 'store'])->name('company.save');

  // SETTINGS
  Route::get('settings', [SettingsController::class, 'show'])->name('settings.index');
  Route::post('settings', [SettingsController::class, 'store'])->name('settings.save');

  //REPORTS
  Route::get('reports', [ReportController::class, 'index'])
    ->name('reports.index')
    ->middleware('can:viewReports');
});

// NOTIFICATIONS
Route::get('/notifications/unsubscribe/{token}', [NotificationController::class, 'unsubscribe'])
  ->name('notifications.unsubscribe');
