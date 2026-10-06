<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyProfile extends Model
{
    protected $fillable = [
        'company_id', 'logo_file_id', 'description', 'website', 'email', 'phone', 'industry',
        'address', 'city', 'city_code', 'state', 'country', 'location',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function logo(): BelongsTo
    {
        return $this->belongsTo(File::class, 'logo_file_id');
    }
}
