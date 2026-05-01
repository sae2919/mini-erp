<?php
// ─── StorePurchaseRequest ──────────────────────────────────────────────────────
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePurchaseRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'supplier_id'           => ['required', 'exists:suppliers,id'],
            'purchase_date'         => ['required', 'date', 'before_or_equal:today'],
            'notes'                 => ['nullable', 'string', 'max:500'],
            'items'                 => ['required', 'array', 'min:1'],
            'items.*.product_id'    => ['required', 'exists:products,id'],
            'items.*.quantity'      => ['required', 'integer', 'min:1', 'max:99999'],
            'items.*.cost_price'    => ['required', 'numeric', 'min:0.01', 'max:9999999'],
        ];
    }

    public function messages(): array
    {
        return [
            'items.required'              => 'Add at least one product line item.',
            'items.*.product_id.required' => 'Each line item must have a product.',
            'items.*.quantity.min'        => 'Quantity must be at least 1.',
            'items.*.cost_price.min'      => 'Cost price must be greater than zero.',
        ];
    }
}
