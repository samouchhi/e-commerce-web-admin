<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class CustomerChangePasswordController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        abort_unless($request->user() instanceof Customer, 403);
        $credentials = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => [
                'bail', 'required', 'string', 'min:8', 'confirmed', 'different:current_password',
                function (string $attribute, mixed $value, Closure $fail): void {
                    if (strlen($value) > 72 || str_contains($value, "\0")) {
                        $fail('The password must be at most 72 bytes and must not contain null characters.');
                    }
                },
            ],
        ]);

        DB::transaction(function () use ($request, $credentials): void {
            $customer = Customer::whereKey($request->user()->getKey())->lockForUpdate()->firstOrFail();
            if (! Hash::check($credentials['current_password'], $customer->password)) {
                throw ValidationException::withMessages(['current_password' => ['Your current password is incorrect.']]);
            }
            $customer->forceFill(['password' => $credentials['password']])->save();
            $customer->tokens()->delete();
        });

        return response()->json(['message' => 'Your password has been changed. Please sign in again.']);
    }
}
