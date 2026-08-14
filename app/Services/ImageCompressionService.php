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

    /**
     * Add a watermark text to the bottom of the image.
     */
    public function addWatermark(string $fullPath, array $watermarkData): void
    {
        if (!file_exists($fullPath)) return;

        $manager = new ImageManager(new Driver());
        $image = $manager->decodePath($fullPath);
        
        $fontPath = base_path('vendor/dompdf/dompdf/lib/fonts/DejaVuSans-Bold.ttf');
        
        $y = $image->height() - 10;
        $x = 10;
        
        $lines = array_reverse($watermarkData);
        
        foreach ($lines as $label => $value) {
            $text = is_numeric($label) ? $value : "{$label}: {$value}";
            
            // Draw text shadow
            $image->text($text, $x + 1, $y + 1, function ($font) use ($fontPath) {
                if (file_exists($fontPath)) {
                    $font->filename($fontPath);
                } else {
                    $font->file(3);
                }
                $font->size(16);
                $font->color('#000000');
                $font->align('left');
                $font->valign('bottom');
            });
            
            // Draw main text
            $image->text($text, $x, $y, function ($font) use ($fontPath) {
                if (file_exists($fontPath)) {
                    $font->filename($fontPath);
                } else {
                    $font->file(3);
                }
                $font->size(16);
                $font->color('#ffffff');
                $font->align('left');
                $font->valign('bottom');
            });
            
            $y -= 25; // Move up for next line
        }
        
        $image->save($fullPath, quality: $this->quality);
    }
}
