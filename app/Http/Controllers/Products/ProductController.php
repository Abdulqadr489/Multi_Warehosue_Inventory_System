<?php

namespace App\Http\Controllers\Products;

use App\Http\Controllers\Controller;
use App\Http\Requests\BaseList\BaseListRequest;
use App\Http\Requests\Products\CreateProductRequest;
use App\Http\Requests\Products\UpdateProductRequest;
use App\Models\Product\Product;
use App\Repositories\Traits\ApiResponse;
use App\Services\Products\ProductService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    use ApiResponse;

    public function __construct(protected ProductService $productService)
    {}

    /**
     * List All Products
     *
     * You can:
     * - filter using `search` (`name` `sku` `status`),
     * - sort using `sort_by` (`name` `sku` `status`),
     * - control direction with `sort_dir` (`asc`, `desc`),
     * - control page size with `per_page` (default 15).
     */
    public function index(BaseListRequest $request)
    {
        try {
            $perPage = $request->perPage();
            $filters = $request->validated();

            $products = $this->productService->list($filters, $perPage);
            return $this->success($products, 'Product fetched successfully.',200);
        }catch (\Exception $e){
            return $this->error("An unexpected error occurred",500, $e->getMessage(),);
        }
    }

    /**
     * Create New Product
     *
     * */
    public function store(CreateProductRequest $request)
    {
        try {
            $validated = $request->validated();
            $product = $this->productService->create($validated);
            return $this->success($product, 'Product created successfully.',201);
        }catch (\Exception $e){
            return $this->error("An unexpected error occurred",500, $e->getMessage());
        }

    }

    /**
     * show Product detail
     *
     * */
    public function show(Product $product)
    {
        $product = $this->productService->find($product->id);
        return $this->success($product, 'Product fetched successfully.',200);
    }


    /**
     * Update Product
     *
     * */
    public function update(UpdateProductRequest $request,Product $product)
    {
        try {
            $validated = $request->validated();
            $product = $this->productService->update($product,$validated);
            return $this->success($product, 'Product updated successfully.');
        }catch (\Exception $e){
            return $this->error("Error", 500 ,$e->getMessage());
        }
    }

    /**
     * Delete Product
     *
     * */
    public function destroy(Product $product)
    {
        try {
            $this->productService->delete($product);
            return $this->success($product, 'Product deleted successfully.');
        }catch (\Exception $e){
            return $this->error("Error", $e->getMessage());
        }
    }
}
