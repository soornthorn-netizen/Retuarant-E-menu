<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ColorStructure;
use Illuminate\Http\Request;
use Throwable;

class ColorStructureController extends Controller
{
    /**
     * Get the active color structure (for website/app theme).
     * Accessible via GET /api/color-structure
     */
    public function current(Request $request)
    {
        $color = null;

        try {
            $query = ColorStructure::query();

            if ($request->has('restaurant_id')) {
                $query->where('restaurant_id', $request->restaurant_id);
            } else {
                $query->where('is_active', true);
            }

            $color = $query->first();
        } catch (Throwable $e) {
            // Fallback to configuration default if database is offline or not yet migrated
            $color = null;
        }

        $primary = $color->primary_color ?? config('theme.colors.primary.hex', '#2563EB');
        $secondary = $color->secondary_color ?? config('theme.colors.secondary.hex', '#60A5FA');
        $accent = $color->accent_color ?? config('theme.colors.accent.hex', '#F59E0B');
        $background = $color->background_color ?? config('theme.colors.background.hex', '#F8FAFC');

        return response()->json([
            'success' => true,
            'message' => 'Active color structure retrieved successfully',
            'data' => [
                'theme_name' => $color->theme_name ?? 'Default Restaurant Theme',
                'colors' => [
                    'primary' => [
                        'name' => 'Primary',
                        'hex' => $primary,
                        'usage' => 'Buttons and Navbar',
                        'khmer_label' => 'ពណ៌ចម្បង សម្រាប់ Buttons និង Navbar',
                    ],
                    'secondary' => [
                        'name' => 'Secondary',
                        'hex' => $secondary,
                        'usage' => 'Secondary Elements & Borders',
                        'khmer_label' => 'ពណ៌បន្ទាប់បន្សំ',
                    ],
                    'accent' => [
                        'name' => 'Accent',
                        'hex' => $accent,
                        'usage' => 'Highlight and Badges',
                        'khmer_label' => 'ពណ៌ Highlight និង Badges',
                    ],
                    'background' => [
                        'name' => 'Background',
                        'hex' => $background,
                        'usage' => 'Website Background',
                        'khmer_label' => 'ពណ៌ផ្ទៃខាងក្រោយ Website',
                    ],
                ],
                'css_variables' => [
                    '--color-primary' => $primary,
                    '--color-secondary' => $secondary,
                    '--color-accent' => $accent,
                    '--color-background' => $background,
                ],
            ],
        ]);
    }

    /**
     * Display a listing of color structures.
     */
    public function index(Request $request)
    {
        $query = ColorStructure::with('restaurant');

        if ($request->has('restaurant_id')) {
            $query->where('restaurant_id', $request->restaurant_id);
        }

        $colors = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $colors,
        ]);
    }

    /**
     * Store a newly created color structure.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'restaurant_id' => 'nullable|exists:restaurants,id',
            'theme_name' => 'nullable|string|max:100',
            'primary_color' => ['required', 'regex:/^#([a-fA-F0-9]{6})$/'],
            'secondary_color' => ['required', 'regex:/^#([a-fA-F0-9]{6})$/'],
            'accent_color' => ['required', 'regex:/^#([a-fA-F0-9]{6})$/'],
            'background_color' => ['required', 'regex:/^#([a-fA-F0-9]{6})$/'],
            'is_active' => 'nullable|boolean',
        ]);

        $validated['theme_name'] = $validated['theme_name'] ?? 'Custom Theme';
        $validated['is_active'] = $validated['is_active'] ?? true;

        if ($validated['is_active']) {
            ColorStructure::where('is_active', true)->update(['is_active' => false]);
        }

        $colorStructure = ColorStructure::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Color structure created successfully',
            'data' => $colorStructure,
        ], 201);
    }

    /**
     * Display the specified color structure.
     */
    public function show($id)
    {
        $colorStructure = ColorStructure::with('restaurant')->find($id);

        if (!$colorStructure) {
            return response()->json([
                'success' => false,
                'message' => 'Color structure not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $colorStructure,
        ]);
    }

    /**
     * Update the specified color structure.
     */
    public function update(Request $request, $id)
    {
        $colorStructure = ColorStructure::find($id);

        if (!$colorStructure) {
            return response()->json([
                'success' => false,
                'message' => 'Color structure not found',
            ], 404);
        }

        $validated = $request->validate([
            'restaurant_id' => 'nullable|exists:restaurants,id',
            'theme_name' => 'nullable|string|max:100',
            'primary_color' => ['sometimes', 'required', 'regex:/^#([a-fA-F0-9]{6})$/'],
            'secondary_color' => ['sometimes', 'required', 'regex:/^#([a-fA-F0-9]{6})$/'],
            'accent_color' => ['sometimes', 'required', 'regex:/^#([a-fA-F0-9]{6})$/'],
            'background_color' => ['sometimes', 'required', 'regex:/^#([a-fA-F0-9]{6})$/'],
            'is_active' => 'nullable|boolean',
        ]);

        if (!empty($validated['is_active']) && $validated['is_active']) {
            ColorStructure::where('id', '!=', $colorStructure->id)
                          ->where('is_active', true)
                          ->update(['is_active' => false]);
        }

        $colorStructure->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Color structure updated successfully',
            'data' => $colorStructure,
        ]);
    }

    /**
     * Remove the specified color structure.
     */
    public function destroy($id)
    {
        $colorStructure = ColorStructure::find($id);

        if (!$colorStructure) {
            return response()->json([
                'success' => false,
                'message' => 'Color structure not found',
            ], 404);
        }

        $colorStructure->delete();

        return response()->json([
            'success' => true,
            'message' => 'Color structure deleted successfully',
        ]);
    }
}
