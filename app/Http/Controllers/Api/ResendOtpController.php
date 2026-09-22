<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Notifications\VerifyOtpNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ResendOtpController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $data = $request->validate(['email' => ['required', 'email']]);
        $customer = Customer::where('email', $data['email'])->firstOrFail();

        if ($customer->email_verified_at !== null) {
            return response()->json(['message' => 'This email has already been verified.'], 422);
        }

        $otp = (string) random_int(100000, 999999);

        $customer->update([
            'otp_code' => Hash::make($otp),
            'otp_expires_at' => now()->addMinutes(10),
        ]);
        $customer->notify(new VerifyOtpNotification($otp));

        return response()->json([
            'message' => 'Verification code sent.',
            'email' => $customer->email,
        ]);
    }
}
