<?php

namespace App\Models;

use App\Enums\BranchRequestStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BranchRequest extends Model
{
    protected $fillable = [
        'company_id', 'user_id', 'branch_name', 'location', 'city', 'city_code', 'state', 'country',
        'status', 'reviewed_by', 'reviewed_at', 'rejection_reason',
    ];

    protected function casts(): array
    {
        return ['status' => BranchRequestStatus::class, 'reviewed_at' => 'datetime'];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
