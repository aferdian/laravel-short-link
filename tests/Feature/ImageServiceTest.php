<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Services\ImageService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

class ImageServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    /** @test */
    public function it_downloads_and_optimizes_an_image()
    {
        $imageService = new ImageService();
        $imageUrl = 'https://via.placeholder.com/150';
        $dummyImageContent = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII='); // A 1x1 transparent PNG base64 encoded

        Http::fake([
            $imageUrl => Http::response($dummyImageContent, 200, ['Content-Type' => 'image/png']),
        ]);

        $path = $imageService->downloadAndOptimizeImage($imageUrl);

        $this->assertNotNull($path);
        Storage::disk('public')->assertExists(ltrim($path, '/assets/'));

        // Further assertions could be made to check image dimensions or quality
        // This would require more advanced image assertion libraries or manual inspection
    }

    /** @test */
    public function it_returns_null_if_image_download_fails()
    {
        $imageService = new ImageService();
        $imageUrl = 'https://invalid.url/image.jpg';

        Http::fake([
            $imageUrl => Http::response('', 404),
        ]);

        $path = $imageService->downloadAndOptimizeImage($imageUrl);

        $this->assertNull($path);
    }

    /** @test */
    public function it_returns_null_if_image_content_is_invalid()
    {
        $imageService = new ImageService();
        $imageUrl = 'https://example.com/not-an-image.txt';

        Http::fake([
            $imageUrl => Http::response('This is not an image', 200, ['Content-Type' => 'text/plain']),
        ]);

        $path = $imageService->downloadAndOptimizeImage($imageUrl);

        $this->assertNull($path);
    }
}