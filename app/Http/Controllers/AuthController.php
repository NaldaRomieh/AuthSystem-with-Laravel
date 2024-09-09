<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use App\Mail\VerificationCodeMail;
use Illuminate\Support\Facades\Mail;
use App\Events\SignupEvent;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Sanctum\PersonalAccessToken;
use App\Http\Requests\SignupRequest;
use App\Mail\codeMailer;
use App\Services\AuthService;
use App\Traits\UploadFile;
use App\Traits\HandleRespons;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;



class AuthController extends Controller
{

    use UploadFile, HandleRespons;

    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    //signup 
    public function signup(SignupRequest $request)
    {
        //DB::enableQueryLog();
        $validator = $request->validated(); //use the custom request

        $user = $this->authService->createUser($validator);


        $profilePhotoPath = null;
        $profilePhotoPath = $this->handleFileUpload($request, 'profile_photo', 'profile_photos');

        $certificatePath = null;
        $certificatePath = $this->handleFileUpload($request, 'certificate', 'certificates');
        //dd(DB::getQueryLog());
        $user->profile_photo = $profilePhotoPath;
        $user->certificate = $certificatePath;
        $user->save();

        $token = $user->createToken('myappToken')->plainTextToken;
        event(new SignupEvent($user));

        return $this->successResponse([
            'user' => $user,
            'token' => $token,
        ], 'User registered successfully', 201);
    }


    ///// Re-Send email verfication code 

    public function resendEmailVerificationCode(Request $request)
    {
        $request->validate([
            'email' => 'required|exists:users,email',
        ]);

        $user = User::where('email', $request->email)->first();
        $verificationCode = Str::random(6);
        $cacheKey = 'email_verification_' . $user->id;
        Cache::put($cacheKey, $verificationCode, 10 * 60);

        event(new SignupEvent($user));

        return $this->successResponse(['message' => 'Verification code sent successfully.']);
    }


    //////////////////
    //login function 



    public function login(Request $request)
    {
        $request->validate([
            'identifier' => 'required',
            'password' => 'required|min:6',
        ]);

        $user = User::where('email', $request->identifier)
            ->orWhere('phone', $request->identifier)
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return $this->errorResponse('Invalid credentials', 401);
        }


        $token = $user->createToken('Personal Access Token')->plainTextToken;
        $code = rand(100000, 999999);
        Cache::put('2fa_code_' . $user->id, $code, now()->addMinutes(10));
        Mail::to($user->email)->send(new codeMailer($user, $code));

        return $this->successResponse([
            'user' => $user,
            'message' => '2FA code sent to your email.',
            'token' => $token,
            'code' => $code
        ], 'Login successful', 200);
    }

    ///// confirm email verification code 
    public function confirmEmailVerification(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'verification_code' => 'required'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || $user->email_verification_code !== $request->verification_code) {
            return $this->errorResponse('Invalid verification code.', 400);
        }

        if (now()->greaterThan($user->email_verification_expire)) {
            return $this->errorResponse('Verification code expired.', 400);
        }

        $user->email_verified_at = now();
        $user->email_verification_code = null;
        $user->email_verification_expire = null;
        $user->save();

        return $this->successResponse([], 'Email verified successfully.', 200);
    }
    /////
    ////Resend email verification 
    public function resendEmailVerification(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return $this->errorResponse('User not found.', 404);
        }

        $user->email_verification_code = Str::random(6);
        $user->email_verification_expire = now()->addMinutes(10);
        $user->save();

        event(new SignupEvent($user));

        return $this->successResponse([], 'Verification code resent.', 200);
    }
    ////
    ////log out function 
    public function logout(Request $request)
    {

        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully.']);
    }
    public function resend2FACode(Request $request)
    {
        $user = Auth::user();
        $code = rand(100000, 999999);
        Cache::put('2fa_code_' . $user->id, $code, now()->addMinutes(10));
        Mail::to($user->email)->send(new codeMailer($user, $code));

        return response()->json(['message' => '2FA code has been re-sent.']);
    }


    public function confirm2FACode(Request $request)
    {
        $request->validate([
            '2fa_code' => 'required|numeric',
        ]);

        $user = Auth::user();
        $cachedCode = Cache::get('2fa_code_' . $user->id);

        if ($cachedCode && $request->input('2fa_code') == $cachedCode) {
            Cache::forget('2fa_code_' . $user->id);

            return response()->json(['message' => '2FA verification successful.']);
        }

        return response()->json(['message' => 'Invalid 2FA code.'], 422);
    }


    public function refreshToken(Request $request)
    {
        $newToken = Auth::refresh();

        return response()->json([
            'access_token' => $newToken,
            'token_type' => 'Bearer',
            'expires_in' => config('sanctum.expiration') * 60
        ]);
    }
}
