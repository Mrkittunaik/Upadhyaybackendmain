<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobSkill extends Model
{
    public $timestamps = false;

    protected $fillable = ['job_post_id', 'skill'];

    public function job(): BelongsTo
    {
        return $this->belongsTo(JobPost::class, 'job_post_id');
    }
}
