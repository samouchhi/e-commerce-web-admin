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
            'logistic_id' => ['nullable', 'exists:logistics,id'],
            'total_amount' => ['required', 'numeric'],
            'subtotal_amount' => ['required', 'numeric'],
            'shipping_cost' => ['required', 'numeric'],
            'payment_status' => ['sometimes', new Enum(PaymentStatus::class)],
            'shipping_status' => ['sometimes', new Enum(ShippingStatus::class)],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_variant_id' => ['required', 'integer', 'exists:product_variants,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ];
    }
}
