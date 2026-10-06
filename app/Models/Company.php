<?php

namespace App\Models;

use App\Enums\VerificationStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use SoftDeletes;

    protected $fillable = ['owner_user_id', 'name', 'company_code', 'verification_status', 'verified_at'];

    protected function casts(): array
    {
        return ['verification_status' => VerificationStatus::class, 'verified_at' => 'datetime'];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function profile(): HasOne
    {
        return $this->hasOne(CompanyProfile::class);
    }

    public function branches(): HasMany
    {
        return $this->hasMany(CompanyBranch::class);
    }

    public function branchRequests(): HasMany
    {
        return $this->hasMany(BranchRequest::class);
    }

    public function jobs(): HasMany
    {
        return $this->hasMany(JobPost::class);
    }

    /** Search by name, code, city/location. */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (! $term) {
            return $query;
        }

        $like = '%'.addcslashes($term, '%_\\').'%';

        return $query->where(function (Builder $q) use ($like) {
            $q->where('name', 'like', $like)
                ->orWhere('company_code', 'like', $like)
                ->orWhereHas('profile', fn (Builder $p) => $p
                    ->where('city', 'like', $like)
                    ->orWhere('location', 'like', $like)
                    ->orWhere('state', 'like', $like));
        });
    }
}
