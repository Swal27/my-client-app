<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/clients/{slug}', [App\Http\Controllers\MyClientController::class, 'show']);
Route::post('/clients', [App\Http\Controllers\MyClientController::class, 'store']);
Route::put('/clients/{id}', [App\Http\Controllers\MyClientController::class, 'update']);
Route::patch('/clients/{id}', [App\Http\Controllers\MyClientController::class, 'update']);
Route::delete('/clients/{id}', [App\Http\Controllers\MyClientController::class, 'destroy']);