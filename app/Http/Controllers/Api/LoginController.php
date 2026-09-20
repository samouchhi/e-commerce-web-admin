<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Notifications\VerifyOtpNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $customer = Customer::where('email', $credentials['email'])->first();

        if ($customer === null || ! Hash::check($credentials['password'], $customer->password)) {
            return response()->json([
                'message' => 'The provided credentials are incorrect.',
            ], 401);
        }

        if ($customer->email_verified_at === null) {
            $otp = (string) random_int(100000, 999999);

            $customer->update([
                'otp_code' => Hash::make($otp),
                'otp_expires_at' => now()->addMinutes(10),
            ]);
            $customer->notify(new VerifyOtpNotification($otp));
            return response()->json([
                'message' => 'Your email is not verified. A new verification code has been sent to your email.',
                'email' => $customer->email,
            ], 403);
        }

        return response()->json([
            'customer' => $customer,
            'token' => $customer->createToken('api-token', [], now()->addDays(7))->plainTextToken,
        ]);
    }
}
