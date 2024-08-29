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

Route::get('hello', function () {
    return 'hello';
});
Route::post('register', [AuthController::class, 'signup']);



Route::get('/test-email', function () {
    $user = User::first(); // Or create a test user
    Mail::to($user->email)->send(new VerificationCodeMail('123456'));
    return 'Email sent!';
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
