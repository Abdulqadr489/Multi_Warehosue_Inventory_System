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
    public function index(BaseListRequest $request)
    {
        try {
            $perPage = $request->perPage();
            $filters = $request->validated();

            $products = $this->productService->list($filters, $perPage);
            return $this->success($products, 'Product fetched successfully.');
        }catch (\Exception $e){
            return $this->error("Error", $e->getMessage());
        }
    }


    public function store(CreateProductRequest $request)
    {
        try {
            $validated = $request->validated();
            $product = $this->productService->create($validated);
            return $this->success($product, 'Product created successfully.',201);
        }catch (\Exception $e){
            return $this->error("Error",500, $e->getMessage());
        }

    }


    public function show(Product $product)
    {
        $product = $this->productService->find($product->id);
        return $this->success($product, 'Product fetched successfully.');
    }



    public function update(UpdateProductRequest $request, Product $product)
    {
        try {
            $validated = $request->validated();
            $product = $this->productService->update($product,$validated);
            return $this->success($product, 'Product updated successfully.');
        }catch (\Exception $e){
            return $this->error("Error", 500 ,$e->getMessage());
        }
    }


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
