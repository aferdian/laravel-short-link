<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Link;
use App\Models\Category;
use GuzzleHttp\Client;

class LinkController extends Controller
{
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
            'categories' => ['nullable', 'string'],
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
                'image' => $image,
            ];
        } catch (\Exception $e) {
            //
        }

        $link = auth()->user()->links()->create(array_merge([
            'original_url' => $request->original_url,
            'alias' => $request->alias,
            'short_code' => \Illuminate\Support\Str::random(6),
        ], $metadata));

        $categoryIds = [];
        if ($request->has('categories')) {
            $categoryNames = explode(',', $request->categories);
            foreach ($categoryNames as $categoryName) {
                $category = Category::firstOrCreate(['name' => trim($categoryName)]);
                $categoryIds[] = $category->id;
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
            'image' => ['nullable', 'url'],
            'categories' => ['nullable', 'string'],
        ]);

        $updateData = [
            'original_url' => $request->original_url,
            'alias' => $request->alias,
            'name' => $request->name,
            'description' => $request->description,
            'image' => $request->image,
        ];

        // If name, description, or image are empty, try to fetch metadata
        if (empty($request->name) || empty($request->description) || empty($request->image)) {
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

                if (empty($request->name)) {
                    $updateData['name'] = $title;
                }
                if (empty($request->description)) {
                    $updateData['description'] = $description;
                }
                if (empty($request->image)) {
                    $updateData['image'] = $image;
                }

            } catch (\Exception $e) {
                // Log the exception or handle it as needed
            }
        }

        $link->update($updateData);

        $categoryIds = [];
        if ($request->has('categories')) {
            $categoryNames = explode(',', $request->categories);
            foreach ($categoryNames as $categoryName) {
                $category = Category::firstOrCreate(['name' => trim($categoryName)]);
                $categoryIds[] = $category->id;
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
