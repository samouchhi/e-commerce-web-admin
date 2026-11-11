<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;

class CustomerPasswordResetController extends Controller
{
    public function sendLink(Request $request): JsonResponse
    {
        $credentials = $request->validate(['email' => ['required', 'email']]);
        Password::broker('customers')->sendResetLink($credentials);

        return response()->json(['message' => 'If an account exists, a password reset link has been sent.']);
    }

    public function reset(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'token' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $status = DB::transaction(function () use ($credentials): string {
            Customer::where('email', $credentials['email'])->lockForUpdate()->first();

            return Password::broker('customers')->reset($credentials, function (Customer $customer, string $password): void {
                $customer->forceFill(['password' => $password])->save();
                $customer->tokens()->delete();
                event(new PasswordReset($customer));
            });
        });

        if ($status !== Password::PasswordReset) {
            return response()->json(['code' => 'invalid_reset_link', 'message' => 'This reset link is invalid or expired.'], 422);
        }

        return response()->json(['message' => 'Your password has been reset.']);
    }
}
