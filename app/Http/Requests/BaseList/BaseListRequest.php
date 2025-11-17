<?php

namespace App\Http\Requests\BaseList;

use Illuminate\Foundation\Http\FormRequest;

class BaseListRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'search'   => ['nullable', 'string', 'max:255'],
            'sort_by'  => ['nullable', 'string', 'max:50'],
            'sort_dir' => ['nullable', 'in:asc,desc'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }

    public function filters(): array
    {
        return $this->only(['search', 'sort_by', 'sort_dir']);
    }


    public function perPage(): int
    {
        $perPage = (int) $this->input('per_page', 15);

        if ($perPage < 1) {
            $perPage = 15;
        } elseif ($perPage > 100) {
            $perPage = 100;
        }

        return $perPage;
    }


}
