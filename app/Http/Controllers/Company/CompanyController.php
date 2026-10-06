<?php

namespace App\Http\Controllers\Company;

use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCompanyRequest;
use App\Http\Requests\UpdateCompanyRequest;
use App\Http\Resources\CompanyResource;
use App\Models\Company;
use App\Services\CompanyService;
use App\Support\ApiResponse;
use App\Support\Pagination;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function __construct(private CompanyService $companies) {}

    /** GET /companies/search?q= (any authenticated user) */
    public function search(Request $request): JsonResponse
    {
        $request->validate(['q' => ['nullable', 'string', 'max:100']]);

        $paginator = $this->companies->search($request->query('q'), Pagination::perPage($request));
        $paginator->through(fn (Company $c) => new CompanyResource($c));

        return ApiResponse::paginated($paginator);
    }

    /** GET /companies/{company} */
    public function showPublic(Company $company): JsonResponse
    {
        return $this->ok(new CompanyResource($company->load('profile')));
    }

    /** POST /company (company_main) */
    public function store(StoreCompanyRequest $request): JsonResponse
    {
        $company = $this->companies->createFor($request->user(), $request->validated());

        return $this->created(new CompanyResource($company), 'Company created');
    }

    /** GET /company */
    public function show(Request $request): JsonResponse
    {
        return $this->ok(new CompanyResource($this->ownCompany($request)->load('profile')));
    }

    /** PUT /company */
    public function update(UpdateCompanyRequest $request): JsonResponse
    {
        $company = $this->companies->update($this->ownCompany($request), $request->validated());

        return $this->ok(new CompanyResource($company), 'Company updated');
    }

    private function ownCompany(Request $request): Company
    {
        return $request->user()->company
            ?? throw new ApiException('Company profile has not been created yet', 404);
    }
}
