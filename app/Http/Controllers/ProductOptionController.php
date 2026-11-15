<?php

namespace App\Http\Controllers;

use App\Models\ProductOption;
use Illuminate\Http\Request;

class ProductOptionController extends Controller
{
    public function index(Request $request)
    {
        $query = ProductOption::with('product');

        if ($request->has('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        return response()->json(['success' => true, 'data' => $query->get()]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'name' => 'required|string|max:150',
        ]);
        $validated['created_at'] = now();

        $option = ProductOption::create($validated);

        return response()->json(['success' => true, 'message' => 'Product option created', 'data' => $option], 201);
    }

    public function show($id)
    {
        $option = ProductOption::with('product')->find($id);
        if (!$option) {
            return response()->json(['success' => false, 'message' => 'Option not found'], 404);
        }
        return response()->json(['success' => true, 'data' => $option]);
    }

    public function update(Request $request, $id)
    {
        $option = ProductOption::find($id);
        if (!$option) {
            return response()->json(['success' => false, 'message' => 'Option not found'], 404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:150',
        ]);

        $option->update($validated);
        return response()->json(['success' => true, 'message' => 'Option updated', 'data' => $option]);
    }

    public function destroy($id)
    {
        $option = ProductOption::find($id);
        if (!$option) {
            return response()->json(['success' => false, 'message' => 'Option not found'], 404);
        }

        $option->delete();
        return response()->json(['success' => true, 'message' => 'Option deleted']);
    }
}
