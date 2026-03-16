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
  Route::resource('clients', ClientController::class);
});
