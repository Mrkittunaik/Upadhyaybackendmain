<?php

namespace App\Services;

use App\Enums\BranchRequestStatus;
use App\Enums\BranchStatus;
use App\Enums\NotificationType;
use App\Exceptions\ApiException;
use App\Models\BranchRequest;
use App\Models\Company;
use App\Models\CompanyBranch;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;

class BranchService
{
    public function __construct(
        private BranchCodeGenerator $codes,
        private NotificationService $notifications,
        private AuditLogService $audit,
    ) {}

    /** Branch user -> sends a request to a main company. */
    public function createRequest(User $user, array $data): BranchRequest
    {
        if (CompanyBranch::where('user_id', $user->id)->exists()) {
            throw new ApiException('You already belong to a branch', 409);
        }
        if (BranchRequest::where('user_id', $user->id)->where('status', BranchRequestStatus::Pending)->exists()) {
            throw new ApiException('You already have a pending branch request', 409);
        }

        $company = Company::findOrFail($data['company_id']);

        return DB::transaction(function () use ($user, $data, $company) {
            $request = BranchRequest::create([
                'company_id' => $company->id,
                'user_id' => $user->id,
                'branch_name' => $data['branch_name'],
                'location' => $data['location'] ?? null,
                'city' => $data['city'] ?? null,
                'city_code' => $data['city_code'] ?? null,
                'state' => $data['state'] ?? null,
                'country' => $data['country'] ?? null,
                'status' => BranchRequestStatus::Pending,
            ]);

            $this->notifications->send(
                $company->owner_user_id,
                NotificationType::BranchRequest,
                'New branch request',
                "{$user->name} requested to join as {$request->branch_name}",
                ['branch_request_id' => $request->id],
            );

            return $request->load('company:id,name');
        });
    }

    public function cancel(BranchRequest $request): BranchRequest
    {
        if ($request->status !== BranchRequestStatus::Pending) {
            throw new ApiException('Only pending requests can be cancelled', 409);
        }
        $request->update(['status' => BranchRequestStatus::Cancelled]);

        return $request;
    }

    /**
     * Approve: lock request, activate branch, generate unique 5-digit code,
     * update request, notify, audit. All-or-nothing.
     */
    public function approve(BranchRequest $branchRequest, User $reviewer): CompanyBranch
    {
        for ($attempt = 1; $attempt <= 3; $attempt++) {
            try {
                return DB::transaction(function () use ($branchRequest, $reviewer) {
                    $req = BranchRequest::whereKey($branchRequest->id)->lockForUpdate()->firstOrFail();

                    if ($req->status !== BranchRequestStatus::Pending) {
                        throw new ApiException('This request has already been processed', 409);
                    }
                    if (CompanyBranch::where('user_id', $req->user_id)->exists()) {
                        throw new ApiException('This user already belongs to a branch', 409);
                    }

                    $branch = CompanyBranch::create([
                        'company_id' => $req->company_id,
                        'user_id' => $req->user_id,
                        'branch_request_id' => $req->id,
                        'branch_code' => $this->codes->generate(),
                        'branch_name' => $req->branch_name,
                        'location' => $req->location,
                        'city' => $req->city,
                        'city_code' => $req->city_code,
                        'state' => $req->state,
                        'country' => $req->country,
                        'status' => BranchStatus::Active,
                        'approved_at' => now(),
                    ]);

                    $req->update([
                        'status' => BranchRequestStatus::Approved,
                        'reviewed_by' => $reviewer->id,
                        'reviewed_at' => now(),
                    ]);

                    $this->notifications->send(
                        $req->user_id,
                        NotificationType::BranchApproved,
                        'Branch approved',
                        "Your branch {$branch->branch_name} was approved. Branch code: {$branch->branch_code}",
                        ['branch_id' => $branch->id, 'branch_code' => $branch->branch_code],
                    );

                    $this->audit->log('branch.approved', $branch, null, [
                        'branch_request_id' => $req->id, 'branch_code' => $branch->branch_code,
                    ]);

                    return $branch->load('company.profile');
                });
            } catch (UniqueConstraintViolationException $e) {
                if ($attempt === 3) {
                    throw new ApiException('Could not generate a unique branch code, please retry', 500);
                }
            }
        }

        throw new ApiException('Approval failed', 500); // unreachable safeguard
    }

    public function reject(BranchRequest $branchRequest, User $reviewer, ?string $reason): BranchRequest
    {
        return DB::transaction(function () use ($branchRequest, $reviewer, $reason) {
            $req = BranchRequest::whereKey($branchRequest->id)->lockForUpdate()->firstOrFail();

            if ($req->status !== BranchRequestStatus::Pending) {
                throw new ApiException('This request has already been processed', 409);
            }

            $req->update([
                'status' => BranchRequestStatus::Rejected,
                'reviewed_by' => $reviewer->id,
                'reviewed_at' => now(),
                'rejection_reason' => $reason,
            ]);

            $this->notifications->send(
                $req->user_id,
                NotificationType::BranchRejected,
                'Branch request rejected',
                $reason ?: 'Your branch request was rejected.',
                ['branch_request_id' => $req->id],
            );

            $this->audit->log('branch.rejected', $req, null, ['reason' => $reason]);

            return $req;
        });
    }
}
