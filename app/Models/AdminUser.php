<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminUser extends Model
{
    protected $fillable = ['user_id', 'is_super', 'permissions'];

    protected function casts(): array
    {
        return ['is_super' => 'boolean', 'permissions' => 'array'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
