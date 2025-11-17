<?php

namespace App\Http\Requests\Products;

use Illuminate\Foundation\Http\FormRequest;

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
            'sku'=>['required','string','max:100','unique:products,sku'],
            'status'=>['in:active,inactive','required'],
            'description'=>['nullable','string','max:100'],
            'price'=>['required','string','max:100'],


        ];
    }
}
