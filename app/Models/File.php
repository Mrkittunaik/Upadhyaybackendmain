<?php

namespace App\Models;

use App\Enums\FileType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class File extends Model
{
    use SoftDeletes;

    protected $fillable = ['user_id', 'file_type', 'disk', 'path', 'original_name', 'mime_type', 'size'];

    protected $hidden = ['disk', 'path'];

    protected function casts(): array
    {
        return ['file_type' => FileType::class, 'size' => 'integer'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
