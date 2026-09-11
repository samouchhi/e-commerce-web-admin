<?php

namespace App\Http\Requests;

use App\Enums\PaymentStatus;
use App\Enums\ShippingStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // 'order_number' => ['required', 'string', 'unique:orders,order_number'],
            // 'customer_id' => ['required', 'exists:customers,id'],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'address' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'note' => ['nullable', 'string', 'max:1000'],
            'logistic_id' => ['nullable', 'exists:logistics,id'],
            'total_amount' => ['sometimes', 'numeric'],
            'subtotal_amount' => ['sometimes', 'numeric'],
            'shipping_cost' => ['sometimes', 'numeric'],
            'payment_status' => ['sometimes', new Enum(PaymentStatus::class)],
            'shipping_status' => ['sometimes', new Enum(ShippingStatus::class)],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_variant_id' => ['required', 'integer', 'exists:product_variants,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ];
    }
}
