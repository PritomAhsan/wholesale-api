<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaService
{
    /**
     * Upload file.
     */
    public function upload(
        UploadedFile $file,
        string $folder
    ): string {

        $filename =
            Str::uuid() .
            '.' .
            $file->getClientOriginalExtension();

        return $file->storeAs(
            $folder,
            $filename,
            'public'
        );
    }

    /**
     * Delete file.
     */
    public function delete(?string $path): void
    {
        if (!$path) {
            return;
        }

        Storage::disk('public')->delete($path);
    }

    /**
     * Replace existing file.
     */
    public function replace(
        UploadedFile $file,
        ?string $oldPath,
        string $folder
    ): string {

        $this->delete($oldPath);

        return $this->upload(
            $file,
            $folder
        );
    }

    /**
     * Get public URL.
     */
    public function url(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        return Storage::url($path);
    }
}
