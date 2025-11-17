<?php

namespace App\Http\Requests\Products;

use App\Models\Product\Product;
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
        $product = (new CreateProductRequest())->rules();

        $routeParam = $this->route('product')->id;
        $product['sku'] = [
            'string',
            'max:255',
            Rule::unique('products', 'sku')->ignore($routeParam)
        ];
        return $product;
    }
}
