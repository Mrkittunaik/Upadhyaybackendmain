<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FacultyProfile extends Model
{
    protected $fillable = [
        'user_id', 'profile_photo_file_id', 'resume_file_id', 'designation', 'department',
        'college', 'qualification', 'experience_years', 'bio', 'location', 'city',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function photo(): BelongsTo
    {
        return $this->belongsTo(File::class, 'profile_photo_file_id');
    }

    public function resume(): BelongsTo
    {
        return $this->belongsTo(File::class, 'resume_file_id');
    }

    public function education(): HasMany
    {
        return $this->hasMany(FacultyEducation::class);
    }

    public function experience(): HasMany
    {
        return $this->hasMany(FacultyExperience::class);
    }

    public function skills(): HasMany
    {
        return $this->hasMany(FacultySkill::class);
    }
}
