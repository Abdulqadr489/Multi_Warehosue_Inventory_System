<?php

namespace App\Http\Controllers\Inventories;

use App\Http\Controllers\Controller;
use App\Http\Requests\BaseList\BaseListRequest;
use App\Http\Requests\InventoryTransfers\CreateInventoryTransferRequest;
use App\Models\Inventories\Inventory;
use App\Repositories\Traits\ApiResponse;
use App\Services\Inventories\InventoryService;
use Illuminate\Http\Request;

class InventoryTransferController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected InventoryService $inventoryService,
    )
    {}

    /**
     * Create new Transfer
     *
     * */
    public function store(CreateInventoryTransferRequest $request)
    {
        try {
            $validated = $request->validated();
            $user = auth('api')->user();
            $result = $this->inventoryService->transfer($validated, $user);

            return  $this->success($result,"Inventory Transfer created successfully",201);
        }catch (\Exception $exception){
            return $this->error("Error",500,$exception->getMessage());
        }
    }

}
