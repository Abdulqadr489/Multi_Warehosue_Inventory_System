<?php

namespace App\Http\Requests\Products;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateProductRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            'name'=>['required','string','max:100'],
            'sku'=>['required','string','max:100', Rule::unique('products','sku')->whereNull('deleted_at')],
            'status'=>['in:active,inactive','required'],
            'description'=>['nullable','string','max:100'],
            'price'=>['required','numeric'],


        ];
    }
}
