<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FacultyEducation extends Model
{
    protected $table = 'faculty_education';

    protected $fillable = ['faculty_profile_id', 'degree', 'institution', 'field_of_study', 'start_year', 'end_year', 'certificate_file_id'];

    public function profile(): BelongsTo
    {
        return $this->belongsTo(FacultyProfile::class, 'faculty_profile_id');
    }

    public function certificate(): BelongsTo
    {
        return $this->belongsTo(File::class, 'certificate_file_id');
    }
}
