<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class AuthService
{
    public function createUser(array $validatedData)
    {
        // we use service to create a user 
        $user = new User();
        $user->username = $validatedData['username'];
        $user->email = $validatedData['email'];
        $user->phone = $validatedData['phone'];
        //$user->profile_photo = $validatedData['profile_photo'];
        //$user->certificate = $validatedData['certificate'];
        $user->password = bcrypt($validatedData['password']);

        $verificationCode = Str::random(6);
        $user->email_verification_code = $verificationCode;
        $dateTime = new \DateTime();
        $dateTime->add(new \DateInterval('PT10M'));
        $user->email_verification_expire = $dateTime->format('Y-m-d H:i:s');
        //$user->email_verification_expire = now()->addMinutes(10);

        $user->save();

        Cache::put("verification_code_{$user->id}", $verificationCode, now()->addMinutes(10));
        return $user;
    }
}
