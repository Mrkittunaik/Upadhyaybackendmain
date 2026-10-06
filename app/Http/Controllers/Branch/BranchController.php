<?php

namespace App\Http\Controllers\Branch;

use App\Enums\BranchStatus;
use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Http\Resources\BranchRequestResource;
use App\Http\Resources\BranchResource;
use App\Models\Application;
use App\Models\BranchRequest;
use App\Models\CompanyBranch;
use App\Models\JobPost;
use App\Models\Notification;
use App\Support\ApiResponse;
use App\Support\Pagination;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class BranchController extends Controller
{
    /** GET /branches?status= (company_main): all branches of own company */
    public function index(Request $request): JsonResponse
    {
        $request->validate(['status' => ['nullable', Rule::in(BranchStatus::values())]]);

        $company = $request->user()->company
            ?? throw new ApiException('Company profile has not been created yet', 404);

        $paginator = CompanyBranch::with('user:id,name,email')
            ->where('company_id', $company->id)
            ->when($request->query('status'), fn ($q, $s) => $q->where('status', $s))
            ->latest()
            ->paginate(Pagination::perPage($request));
        $paginator->through(fn ($b) => new BranchResource($b));

        return ApiResponse::paginated($paginator);
    }

    /** GET /branches/{branch} (main owner, the branch itself) */
    public function show(CompanyBranch $branch): JsonResponse
    {
        Gate::authorize('view', $branch);

        return $this->ok(new BranchResource($branch->load('company.profile', 'user:id,name,email')));
    }

    /** GET /branches/{branch}/jobs (main campus views what a branch posts) */
    public function jobs(Request $request, CompanyBranch $branch): JsonResponse
    {
        Gate::authorize('view', $branch);

        $paginator = JobPost::where('branch_id', $branch->id)
            ->when($request->query('status'), fn ($q, $s) => $q->where('status', $s))
            ->latest()
            ->paginate(Pagination::perPage($request));

        return ApiResponse::paginated($paginator);
    }

    /** GET /branch/dashboard (company_branch) */
    public function dashboard(Request $request): JsonResponse
    {
        $user = $request->user();
        $branch = $user->branch()->with('company.profile')->first();

        // Locked until a main campus approves the request.
        if (! $branch || $branch->status !== BranchStatus::Active) {
            $latest = BranchRequest::with('company:id,name')->where('user_id', $user->id)->latest()->first();

            return $this->ok([
                'unlocked' => false,
                'branch' => $branch ? new BranchResource($branch) : null,
                'latest_request' => $latest ? new BranchRequestResource($latest) : null,
            ]);
        }

        return $this->ok([
            'unlocked' => true,
            'company' => ['id' => $branch->company->id, 'name' => $branch->company->name],
            'branch' => new BranchResource($branch),
            'stats' => [
                'jobs' => JobPost::where('branch_id', $branch->id)->count(),
                'applications' => Application::whereHas('job', fn ($q) => $q->where('branch_id', $branch->id))->count(),
                'unread_notifications' => Notification::where('user_id', $user->id)->whereNull('read_at')->count(),
            ],
        ]);
    }
}
