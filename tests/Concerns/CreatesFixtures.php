<?php

namespace Tests\Concerns;

use App\Enums\UserRole;
use App\Models\Company;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

trait CreatesFixtures
{
    protected function makeUser(UserRole $role = UserRole::Faculty, array $attrs = []): User
    {
        return User::factory()->role($role)->create($attrs);
    }

    protected function makeCompany(?User $owner = null, array $profile = [], string $name = 'ABC Technologies'): Company
    {
        $owner ??= $this->makeUser(UserRole::CompanyMain);
        $company = Company::create(['owner_user_id' => $owner->id, 'name' => $name]);
        $company->profile()->create($profile + ['city' => 'Hyderabad', 'city_code' => 'HYD', 'state' => 'Telangana', 'country' => 'India']);

        return $company;
    }

    protected function actingAsUser(User $user): static
    {
        Sanctum::actingAs($user);

        return $this;
    }

    protected function branchPayload(int $companyId, array $over = []): array
    {
        return $over + [
            'company_id' => $companyId,
            'branch_name' => 'ABC Technologies - Hyderabad',
            'city' => 'Hyderabad',
            'city_code' => 'hyd',
            'state' => 'Telangana',
            'country' => 'India',
        ];
    }
}
