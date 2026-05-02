<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSaleRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'sale_date'              => ['required', 'date', 'before_or_equal:today'],
            'customer_name'          => ['nullable', 'string', 'max:255'],
            'notes'                  => ['nullable', 'string', 'max:500'],
            'items'                  => ['required', 'array', 'min:1'],
            'items.*.product_id'     => ['required', 'exists:products,id'],
            'items.*.quantity'       => ['required', 'integer', 'min:1', 'max:99999'],
            'items.*.selling_price'  => ['required', 'numeric', 'min:0.01', 'max:9999999'],
            'seller_id'                  => ['nullable', 'exists:users,id'],
'items.*.commission_rate'    => ['nullable', 'numeric', 'min:0', 'max:100'],
'items.*.dispatch_price'     => ['nullable', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'items.required'                  => 'Add at least one product line item.',
            'items.*.selling_price.min'        => 'Selling price must be greater than zero.',
        ];
    }
}
