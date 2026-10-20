<?php

namespace App\Services;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Profile pictures on the media disk (config/filesystems.php): stored under avatars/, the path saved on the user.
 */
class AvatarService
{
    /** Stores an uploaded picture and returns its path. */
    public function store(UploadedFile $file): string
    {
        return $file->store('avatars', config('filesystems.media'));
    }

    public function delete(?string $path): void
    {
        if ($path) {
            $this->disk()->delete($path);
        }
    }

    public function url(?string $path): ?string
    {
        return $path ? $this->disk()->url($path) : null;
    }

    private function disk(): Filesystem
    {
        return Storage::disk(config('filesystems.media'));
    }
}
