<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Models\User;
use App\Mail\VerificationCodeMail;
use Illuminate\Support\Facades\Mail;

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


Route::prefix('v1/auth')->group(function () {
    Route::post('register', [AuthController::class, 'signup']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('confirmemail', [AuthController::class, 'confirmEmailVerification']);
    Route::post('resendemailvf', [AuthController::class, 'resendEmailVerification']);
});





Route::prefix('v1/auth')->middleware('auth:sanctum')->group(function () {
    Route::get('user', function (Request $request) {
        return $request->user();
    });

    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('resend2facode', [AuthController::class, 'resend2FACode']);
    Route::post('confirm2facode', [AuthController::class, 'confirm2FACode']);
    Route::post('refreshToken', [AuthController::class, 'refreshToken']);
});
