<?php

namespace App\Models;

use App\Enums\ApplicationStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Application extends Model
{
    protected $fillable = ['job_post_id', 'user_id', 'resume_file_id', 'cover_letter', 'status', 'applied_at'];

    protected function casts(): array
    {
        return ['status' => ApplicationStatus::class, 'applied_at' => 'datetime'];
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(JobPost::class, 'job_post_id');
    }

    public function applicant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function resume(): BelongsTo
    {
        return $this->belongsTo(File::class, 'resume_file_id');
    }
}
