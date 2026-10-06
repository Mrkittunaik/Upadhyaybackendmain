<?php

namespace App\Models;

use App\Enums\BranchStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompanyBranch extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id', 'user_id', 'branch_request_id', 'branch_code', 'branch_name', 'location',
        'city', 'city_code', 'state', 'country', 'status', 'approved_at',
    ];

    protected function casts(): array
    {
        return ['status' => BranchStatus::class, 'approved_at' => 'datetime'];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(BranchRequest::class, 'branch_request_id');
    }

    public function jobs(): HasMany
    {
        return $this->hasMany(JobPost::class, 'branch_id');
    }

    /** "Hyderabad (HYD)" display format. */
    public function getDisplayLocationAttribute(): ?string
    {
        if (! $this->city) {
            return $this->location;
        }

        return $this->city_code ? "{$this->city} ({$this->city_code})" : $this->city;
    }
}
