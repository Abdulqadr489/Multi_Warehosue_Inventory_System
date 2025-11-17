<?php

namespace App\Http\Requests\InventoryTransfers;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateInventoryTransferRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => [
                'required',
                'integer',
                Rule::exists('products', 'id'),
            ],
            'from_warehouse_id' => [
                'required',
                'integer',
                Rule::exists('warehouses', 'id'),
            ],
            'to_warehouse_id' => [
                'required',
                'integer',
                'different:from_warehouse_id',
                Rule::exists('warehouses', 'id'),
            ],
            'quantity' => [
                'required',
                'numeric',
                'min:0.0001',
            ],
            'supplier_id' => [
                'nullable',
                'integer',
                Rule::exists('suppliers', 'id'),
            ],
            'date' => [
                'nullable',
                'date',
            ],

        ];
    }
}
