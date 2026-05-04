<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $productId = $this->route('product')?->id;

        $rules = [
            'name'        => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'sku'         => ['required', 'string', Rule::unique('products', 'sku')->ignore($productId)],
            'unit'        => 'nullable|string|max:50',
            'price'       => 'nullable|numeric|min:0',
            'add_stock'   => 'nullable|integer|min:1',
            'stock_notes' => 'nullable|string|max:255',
        ];

        if ($this->user()->hasRole('admin')) {
            $rules['cost_price'] = 'nullable|numeric|min:0';
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'sku.unique' => 'This SKU is already used by another product.',
        ];
    }
}