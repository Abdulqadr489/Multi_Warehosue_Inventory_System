<?php

namespace App\Http\Requests\BaseList;

use Illuminate\Foundation\Http\FormRequest;

class BaseListRequest extends FormRequest
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
            'search' => 'nullable|string',
            'sort_by' => 'nullable|string',
            'sort_dir' => 'nullable|string',
            'per_page' => 'nullable|integer',
        ];
    }

    public function listParams(): array
    {
        return [
            'search'   => $this->input('search',  ),
            'sort_by'  => $this->input('sort_by'),
            'sort_dir' => $this->input('sort_dir', 'asc'),
            'per_page' => $this->input('per_page', 10),
        ];
    }


}
