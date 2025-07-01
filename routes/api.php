<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ChipController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::post('/change-password', [AuthController::class, 'changePassword'])->middleware('auth:sanctum');

Route::apiResource('chips', ChipController::class)->middleware('auth:sanctum');

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
