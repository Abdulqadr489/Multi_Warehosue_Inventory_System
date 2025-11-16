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
     * Display a listing of the resource.
     */
    public function index(BaseListRequest $request):JsonResponse
    {
        try {
            $validated = $request->listParams();


            $filters = $request->only(['search', 'sort_by', 'sort_dir']);

            $countries = $this->countryService->list($filters, $validated['per_page']);

            return $this->success($countries, 'Countries fetched successfully.');
        } catch (\Throwable $e) {
            \Log::error('Error listing countries', [
                'error' => $e->getMessage(),
            ]);

            return $this->error('Failed to fetch countries.', 500);
        }
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateCountryRequest $request)
    {
        try {
            DB::beginTransaction();
            $validated = $request->validated();
            $country=$this->countryService->create($validated);
            DB::commit();
            return $this->success($country,"Successfully Created","201");
        }catch (\Exception $exception){
            DB::rollBack();
            return $this->error($exception->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(UpdateCountryRequest $country)
    {
        //
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCountryRequest $request, Country $country)
    {
        try {
            $validated = $request->validated();
            DB::beginTransaction();
            $country=$this->countryService->update($country,$validated);
            DB::commit();
            return $this->success($country,"Successfully Updated","201");
        }catch (\Exception $exception){
            DB::rollBack();
            return $this->error($exception->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Country $country)
    {
        try {
            DB::beginTransaction();
            $country=$this->countryService->delete($country);
            DB::commit();
            return $this->success($country,"Successfully Deleted","201");
        }catch (\Exception $exception){
            DB::rollBack();
            return $this->error($exception->getMessage());

        }
    }
}
