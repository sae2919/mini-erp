<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $productId = $this->route('product')?->id;

        return [
            'category_id'          => ['required', 'exists:categories,id'],
            'name'                 => ['required', 'string', 'max:255'],
            'sku'                  => ['required', 'string', 'max:100',
                                       Rule::unique('products', 'sku')->ignore($productId)->whereNull('deleted_at')],
            'price'                => ['required', 'numeric', 'min:0', 'max:9999999'],
            'cost_price'           => ['required', 'numeric', 'min:0', 'max:9999999'],
            'low_stock_threshold'  => ['required', 'integer', 'min:0'],
            'unit'                 => ['required', 'string', 'max:20'],
            'description'          => ['nullable', 'string', 'max:1000'],
            'is_active'            => ['boolean'],
        ];
    }
}
