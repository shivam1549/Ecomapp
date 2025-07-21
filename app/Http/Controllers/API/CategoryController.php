<?php

namespace App\Http\Controllers\Api;

use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
    //
    public function index()
    {
        return Category::with('children')->whereNull('parent_id')->get();
    }

    public function store(Request $request)
    {
        // var_dump($request->all());
        $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:categories,id',
            'slug' => 'required|string|unique:categories,slug',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);


        $imagePath = null;

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $filename = Str::slug($request->name) . '-' . time() . '.webp';

            // Convert and save as webp
            $webpImage = Image::make($image)->encode('webp', 85); // 85 = compression quality
            $path = storage_path('app/public/categories/' . $filename);
            $webpImage->save($path);

            $imagePath = 'categories/' . $filename;
        }

        $category = Category::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'parent_id' => $request->parent_id,
            'image' => $imagePath,
            'description' => $request->description,
            'is_active' => $request->is_active,
        ]);

        return response()->json($category, 201);
    }

    public function show($id)
    {
        $category = Category::with('children')->findOrFail($id);
        return response()->json($category);
    }

    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:categories,id',
            'slug' => 'required|string|unique:categories,slug,' . $category->id,
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $imagePath = $category->image;

        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($imagePath) {
                Storage::delete('public/' . $imagePath);
            }

            $image = $request->file('image');
            $filename = Str::slug($request->name) . '-' . time() . '.webp';

            // Convert and save as webp
            $webpImage = Image::make($image)->encode('webp', 85);
            $path = storage_path('app/public/categories/' . $filename);
            $webpImage->save($path);

            $imagePath = 'categories/' . $filename;
        }

        $category->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'parent_id' => $request->parent_id,
            'image' => $imagePath,
            'description' => $request->description,
            'is_active' => $request->is_active,
        ]);

        return response()->json($category);
    }

    public function destroy($id)
    {
        $category = Category::findOrFail($id);

        // Delete image if exists
        if ($category->image) {
            Storage::delete('public/' . $category->image);
        }

        $category->delete();

        return response()->json(['message' => 'Category deleted successfully']);
    }
}
