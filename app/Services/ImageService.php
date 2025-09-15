<?php

namespace App\Services;

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\WebpEncoder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;

class ImageService
{
    protected $manager;

    public function __construct()
    {
        $this->manager = new ImageManager(new Driver());
    }

    public function downloadAndOptimizeImage(string $imageUrl, string $directory = 'link_images', int $quality = 75): ?string
    {
        try {
            $response = Http::get($imageUrl);

            if ($response->failed()) {
                Log::error("ImageService: Failed to download image from {$imageUrl}. Status: {$response->status()}");
                return null;
            }

            $imageContents = $response->body();

            $image = $this->manager->read($imageContents);

            // Resize image to a maximum width of 1200px, maintaining aspect ratio
            $image->scaleDown(width: 1200);

            // Generate a unique filename
            $extension = pathinfo(parse_url($imageUrl, PHP_URL_PATH), PATHINFO_EXTENSION);
            if (empty($extension)) {
                $extension = 'jpg'; // Default to jpg if no extension found
            }
            $filename = $directory . '/' . Str::random(40) . '.' . $extension;
            
            // Save the optimized image to the public disk
            // Use WebpEncoder for better compression and modern format
            Storage::disk('public')->put($filename, $image->encode(new WebpEncoder($quality)));

            return '/assets/' . $filename;

        } catch (\Exception $e) {
            // Log the exception for debugging
            \Log::error("ImageService: Failed to download or optimize image from {$imageUrl}. Error: {$e->getMessage()}");
            return null;
        }
    }
}