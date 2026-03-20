<?php

use App\Http\Controllers\ClientController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/home');

Auth::routes();

Route::middleware('auth')->group(function () {
  Route::get('/home', [HomeController::class, 'index'])->name('home');
  Route::resource('users', UserController::class);
  Route::delete('users/{uuid}/anonymize', [UserController::class, 'anonymize'])->name('users.anonymize');
  Route::put('users/{uuid}/restore', [UserController::class, 'restore'])->name('users.restore');
  Route::resource('clients', ClientController::class);
});
