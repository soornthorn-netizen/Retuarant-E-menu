<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    // GET ALL
    public function index()
    {
        $data = Profile::all()->map(function ($item) {
            return [
                'id' => $item->id,
                'image' => $item->image
                    ? asset('storage/' . $item->image)
                    : null,
            ];
        });

        return response()->json($data, 200);
    }

    // STORE
    public function store(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $path = $request->file('image')->store('profiles', 'public');

        $data = Profile::create([
            'image' => $path,
        ]);

        return response()->json([
            'id' => $data->id,
            'image' => asset('storage/' . $data->image),
        ], 201);
    }

    // SHOW
    public function show($id)
    {
        $item = Profile::findOrFail($id);

        return response()->json([
            'id' => $item->id,
            'image' => $item->image
                ? asset('storage/' . $item->image)
                : null,
        ], 200);
    }

    // UPDATE
    public function update(Request $request, $id)
    {
        $data = Profile::findOrFail($id);

        if ($request->hasFile('image')) {
            $request->validate([
                'image' => 'image|mimes:jpg,jpeg,png|max:2048',
            ]);

            if ($data->image && Storage::disk('public')->exists($data->image)) {
                Storage::disk('public')->delete($data->image);
            }

            $path = $request->file('image')->store('profiles', 'public');

            $data->image = $path;
            $data->save();
        }

        return response()->json([
            'id' => $data->id,
            'image' => asset('storage/' . $data->image),
        ], 200);
    }

    // DELETE
    public function destroy($id)
    {
        $data = Profile::findOrFail($id);

        if ($data->image && Storage::disk('public')->exists($data->image)) {
            Storage::disk('public')->delete($data->image);
        }

        $data->delete();

        return response()->json([
            'message' => 'Deleted successfully',
        ], 200);
    }
}

