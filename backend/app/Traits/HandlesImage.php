<?php

namespace App\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

trait HandlesImage
{
    public function storeImage(?UploadedFile $file, string $folder): ?string
    {
        if (!$file) {
            return null;
        }
        return $file->store('uploads/' . $folder, 'public');
    }

    public function deleteImageIfStored(?string $path): void
    {
        if (!$path || filter_var($path, FILTER_VALIDATE_URL)) {
            return;
        }
        Storage::disk('public')->delete($path);
    }
}