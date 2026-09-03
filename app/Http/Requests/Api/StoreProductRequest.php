<?php

namespace App\Http\Requests\Api;

use App\Enums\ProductStatusEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'product_code' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'unit_id' => ['nullable', 'integer', 'exists:units,id'],
            // 'status' => ['sometimes', new Enum(ProductStatusEnum::class)],
            // 'is_active' => ['sometimes', 'boolean'],
            'variants' => ['required', 'array'],
            'variants.*.name' => ['required', 'string', 'max:255'],
            'variants.*.price' => ['required_with:variants', 'numeric', 'min:0'],
            'variants.*.cost' => ['required_with:variants', 'numeric', 'min:0'],
            'variants.*.stock_qty' => ['required', 'integer', 'min:0'],
            'variants.*.is_active' => ['required', 'boolean'],
            'images' => ['sometimes', 'array'],
            'images.*.file' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'images.*.sort_order' => ['sometimes', 'integer'],
        ];
    }
}
