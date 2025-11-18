<?php

namespace App\Http\Requests\Inventories;

use App\Http\Requests\BaseList\BaseListRequest;
use Illuminate\Foundation\Http\FormRequest;

class InventoryGlobalViewRequest extends BaseListRequest
{

    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            'warehouse_id'   => ['nullable', 'integer', 'exists:warehouses,id'],
            'country_id'     => ['nullable', 'integer', 'exists:countries,id'],
            'warehouse_name' => ['nullable', 'string', 'max:255'],
            'country_name'   => ['nullable', 'string', 'max:255'],
        ];
    }

    public function filters(): array
    {
        return [
            'warehouse_id'   => $this->input('warehouse_id'),
            'country_id'     => $this->input('country_id'),
            'warehouse_name' => $this->input('warehouse_name'),
            'country_name'   => $this->input('country_name'),
        ];
    }
}
