<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\BranchRequest;
use App\Models\User;

class BranchRequestPolicy
{
    public function view(User $user, BranchRequest $request): bool
    {
        return $user->isAdmin()
            || $request->user_id === $user->id
            || $request->company->owner_user_id === $user->id;
    }

    /** Only the owner of the target main company may approve/reject. */
    public function review(User $user, BranchRequest $request): bool
    {
        return $user->role === UserRole::CompanyMain
            && $request->company->owner_user_id === $user->id;
    }

    public function cancel(User $user, BranchRequest $request): bool
    {
        return $request->user_id === $user->id;
    }
}
