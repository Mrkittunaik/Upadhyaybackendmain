<?php

namespace App\Services;

use App\Enums\FileType;
use App\Models\File;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Stores uploads on the configured disk (R2 in production), metadata in MySQL.
 * Never trusts the client filename or client-supplied MIME type.
 */
class FileStorageService
{
    /** extension => allowed real MIME types (detected server-side) */
    private const MIME_MAP = [
        'jpg' => ['image/jpeg'], 'jpeg' => ['image/jpeg'], 'png' => ['image/png'], 'webp' => ['image/webp'],
        'pdf' => ['application/pdf'],
        'doc' => ['application/msword', 'application/x-ole-storage'],
        'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/zip'],
        'xls' => ['application/vnd.ms-excel', 'application/x-ole-storage'],
        'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/zip'],
        'mp4' => ['video/mp4'], 'webm' => ['video/webm'],
    ];

    public function disk(): string
    {
        return config('filesystems.default');
    }

    public function store(UploadedFile $upload, User $user, FileType $type): File
    {
        $rules = config('upadhyay.uploads.'.$type->value);
        $ext = strtolower($upload->getClientOriginalExtension());
        $mime = (string) $upload->getMimeType(); // detected from file content

        if (! $upload->isValid()
            || ! in_array($ext, $rules['ext'], true)
            || ! in_array($mime, self::MIME_MAP[$ext] ?? [], true)
            || $upload->getSize() > $rules['max_kb'] * 1024) {
            throw ValidationException::withMessages(['file' => ['Invalid file type or size.']]);
        }

        $path = sprintf('%s/%d/%s.%s', $type->value, $user->id, Str::uuid(), $ext);
        Storage::disk($this->disk())->putFileAs(dirname($path), $upload, basename($path));

        return File::create([
            'user_id' => $user->id,
            'file_type' => $type,
            'disk' => $this->disk(),
            'path' => $path,
            'original_name' => Str::limit(basename($upload->getClientOriginalName()), 200, ''),
            'mime_type' => $mime,
            'size' => $upload->getSize(),
        ]);
    }

    /** Short-lived signed URL (private bucket). Falls back to null on disks without support. */
    public function temporaryUrl(File $file, int $minutes = 15): ?string
    {
        $disk = Storage::disk($file->disk);

        return method_exists($disk, 'temporaryUrl') ? $disk->temporaryUrl($file->path, now()->addMinutes($minutes)) : null;
    }

    public function delete(File $file): void
    {
        Storage::disk($file->disk)->delete($file->path);
        $file->delete();
    }
}
