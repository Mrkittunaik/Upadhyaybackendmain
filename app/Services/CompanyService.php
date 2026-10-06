<?php

namespace App\Services;

use App\Enums\VerificationStatus;
use App\Exceptions\ApiException;
use App\Models\Company;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class CompanyService
{
    private const PROFILE_FIELDS = [
        'description', 'website', 'email', 'phone', 'industry', 'address',
        'city', 'city_code', 'state', 'country', 'location',
    ];

    /** Create the company (+ profile) for a company_main user. One company per user. */
    public function createFor(User $owner, array $data): Company
    {
        if ($owner->company()->exists()) {
            throw new ApiException('A company already exists for this account', 409);
        }

        return DB::transaction(function () use ($owner, $data) {
            $company = Company::create([
                'owner_user_id' => $owner->id,
                'name' => $data['name'],
                'verification_status' => VerificationStatus::Unverified,
            ]);
            $company->profile()->create(array_intersect_key($data, array_flip(self::PROFILE_FIELDS)));

            return $company->load('profile');
        });
    }

    public function update(Company $company, array $data): Company
    {
        return DB::transaction(function () use ($company, $data) {
            if (isset($data['name'])) {
                $company->update(['name' => $data['name']]);
            }
            $profile = array_intersect_key($data, array_flip(self::PROFILE_FIELDS));
            if ($profile) {
                $company->profile()->updateOrCreate(['company_id' => $company->id], $profile);
            }

            return $company->load('profile');
        });
    }

    public function search(?string $term, int $perPage): LengthAwarePaginator
    {
        return Company::query()
            ->with('profile')
            ->search($term)
            ->orderBy('name')
            ->paginate($perPage);
    }
}
