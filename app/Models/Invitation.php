<?php

namespace App\Models;

use App\Enums\InvitationStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invitation extends Model
{
    protected $fillable = [
        'company_id', 'branch_id', 'job_post_id', 'invited_by', 'faculty_user_id',
        'message', 'status', 'expires_at', 'responded_at',
    ];

    protected function casts(): array
    {
        return ['status' => InvitationStatus::class, 'expires_at' => 'datetime', 'responded_at' => 'datetime'];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(CompanyBranch::class, 'branch_id');
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(JobPost::class, 'job_post_id');
    }

    public function inviter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    public function faculty(): BelongsTo
    {
        return $this->belongsTo(User::class, 'faculty_user_id');
    }
}
