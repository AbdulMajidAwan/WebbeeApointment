<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\SlotsController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
Route::get('getAllSlots', [SlotsController::class, 'getAllSlots'])->name('getAllSlots');
Route::post('appointments_create', [AppointmentController::class, 'create'])->name('appointments_create');
//Route::get('slots', 'App\Http\Controllers\SlotsController@getAllSlots');
//Route::post('create_appointment', 'App\Http\Controllers\AppointmentController@save');
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
