<?php

namespace App\Http\Controllers\Suppliers;

use App\Http\Controllers\Controller;
use App\Http\Requests\BaseList\BaseListRequest;
use App\Http\Requests\Suppliers\CreateSupplierRequest;
use App\Http\Requests\Suppliers\UpdateSupplierRequest;
use App\Models\Suppliers\Supplier;
use App\Repositories\Traits\ApiResponse;
use App\Services\Suppliers\SupplierService;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    use ApiResponse;

    public function __construct(protected SupplierService $supplierService)
    {

    }
    public function index(BaseListRequest $request)
    {
        try {
            $perPage = $request->perPage();
            $filters = $request->filters();
            $suppliers = $this->supplierService->list($filters, $perPage);

            return $this->success($suppliers, 'Suppliers fetched successfully.');
        }catch (\Exception $e){
            return $this->error($e->getMessage());
        }


    }

    public function create()
    {
        //
    }

    public function store(CreateSupplierRequest $request)
    {
        try {
            $validated = $request->validated();
            $supplier = $this->supplierService->create($validated);
            return $this->success($supplier, 'Supplier created successfully.');
        }catch (\Exception $e){
            return $this->error($e->getMessage());
        }
    }

    public function show(Supplier $supplier)
    {
        //
    }


    public function edit(Supplier $supplier)
    {
        //
    }

    public function update(UpdateSupplierRequest $request, Supplier $supplier)
    {
        try {
            $validated = $request->validated();
            $supplier = $this->supplierService->update($supplier, $validated);
            return $this->success($supplier, 'Supplier updated successfully.');
        }catch (\Exception $e){
            return $this->error($e->getMessage());
        }
    }

    public function destroy(Supplier $supplier)
    {
        try {
            $supplier = $this->supplierService->delete($supplier);
            return $this->success("Deleted",'Supplier deleted successfully.');
        }catch (\Exception $e){
            return $this->error($e->getMessage());
        }
    }
}
