<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Category::with(['children', 'languages.language']);

        if ($request->has('restaurant_id')) {
            $query->where('restaurant_id', $request->restaurant_id);
        }

        if ($request->boolean('parents_only')) {
            $query->whereNull('parent_id');
        }

        if ($request->has('parent_id')) {
            $query->where('parent_id', $request->parent_id);
        }

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $categories = $query->orderBy('name', 'asc')->paginate($request->get('per_page', 20));

        return response()->json(['success' => true, 'data' => $categories]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'parent_id' => 'nullable|exists:categories,id',
            'restaurant_id' => 'nullable|exists:restaurants,id',
            'name' => 'required|string|max:150',
            'image' => 'nullable|string|max:255',
            'slug' => 'nullable|string|max:255',
            'languages' => 'nullable|array',
            'languages.*.language_id' => 'required_with:languages|exists:languages,id',
            'languages.*.name' => 'required_with:languages|string|max:150',
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $languages = $validated['languages'] ?? [];
        unset($validated['languages']);

        $category = Category::create($validated);

        if (!empty($languages)) {
            foreach ($languages as $lang) {
                $category->languages()->create([
                    'language_id' => $lang['language_id'],
                    'name' => $lang['name'],
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Category created successfully',
            'data' => $category->load(['children', 'languages.language']),
        ], 201);
    }

    public function show($id)
    {
        $category = Category::with(['parent', 'children', 'products.variations', 'languages.language'])->find($id);

        if (!$category) {
            return response()->json(['success' => false, 'message' => 'Category not found'], 404);
        }

        return response()->json(['success' => true, 'data' => $category]);
    }

    public function update(Request $request, $id)
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json(['success' => false, 'message' => 'Category not found'], 404);
        }

        $validated = $request->validate([
            'parent_id' => 'nullable|exists:categories,id',
            'restaurant_id' => 'nullable|exists:restaurants,id',
            'name' => 'sometimes|required|string|max:150',
            'image' => 'nullable|string|max:255',
            'slug' => 'nullable|string|max:255',
        ]);

        if (isset($validated['name']) && empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $category->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Category updated successfully',
            'data' => $category->load(['children', 'languages.language']),
        ]);
    }

    public function destroy($id)
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json(['success' => false, 'message' => 'Category not found'], 404);
        }

        $category->delete();

        return response()->json(['success' => true, 'message' => 'Category deleted successfully']);
    }
}
