<?php

namespace App\Http\Requests\Api;

use App\Models\Customer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SyncWishlistRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() instanceof Customer;
    }

    public function rules(): array
    {
        return [
            'operations' => ['required', 'array', 'min:1', 'max:100'],
            'operations.*' => ['required', 'array:id,action,product'],
            'operations.*.id' => ['required', 'uuid', 'distinct'],
            'operations.*.action' => ['required', Rule::in(['save', 'remove', 'merge'])],
            'operations.*.product' => ['required', 'array:id,name,product_code,images'],
            'operations.*.product.id' => ['required', 'integer', 'min:1'],
            'operations.*.product.name' => ['required', 'string', 'max:255'],
            'operations.*.product.product_code' => ['nullable', 'string', 'max:255'],
            'operations.*.product.images' => ['present', 'array', 'max:1'],
            'operations.*.product.images.*' => ['array:image_path,image_url,sort_order'],
            'operations.*.product.images.*.image_path' => ['nullable', 'string', 'max:2048', 'regex:~\A(?!.*\.\.)[a-zA-Z0-9_/.-]+\z~'],
            'operations.*.product.images.*.image_url' => ['nullable', 'url:http,https', 'max:2048'],
            'operations.*.product.images.*.sort_order' => ['sometimes', 'integer'],
        ];
    }
}
