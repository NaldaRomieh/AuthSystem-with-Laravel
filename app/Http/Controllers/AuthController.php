<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Mail\VerificationCodeMail;
use Illuminate\Support\Facades\Mail;
use App\Events\SignupEvent;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Sanctum\PersonalAccessToken;


class AuthController extends Controller
{

    //signup 
    public function signup(Request $request)
    {

        $validator = $request->validate([
            'username' => 'required|unique:users',
            'email' => 'required|email|unique:users',
            'phone' => 'required',
            'profile_photo' => 'nullable|image',
            'certificate' => 'nullable|file|mimes:pdf',
            'password' => 'required|confirmed|min:8',
        ]);

        $user = User::create([
            'username' => $validator['username'],
            'email' => $validator['email'],
            'phone' => $validator['phone'],
            'profile_photo' => $validator['profile_photo'],
            'certificate' => $validator['certificate'],
            'password' => bcrypt($validator['password']),
            'email_verification_code' => Str::random(6),
            'email_verification_expire' => now()->addMinutes(10)
        ]);

        $profilePhotoPath = null;
        if ($request->hasFile('profile_photo')) {
            $profilePhotoPath = $request->file('profile_photo')->store('profile_photos', 'public');
        }

        $certificatePath = null;
        if ($request->hasFile('certificate')) {
            $certificatePath = $request->file('certificate')->store('certificates', 'public');
        }

        $user->save();
        $token = $user->createToken('myapptoken'); //??

        event(new SignupEvent($user));

        return response()->json(['message' => 'User registered successfully. Check your email for verification code.'], 201);
    }

    //////////////////
    //login function 

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email_or_phone' => 'required',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user = User::where('email', $request->email_or_phone)
            ->orWhere('phone_number', $request->email_or_phone)
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }


        if (!$user->email_verified_at) {
            return response()->json(['message' => 'Email not verified.'], 403);
        }

        $user->two_factor_code = rand(100000, 999999);
        $user->save();


        Mail::raw("Your 2FA code is: $user->two_factor_code", function ($message) use ($user) {
            $message->to($user->email)->subject('2FA Code');
        });

        return response()->json(['message' => '2FA code sent.']);
    }

    public function verify2FA(Request $request)
    {
        $request->validate([
            'email_or_phone' => 'required',
            'two_factor_code' => 'required',
        ]);

        $user = User::where('email', $request->email_or_phone)
            ->orWhere('phone_number', $request->email_or_phone)
            ->first();

        if ($user && $user->two_factor_code == $request->two_factor_code) {

            $user->two_factor_code = null;
            $user->save();


            $token = $user->createToken('API Token')->plainTextToken;

            return response()->json([
                'access_token' => $token,
                'token_type' => 'Bearer',
                'expires_in' => config('sanctum.expiration') * 60,
            ]);
        }

        return response()->json(['message' => 'Invalid 2FA code.'], 401);
    }
    ///////////////////////////////
    //logout function..

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json(['message' => 'Logged out successfully.']);
    }
    /////////////////////////////

    public function refreshToken(Request $request)
    {
        $request->validate([
            'refresh_token' => 'required',
        ]);

        $refreshToken = $request->input('refresh_token');
        $token = PersonalAccessToken::findToken($refreshToken);

        if (!$token || $token->created_at->addMinutes(config('sanctum.refresh_token_expiration'))->isPast()) {
            return response()->json(['message' => 'Refresh token is invalid or expired.'], 401);
        }


        $token->delete();


        $user = $token->tokenable;

        // Generate a new token 
        $newAccessToken = $user->createToken('API Token')->plainTextToken;

        return response()->json([
            'access_token' => $newAccessToken,
            'token_type' => 'Bearer',
            'expires_in' => config('sanctum.expiration') * 60, // was added in the config file 
        ]);
    }
}
