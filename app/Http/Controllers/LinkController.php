<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Link;
use App\Models\Category;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;
use App\Services\ImageService;
use Illuminate\Support\Str;

class LinkController extends Controller
{
    protected $imageService;

    public function __construct(ImageService $imageService)
    {
        $this->imageService = $imageService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('links.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Category::all();
        return view('links.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'original_url' => ['required', 'url'],
            'alias' => ['nullable', 'alpha_dash', 'unique:links,alias'],
            'categories' => ['nullable', 'array'],
        ]);

        $metadata = [];
        try {
            $client = new Client();
            $response = $client->get($request->original_url);
            $html = (string) $response->getBody();

            $doc = new \DOMDocument();
            @$doc->loadHTML($html);

            $title = $doc->getElementsByTagName('title')->item(0)->nodeValue;
            $description = '';
            $image = '';

            $metas = $doc->getElementsByTagName('meta');
            for ($i = 0; $i < $metas->length; $i++) {
                $meta = $metas->item($i);
                if ($meta->getAttribute('name') == 'description') {
                    $description = $meta->getAttribute('content');
                }
                if ($meta->getAttribute('property') == 'og:image') {
                    $image = $meta->getAttribute('content');
                }
            }
            $metadata = [
                'name' => $title,
                'description' => $description,
                'image' => null, // Initialize image to null
            ];

            if (!empty($image)) { // Use the fetched $image URL
                $optimizedImagePath = $this->imageService->downloadAndOptimizeImage($image);
                if ($optimizedImagePath) {
                    $metadata['image'] = $optimizedImagePath;
                } else {
                    $metadata['image'] = null; // Set to null if download/optimization failed
                }
            }

        } catch (\Exception $e) {
            \Log::error("LinkController: Failed to fetch metadata for {$request->original_url}. Error: {$e->getMessage()}");
        }

        $link = auth()->user()->links()->create(array_merge([
            'original_url' => $request->original_url,
            'alias' => $request->alias,
            'short_code' => \Illuminate\Support\Str::random(6),
        ], $metadata));

        $categoryIds = [];
        if ($request->has('categories')) {
            foreach ($request->categories as $category) {
                if (is_numeric($category)) {
                    $categoryIds[] = $category;
                } else {
                    $newCategory = Category::firstOrCreate(['name' => $category]);
                    $categoryIds[] = $newCategory->id;
                }
            }
        }
        $link->categories()->sync($categoryIds);

        return redirect()->route('links.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Link $link)
    {
        $categories = Category::all();
        return view('links.edit', compact('link', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Link $link)
    {
        $request->validate([
            'original_url' => ['required', 'url'],
            'alias' => ['nullable', 'alpha_dash', 'unique:links,alias,' . $link->id],
            'name' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'string'],
            'categories' => ['nullable', 'array'],
        ]);

        $updateData = [
            'original_url' => $request->original_url,
            'alias' => $request->alias,
            'name' => $request->name,
            'description' => $request->description,
            'image' => $request->image,
        ];

        // Handle image from request if provided
        if ($request->has('image')) {
            $newImage = $request->input('image');
            if (empty($newImage)) {
                $updateData['image'] = null; // Allow removing the image
            } elseif (Str::startsWith($newImage, ['http://', 'https://'])) {
                // Commenting this code out to prevent automatic downloading of new images from URLs, use the provided url as it is
                // The downloading is only for the automatic meta getting, not for user-provided URLs
                /*// If a new, valid URL is provided, download and optimize it.
                $optimizedImagePath = $this->imageService->downloadAndOptimizeImage($newImage);
                if ($optimizedImagePath) {
                    $updateData['image'] = $optimizedImagePath;
                }*/
            }
            // If the existing relative path is submitted, we do nothing,
            // as $updateData['image'] is already pre-filled with the existing path.
        }

        // If name, description, or image are empty, try to fetch metadata
        if (empty($request->name) || empty($request->description) || (empty($request->image) && empty($updateData['image']))) {
            try {
                $client = new Client();
                $response = $client->get($request->original_url);
                $html = (string) $response->getBody();

                $doc = new \DOMDocument();
                @$doc->loadHTML($html);

                $title = $doc->getElementsByTagName('title')->item(0)->nodeValue;
                $description = '';
                $fetchedImage = ''; // Use a different variable name to avoid conflict

                $metas = $doc->getElementsByTagName('meta');
                for ($i = 0; $i < $metas->length; $i++) {
                    $meta = $metas->item($i);
                    if ($meta->getAttribute('name') == 'description') {
                        $description = $meta->getAttribute('content');
                    }
                    if ($meta->getAttribute('property') == 'og:image') {
                        $fetchedImage = $meta->getAttribute('content');
                    }
                }

                if (empty($request->name)) {
                    $updateData['name'] = $title;
                }
                if (empty($request->description)) {
                    $updateData['description'] = $description;
                }
                // Only fetch and optimize if no image was provided in the request or already fetched
                if (empty($request->image) && empty($updateData['image']) && !empty($fetchedImage)) {
                    $optimizedImagePath = $this->imageService->downloadAndOptimizeImage($fetchedImage);
                    if ($optimizedImagePath) {
                        $updateData['image'] = $optimizedImagePath;
                    } else {
                        $updateData['image'] = null; // Set to null if download/optimization failed
                    }
                }

            } catch (\Exception $e) {
                \Log::error("LinkController: Failed to fetch metadata for {$request->original_url}. Error: {$e->getMessage()}");
            }
        }

        $link->update($updateData);

        $categoryIds = [];
        if ($request->has('categories')) {
            foreach ($request->categories as $category) {
                if (is_numeric($category)) {
                    $categoryIds[] = $category;
                } else {
                    $newCategory = Category::firstOrCreate(['name' => $category]);
                    $categoryIds[] = $newCategory->id;
                }
            }
        }
        $link->categories()->sync($categoryIds);

        return redirect()->route('links.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Link $link)
    {
        $link->delete();

        return redirect()->route('links.index');
    }
}
