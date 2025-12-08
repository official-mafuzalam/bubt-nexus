<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RentPost;

class RentController extends Controller
{
    // ---------------------------------------------------
    // LIST ALL RENTS WITH FILTERS
    // ---------------------------------------------------
    public function index(Request $request)
    {
        $query = RentPost::with('user')->latest();

        if ($request->category) {
            $query->where('category', $request->category);
        }

        if ($request->location) {
            $query->where('location', 'LIKE', "%{$request->location}%");
        }

        if ($request->min_rent) {
            $query->where('rent', '>=', $request->min_rent);
        }

        if ($request->max_rent) {
            $query->where('rent', '<=', $request->max_rent);
        }

        if (!is_null($request->available)) {
            $query->where('is_available', $request->available == 'true');
        }

        return response()->json($query->paginate(20));
    }

    // ---------------------------------------------------
    // CREATE RENT POST (NO IMAGES)
    // ---------------------------------------------------
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|in:room,flat,single_bed,sublet',
            'contact_number' => 'required|string',
            'rent' => 'required|integer',
            'location' => 'required|string',
            'address_detail' => 'nullable|string',
            'bedrooms' => 'nullable|integer',
            'washrooms' => 'nullable|integer',
            'available_from' => 'nullable|date',
        ]);

        $validated['user_id'] = auth()->id();

        $post = RentPost::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Rent post created successfully',
            'data' => $post
        ], 201);
    }

    // ---------------------------------------------------
    // SHOW SINGLE RENT POST
    // ---------------------------------------------------
    public function show($id)
    {
        $post = RentPost::with('user')->find($id);

        if (!$post) {
            return response()->json(['message' => 'Not found'], 404);
        }

        return response()->json($post);
    }

    // ---------------------------------------------------
    // UPDATE RENT POST (NO IMAGE LOGIC)
    // ---------------------------------------------------
    public function update(Request $request, $id)
    {
        $post = RentPost::find($id);

        if (!$post || $post->user_id != auth()->id()) {
            return response()->json(['message' => 'Unauthorized or not found'], 403);
        }

        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'category' => 'nullable|in:room,flat,single_bed,sublet',
            'contact_number' => 'nullable|string',
            'rent' => 'nullable|integer',
            'location' => 'nullable|string',
            'address_detail' => 'nullable|string',
            'bedrooms' => 'nullable|integer',
            'washrooms' => 'nullable|integer',
            'available_from' => 'nullable|date',
            'is_available' => 'nullable|boolean',
        ]);

        $post->update($validated);

        return response()->json([
            'message' => 'Rent post updated successfully',
            'data' => $post
        ]);
    }

    // ---------------------------------------------------
    // DELETE RENT POST
    // ---------------------------------------------------
    public function destroy($id)
    {
        $post = RentPost::find($id);

        if (!$post || $post->user_id != auth()->id()) {
            return response()->json(['message' => 'Unauthorized or not found'], 403);
        }

        $post->delete();

        return response()->json(['message' => 'Rent post deleted successfully']);
    }

    // ---------------------------------------------------
    // SET AVAILABILITY (Available / Rented)
    // ---------------------------------------------------
    public function setAvailability(Request $request, $id)
    {
        $post = RentPost::find($id);

        if (!$post || $post->user_id != auth()->id()) {
            return response()->json(['message' => 'Unauthorized or not found'], 403);
        }

        $request->validate([
            'is_available' => 'required|boolean'
        ]);

        $post->update([
            'is_available' => $request->is_available
        ]);

        return response()->json([
            'message' => 'Availability updated',
            'data' => $post
        ]);
    }

    // ---------------------------------------------------
    // GET MY POSTED RENT ITEMS (NO IMAGES)
    // ---------------------------------------------------
    public function myRents()
    {
        $posts = RentPost::where('user_id', auth()->id())
            ->latest()
            ->get();

        return response()->json($posts);
    }

    // ---------------------------------------------------
    // SEARCH
    // ---------------------------------------------------
    public function search(Request $request)
    {
        $query = RentPost::with('user')->latest();

        // Category filter
        if ($request->category) {
            if ($request->category === 'room') {
                $query->whereIn('category', $request->category);
            } else {
                $query->where('category', $request->category);
            }
        }

        // Location filter
        if ($request->location) {
            $query->where('location', 'LIKE', "%{$request->location}%");
        }

        // Availability filter
        if ($request->available !== null) {
            $query->where('is_available', $request->available === 'true' ? 1 : 0);
        }

        return response()->json($query->paginate(20));
    }


}
