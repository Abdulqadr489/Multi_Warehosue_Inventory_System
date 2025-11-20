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
    {}

    /**
     * List All Suppliers
     *
     * You can:
     * - filter using `search` (`name` `address`),
     * - sort using `sort_by` (`name`, `code`),
     * - control direction with `sort_dir` (`asc`, `desc`),
     * - control page size with `per_page` (default 15).
     */
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

    /**
     * Create New Product
     *
     * */
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

    /**
     * Update Supplier
     *
     * */
    public function update(UpdateSupplierRequest $request, Supplier $supplier)
    {
        try {
            $validated = $request->validated();
            $supplier = $this->supplierService->update($supplier, $validated);
            return $this->success($supplier, 'Supplier updated successfully.',200);
        }catch (\Exception $e){
            return $this->error("An unexpected error occurred",500,$e->getMessage());
        }
    }

    /**
     * Delete Supplier
     *
     * */
    public function destroy(Supplier $supplier)
    {
        try {
            $supplier = $this->supplierService->delete($supplier);
            return $this->success($supplier,'Supplier deleted successfully',200);
        }catch (\Exception $e){
            return $this->error("An unexpected error occurred",500,$e->getMessage());
        }
    }
}
