<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
{
    return [
        'name'                => ['required', 'string', 'max:255'],
        'sku'                 => ['required', 'string', 'max:100'],
        'category_id'         => ['nullable', 'exists:categories,id'],
        'price'               => ['nullable', 'numeric', 'min:0'],
        'cost_price'          => ['nullable', 'numeric', 'min:0'],
        'unit'                => ['required', 'string'],
        'low_stock_threshold' => ['required', 'integer', 'min:0'],
        'description'         => ['nullable', 'string'],

        // ── ADD THESE 4 LINES ──────────────────────────
        'production_cost'  => ['nullable', 'numeric', 'min:0'],
        'dispatch_price'   => ['nullable', 'numeric', 'min:0'],
        'mrp'              => ['nullable', 'numeric', 'min:0'],
        'commission_rate'  => ['nullable', 'numeric', 'min:0', 'max:100'],
    ];
}
}
