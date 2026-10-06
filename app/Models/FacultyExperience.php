<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FacultyExperience extends Model
{
    protected $table = 'faculty_experience';

    protected $fillable = ['faculty_profile_id', 'title', 'organization', 'location', 'start_date', 'end_date', 'is_current', 'description'];

    protected function casts(): array
    {
        return ['start_date' => 'date', 'end_date' => 'date', 'is_current' => 'boolean'];
    }

    public function profile(): BelongsTo
    {
        return $this->belongsTo(FacultyProfile::class, 'faculty_profile_id');
    }
}
