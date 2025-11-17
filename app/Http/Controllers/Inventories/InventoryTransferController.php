<?php

namespace App\Http\Controllers\Inventories;

use App\Http\Controllers\Controller;
use App\Http\Requests\InventoryTransfers\CreateInventoryTransferRequest;
use App\Repositories\Traits\ApiResponse;
use App\Services\Inventories\InventoryService;

class InventoryTransferController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected InventoryService $inventoryService,
    )
    {}

    public function store(CreateInventoryTransferRequest $request)
    {
        try {
            $validated = $request->validated();
            $user = auth('api')->user();
            if(!$user){
                return $this->error("Unauthenticated", 401);
            }

            $result = $this->inventoryService->transfer($validated, $user);

            return  $this->success($result,"Inventory Transfer created successfully",201);
        }catch (\Exception $exception){
            return $this->error("Error",500,$exception->getMessage());
        }
    }
}
