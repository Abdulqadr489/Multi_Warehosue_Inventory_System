<?php

namespace App\Http\Controllers\Countries;

use App\Http\Controllers\Controller;
use App\Http\Requests\BaseList\BaseListRequest;
use App\Http\Requests\Countries\CreateCountryRequest;
use App\Http\Requests\Countries\UpdateCountryRequest;
use App\Http\Requests\Suppliers\CreateSupplierRequest;
use App\Models\Countries\Country;
use App\Repositories\Traits\ApiResponse;
use App\Services\Countries\CountryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CountryController extends Controller
{

    use ApiResponse;
    public function __construct(protected CountryService $countryService)
    {

    }
    /**
     * List All countries
     *
     * You can:
     * - filter using `search` (matches name or ISO code),
     * - sort using `sort_by` (`name`, `code`),
     * - control direction with `sort_dir` (`asc`, `desc`),
     * - control page size with `per_page` (default 15).
     */
    public function index(BaseListRequest $request):JsonResponse
    {
        try {
            $perPage = $request->perPage();
            $filters = $request->filters();

            $countries = $this->countryService->list($filters, $perPage);

            return $this->success($countries, 'Countries fetched successfully.',200);
        } catch (\Throwable $e) {
            \Log::error('Error listing Countries', [
                'error' => $e->getMessage(),
            ]);

            return $this->error('Failed to fetch Countries.', 500);
        }
    }

    /**
     * Create New Country
     *
     * */
    public function store(CreateCountryRequest $request)
    {
        try {
            $validated = $request->validated();
            $country=$this->countryService->create($validated);
            return $this->success($country,"Successfully Country Created",201);
        }catch (\Exception $exception){
            return $this->error("An unexpected error occurred",500,$exception->getMessage());
        }
    }

    /**
     * Update Country
     *
     * */
    public function update(UpdateCountryRequest $request, Country $country)
    {
        try {
            $validated = $request->validated();
            DB::beginTransaction();
            $country=$this->countryService->update($country,$validated);
            DB::commit();
            return $this->success($country,"Successfully Country Updated",200);
        }catch (\Exception $exception){
            DB::rollBack();
            return $this->error("An unexpected error occurred",500,$exception->getMessage());
        }
    }

    /**
     * Delete Country
     *
     * */
    public function destroy(Country $country)
    {
        try {
            $country=$this->countryService->delete($country);
            return $this->success($country,"Successfully Country Deleted",200);
        }catch (\Exception $exception){
            return $this->error("An unexpected error occurred",500,$exception->getMessage());

        }
    }
}
