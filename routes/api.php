<?php

use App\Http\Controllers\ReservationController;
use Illuminate\Support\Facades\Route;

Route::apiResource('reservations', ReservationController::class)->only(['index', 'store']);
