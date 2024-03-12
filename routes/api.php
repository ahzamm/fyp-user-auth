<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Controller;


Route::post('/user/register', [Controller::class, 'register']);
Route::post('/user/login', [Controller::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/user/logout', [Controller::class, 'logout']);
    Route::post('/user/logout-from-all-devices', [Controller::class, 'logoutFromAllDevices']);
    Route::get('/user/profile', [Controller::class, 'profile']);
    Route::post('/user/change-password', [Controller::class, 'changePassword']);
});