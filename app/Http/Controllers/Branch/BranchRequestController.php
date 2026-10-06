<?php

namespace App\Http\Controllers\Branch;

use App\Enums\BranchRequestStatus;
use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Http\Requests\BranchRequestRequest;
use App\Http\Requests\RejectBranchRequest;
use App\Http\Resources\BranchRequestResource;
use App\Http\Resources\BranchResource;
use App\Models\BranchRequest;
use App\Services\BranchService;
use App\Support\ApiResponse;
use App\Support\Pagination;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class BranchRequestController extends Controller
{
    public function __construct(private BranchService $branches) {}

    /** POST /branches/requests (company_branch) */
    public function store(BranchRequestRequest $request): JsonResponse
    {
        $branchRequest = $this->branches->createRequest($request->user(), $request->validated());

        return $this->created(new BranchRequestResource($branchRequest), 'Branch request sent');
    }

    /** GET /branches/requests/mine (company_branch) */
    public function mine(Request $request): JsonResponse
    {
        $paginator = BranchRequest::with('company:id,name')
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(Pagination::perPage($request));
        $paginator->through(fn ($r) => new BranchRequestResource($r));

        return ApiResponse::paginated($paginator);
    }

    /** POST /branches/requests/{branchRequest}/cancel (company_branch, own) */
    public function cancel(BranchRequest $branchRequest): JsonResponse
    {
        Gate::authorize('cancel', $branchRequest);

        return $this->ok(new BranchRequestResource($this->branches->cancel($branchRequest)), 'Request cancelled');
    }

    /** GET /branches/requests?status= (company_main) */
    public function index(Request $request): JsonResponse
    {
        $request->validate(['status' => ['nullable', Rule::in(BranchRequestStatus::values())]]);

        $company = $request->user()->company
            ?? throw new ApiException('Company profile has not been created yet', 404);

        $paginator = BranchRequest::with('requester:id,name,email')
            ->where('company_id', $company->id)
            ->when($request->query('status'), fn ($q, $s) => $q->where('status', $s))
            ->latest()
            ->paginate(Pagination::perPage($request));
        $paginator->through(fn ($r) => new BranchRequestResource($r));

        return ApiResponse::paginated($paginator);
    }

    /** GET /branches/requests/{branchRequest} */
    public function show(BranchRequest $branchRequest): JsonResponse
    {
        Gate::authorize('view', $branchRequest);

        return $this->ok(new BranchRequestResource($branchRequest->load('company:id,name', 'requester:id,name,email')));
    }

    /** POST /branches/requests/{branchRequest}/approve (company_main) */
    public function approve(Request $request, BranchRequest $branchRequest): JsonResponse
    {
        Gate::authorize('review', $branchRequest);

        $branch = $this->branches->approve($branchRequest, $request->user());

        return $this->ok(new BranchResource($branch), 'Branch approved');
    }

    /** POST /branches/requests/{branchRequest}/reject (company_main) */
    public function reject(RejectBranchRequest $request, BranchRequest $branchRequest): JsonResponse
    {
        Gate::authorize('review', $branchRequest);

        $rejected = $this->branches->reject($branchRequest, $request->user(), $request->validated('reason'));

        return $this->ok(new BranchRequestResource($rejected), 'Branch request rejected');
    }
}
