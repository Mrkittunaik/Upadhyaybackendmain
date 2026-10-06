<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FacultySkill extends Model
{
    protected $fillable = ['faculty_profile_id', 'skill'];

    public function profile(): BelongsTo
    {
        return $this->belongsTo(FacultyProfile::class, 'faculty_profile_id');
    }
}
