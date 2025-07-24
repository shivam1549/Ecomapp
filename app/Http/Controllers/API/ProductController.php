<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Models\ProductVariation;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        return Product::with('categories')->latest()->paginate(10);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
        // dd(request()->all());

        $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|unique:products,slug',
            'price' => 'required|numeric',
            'main_image' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
            'gallery_images.*' => 'image|mimes:jpg,jpeg,png,webp|max:2048',
            'category_ids' => 'required|array',
            'category_ids.*' => 'exists:categories,id',
            'short_description' => 'nullable|string',
            'long_description' => 'nullable|string',
            'meta_title' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'is_variable' => 'boolean',
        ]);

        // Upload Main Image
        $mainImage = $request->file('main_image')->store('products', 'public');

        // Upload Gallery Images
        $galleryImages = [];
        if ($request->hasFile('gallery_images')) {
            foreach ($request->file('gallery_images') as $img) {
                $galleryImages[] = $img->store('products', 'public');
            }
        }

        $product = Product::create([
            'title' => $request->title,
            'slug' => $request->slug ?? Str::slug($request->title),
            'price' => $request->price,
            'main_image' => $mainImage,
            'gallery_images' => $galleryImages,
            'short_description' => $request->short_description,
            'long_description' => $request->long_description,
            'meta_title' => $request->meta_title,
            'is_active' => $request->is_active ?? true,
            'is_variable' => $request->is_variable ?? false,
        ]);

        $product->categories()->sync($request->category_ids);

        if ($request->is_variable && $request->has('variations')) {
            foreach ($request->variations as $index => $variationData) {
                $imagePath = null;

                if ($request->hasFile("variations.$index.image")) {
                    $imagePath = $request->file("variations.$index.image")->store('products/variations', 'public');
                }

                ProductVariation::create([
                    'product_id' => $product->id,
                    'attributes' => $variationData['attributes'],
                    'price' => $variationData['price'],
                    'sku' => $variationData['sku'],
                    'quantity' => $variationData['quantity'],
                    'image' => $imagePath,
                ]);
            }
        }

        return response()->json(['message' => 'Product created successfully', 'product' => $product]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
        return Product::with('categories')->findOrFail($id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Product $product)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|unique:products,slug,' . $product->id,
            'price' => 'required|numeric',
            'main_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'gallery_images.*' => 'image|mimes:jpg,jpeg,png,webp|max:2048',
            'category_ids' => 'required|array',
            'category_ids.*' => 'exists:categories,id',
            'short_description' => 'nullable|string',
            'long_description' => 'nullable|string',
            'meta_title' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'is_variable' => 'boolean',
        ]);

        // Update Main Image
        if ($request->hasFile('main_image')) {
            $mainImage = $request->file('main_image')->store('products', 'public');
            $product->main_image = $mainImage;
        }

        // Update Gallery Images
        $galleryImages = $product->gallery_images ?? [];
        if ($request->hasFile('gallery_images')) {
            foreach ($request->file('gallery_images') as $img) {
                $galleryImages[] = $img->store('products', 'public');
            }
            $product->gallery_images = $galleryImages;
        }

        $product->update([
            'title' => $request->title,
            'slug' => $request->slug ?? Str::slug($request->title),
            'price' => $request->price,
            'short_description' => $request->short_description,
            'long_description' => $request->long_description,
            'meta_title' => $request->meta_title,
            'is_active' => $request->is_active ?? true,
            'is_variable' => $request->is_variable ?? false,
        ]);

        $product->categories()->sync($request->category_ids);

        if ($product->is_variable && $request->has('variations')) {
            foreach ($request->variations as $index => $variationData) {
                $variation = null;

                // Prepare attributes
                if (is_string($variationData['attributes'])) {
                    $variationData['attributes'] = json_decode($variationData['attributes'], true);
                }

                // Handle image upload (check if file exists for index)
                $imagePath = null;
                if ($request->hasFile("variations.$index.image")) {
                    $imagePath = $request->file("variations.$index.image")->store('products/variations', 'public');
                }

                // If ID is passed → update existing
                if (!empty($variationData['id'])) {
                    $variation = $product->variations()->where('id', $variationData['id'])->first();
                    if ($variation) {
                        $variation->update([
                            'attributes' => $variationData['attributes'],
                            'price' => $variationData['price'],
                            'sku' => $variationData['sku'],
                            'quantity' => $variationData['quantity'],
                            'image' => $imagePath ?? $variation->image, // retain old image if not replaced
                        ]);
                    }
                }

                // If not existing → create new
                if (!$variation) {
                    $product->variations()->create([
                        'attributes' => $variationData['attributes'],
                        'price' => $variationData['price'],
                        'sku' => $variationData['sku'],
                        'quantity' => $variationData['quantity'],
                        'image' => $imagePath,
                    ]);
                }
            }

            // Optionally: Delete removed variations
            // $product->variations()->whereNotIn('id', collect($request->variations)->pluck('id'))->delete();
        }


        return response()->json(['message' => 'Product updated successfully']);
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
        $product = Product::findOrFail($id);

        Storage::disk('public')->delete($product->main_image);
        foreach ($product->gallery_images ?? [] as $img) {
            Storage::disk('public')->delete($img);
        }

        $product->categories()->detach();
        $product->delete();

        return response()->json(['message' => 'Product deleted successfully']);
    }
}
