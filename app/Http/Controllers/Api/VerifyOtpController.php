<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class VerifyOtpController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'code' => ['required', 'digits:6'],
        ]);

        $customer = Customer::where('email', $data['email'])->firstOrFail();

        if (
            $customer->email_verified_at !== null
            || $customer->otp_code === null
            || $customer->otp_expires_at?->isPast()
            || ! Hash::check($data['code'], $customer->otp_code)
        ) {
            return response()->json([
                'message' => 'The verification code is invalid or expired.',
            ], 422);
        }

        $customer->email_verified_at = now();
        $customer->otp_code = null;
        $customer->otp_expires_at = null;
        $customer->save();

        return response()->json([
            'customer' => $customer,
            'token' => $customer->createToken(
                'api-token',
                [],
                now()->addDays(7),
            )->plainTextToken,
        ]);
    }

    public function index() {}

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
