<?php

namespace App\Http\Controllers\Warehouses;

use App\Http\Controllers\Controller;
use App\Http\Requests\BaseList\BaseListRequest;
use App\Http\Requests\Countries\UpdateCountryRequest;
use App\Http\Requests\Warehouses\CreateWarehouseRequest;
use App\Http\Requests\Warehouses\UpdateWarehouseRequest;
use App\Models\Warehouses\Warehouse;
use App\Repositories\Traits\ApiResponse;
use App\Repositories\Warehouses\WarehouseRepository;
use App\Services\Warehouses\WarehouseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WarehouseController extends Controller
{
    use ApiResponse;

    public function  __construct(protected WarehouseService $warehouseService)
    {

    }
    public function index(BaseListRequest $request)
    {
        try {
            $perPage = $request->perPage();
            $filters = $request->filters();


            $warehouses = $this->warehouseService->list($filters, $perPage);

            return $this->success($warehouses, 'Warehouse fetched successfully.');
        } catch (\Exception $e) {
            // your error() signature is (message, status, errors)
            return $this->error('Error', 500, $e->getMessage());
        }
    }


    public function create()
    {
        //
    }

    public function store(CreateWarehouseRequest $request)
    {
        try {
            $validated = $request->validated();
            $warehouse = $this->warehouseService->create($validated);
            return $this->success($warehouse, 'Warehouse created successfully.');
        }catch (\Exception $e){
            DB::rollBack();
            return $this->error("Error", $e->getMessage());

        }
    }


    public function show(Warehouse $warehouse)
    {
        //
    }

    public function edit(Warehouse $warehouse)
    {
        //
    }


    public function update(UpdateWarehouseRequest $request, Warehouse $warehouse)
    {
        try {
            $validated = $request->validated();
            $warehouse = $this->warehouseService->update($warehouse, $validated);
            return $this->success($warehouse, 'Warehouse fetched successfully.');

        }catch (\Exception $e){
            return $this->error("Error", $e->getMessage());
        }
    }

    public function destroy(Warehouse $warehouse)
    {
        try {
            $warehouse = $this->warehouseService->delete($warehouse);
            return $this->success($warehouse, 'Warehouse fetched successfully.');
        }catch (\Exception $e){
            return $this->error("Error", $e->getMessage());
        }
    }
}
