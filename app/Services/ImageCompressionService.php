<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class ImageCompressionService
{
    private int $maxWidth;
    private int $quality;

    public function __construct()
    {
        $this->maxWidth = (int) config('app.photo_max_width', 800);
        $this->quality  = (int) config('app.photo_quality', 75);
    }

    /**
     * Compress and store an uploaded image.
     * Returns array with 'path' (relative to storage/app/private) and 'size_kb'.
     */
    public function compressAndStore(UploadedFile $file, string $directory): array
    {
        $filename  = Str::uuid() . '.jpg';
        $directory = ltrim($directory, '/');
        $fullDir   = storage_path("app/private/{$directory}");

        if (!is_dir($fullDir)) {
            mkdir($fullDir, 0775, true);
        }

        $fullPath = "{$fullDir}/{$filename}";

        // Read, resize (maintain aspect ratio), encode to JPEG
        $manager = new ImageManager(new Driver());
        $image = $manager->decodePath($file->getRealPath());

        // Only scale down, never scale up
        if ($image->width() > $this->maxWidth) {
            $image->scaleDown(width: $this->maxWidth);
        }

        $image->save($fullPath, quality: $this->quality);

        $sizeKb = (int) ceil(filesize($fullPath) / 1024);

        return [
            'path'    => "private/{$directory}/{$filename}",
            'size_kb' => $sizeKb,
        ];
    }

    /**
     * Delete a stored photo from private storage.
     */
    public function delete(string $path): void
    {
        $fullPath = storage_path("app/{$path}");
        if (file_exists($fullPath)) {
            unlink($fullPath);
        }
    }
}
