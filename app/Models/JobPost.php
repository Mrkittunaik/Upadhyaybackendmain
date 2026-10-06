<?php

namespace App\Models;

use App\Enums\JobStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class JobPost extends Model
{
    use SoftDeletes;

    protected $table = 'job_posts';

    protected $fillable = [
        'company_id', 'branch_id', 'posted_by', 'image_file_id', 'title', 'description', 'department',
        'location', 'city', 'employment_type', 'experience', 'salary', 'education',
        'application_deadline', 'status', 'published_at',
    ];

    protected function casts(): array
    {
        return ['status' => JobStatus::class, 'application_deadline' => 'date', 'published_at' => 'datetime'];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(CompanyBranch::class, 'branch_id');
    }

    public function poster(): BelongsTo
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    public function skills(): HasMany
    {
        return $this->hasMany(JobSkill::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }
}
