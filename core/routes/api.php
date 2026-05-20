<?php

use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;

Route::post('/broadcast', [NotificationController::class, 'broadcast']);
Route::get('/subscriber/{id}/history', [NotificationController::class, 'history']);