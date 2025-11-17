<?php

namespace App\Http\Requests\Warehouses;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateWarehouseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            "name" => ["required", "string", "max:255"],
            "location" => ["required", "string", "max:255"],
            "country_id" => [
                "required",
                "integer",
                Rule::exists('countries', 'id')->whereNull('deleted_at'),
            ],
        ];
    }
}
