<?php

namespace App\Policies;

use App\Models\CompanyBranch;
use App\Models\User;

class CompanyBranchPolicy
{
    public function view(User $user, CompanyBranch $branch): bool
    {
        return $user->isAdmin()
            || $branch->user_id === $user->id
            || $branch->company->owner_user_id === $user->id;
    }
}
