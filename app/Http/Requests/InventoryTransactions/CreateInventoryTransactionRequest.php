<?php

namespace App\Http\Requests\InventoryTransactions;

use App\Enums\TransactionType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateInventoryTransactionRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'product_id' => [
                'required',
                'integer',
                Rule::exists('products', 'id')->whereNull('deleted_at'),
            ],
            'warehouse_id' => [
                'required',
                'integer',
                Rule::exists('warehouses', 'id')->whereNull('deleted_at'),
            ],
            'supplier_id' => [
                'nullable',
                'integer',
                Rule::exists('suppliers', 'id')->whereNull('deleted_at'),
            ],
            'quantity' => [
                'required',
                'numeric',
                'min:0.0001',
            ],
            'transaction_type' => [
                'required',
                Rule::in(TransactionType::values()),
            ],
            'date' => [
                'date',
                'nullable',
                'date',
            ],
            'minimum_quantity'=>[
                'nullable',
                'numeric',
                'min:0',
            ]

        ];
    }
}
