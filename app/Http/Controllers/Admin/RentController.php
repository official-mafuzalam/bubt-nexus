<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RentPost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class RentController extends Controller
{
    /**
     * Display a listing of rent posts.
     */
    public function index(Request $request)
    {
        $query = RentPost::with('user:id,name,email')
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->input('search');
                $q->where(function ($query) use ($search) {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('location', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('category'), function ($q) use ($request) {
                $q->where('category', $request->input('category'));
            })
            ->when($request->filled('min_rent'), function ($q) use ($request) {
                $q->where('rent', '>=', $request->input('min_rent'));
            })
            ->when($request->filled('max_rent'), function ($q) use ($request) {
                $q->where('rent', '<=', $request->input('max_rent'));
            })
            ->when($request->filled('bedrooms'), function ($q) use ($request) {
                $q->where('bedrooms', $request->input('bedrooms'));
            })
            ->when($request->filled('is_available'), function ($q) use ($request) {
                $q->where('is_available', $request->input('is_available') === 'true');
            })
            ->when($request->filled('location'), function ($q) use ($request) {
                $q->where('location', 'like', "%{$request->location}%");
            })
            ->latest();

        $rentPosts = $query->paginate(12);

        // Get filter options
        $categories = RentPost::select('category')->distinct()->pluck('category');
        $bedroomOptions = RentPost::select('bedrooms')->distinct()->whereNotNull('bedrooms')->pluck('bedrooms')->sort();
        $maxRent = RentPost::max('rent');
        $minRent = RentPost::min('rent');

        return Inertia::render('admin/RentPosts/Index', [
            'rentPosts' => $rentPosts,
            'myPosts' => [], // Empty for index page
            'filters' => $request->only(['search', 'category', 'min_rent', 'max_rent', 'bedrooms', 'is_available', 'location']),
            'filterOptions' => [
                'categories' => $categories,
                'bedrooms' => $bedroomOptions,
                'max_rent' => $maxRent,
                'min_rent' => $minRent,
            ],
            'stats' => [
                'total_posts' => RentPost::count(),
                'available_posts' => RentPost::where('is_available', true)->count(),
                'average_rent' => round(RentPost::avg('rent') ?? 0),
                'total_categories' => $categories->count(),
            ],
            'isMyPostsPage' => false, // Flag to identify page type
        ]);
    }

    /**
     * Display user's rent posts.
     */
    public function myPosts(Request $request)
    {
        $query = RentPost::where('user_id', Auth::id())
            ->with('user:id,name,email')
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->input('search');
                $q->where(function ($query) use ($search) {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('location', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('category'), function ($q) use ($request) {
                $q->where('category', $request->input('category'));
            })
            ->when($request->filled('min_rent'), function ($q) use ($request) {
                $q->where('rent', '>=', $request->input('min_rent'));
            })
            ->when($request->filled('max_rent'), function ($q) use ($request) {
                $q->where('rent', '<=', $request->input('max_rent'));
            })
            ->when($request->filled('bedrooms'), function ($q) use ($request) {
                $q->where('bedrooms', $request->input('bedrooms'));
            })
            ->when($request->filled('is_available'), function ($q) use ($request) {
                $q->where('is_available', $request->input('is_available') === 'true');
            })
            ->when($request->filled('location'), function ($q) use ($request) {
                $q->where('location', 'like', "%{$request->location}%");
            })
            ->latest();

        $myPosts = $query->paginate(12);

        // Get filter options from user's posts only
        $categories = RentPost::where('user_id', Auth::id())->select('category')->distinct()->pluck('category');
        $bedroomOptions = RentPost::where('user_id', Auth::id())->select('bedrooms')->distinct()->whereNotNull('bedrooms')->pluck('bedrooms')->sort();
        $maxRent = RentPost::where('user_id', Auth::id())->max('rent');
        $minRent = RentPost::where('user_id', Auth::id())->min('rent');

        return Inertia::render('admin/RentPosts/Index', [
            'rentPosts' => $myPosts, // Using same prop name but with user's posts
            'myPosts' => [], // Empty since all posts here are user's posts
            'filters' => $request->only(['search', 'category', 'min_rent', 'max_rent', 'bedrooms', 'is_available', 'location']),
            'filterOptions' => [
                'categories' => $categories,
                'bedrooms' => $bedroomOptions,
                'max_rent' => $maxRent,
                'min_rent' => $minRent,
            ],
            'stats' => [
                'total_posts' => RentPost::where('user_id', Auth::id())->count(),
                'available_posts' => RentPost::where('user_id', Auth::id())->where('is_available', true)->count(),
                'average_rent' => round(RentPost::where('user_id', Auth::id())->avg('rent') ?? 0),
                'total_categories' => $categories->count(),
            ],
            'isMyPostsPage' => true, // Flag to identify page type
        ]);
    }


    /**
     * Show the form for creating a new rent post.
     */
    public function create()
    {
        return Inertia::render('admin/RentPosts/Create', [
            'categories' => ['room', 'flat', 'single_bed', 'sublet'],
        ]);
    }

    /**
     * Store a newly created rent post.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|in:room,flat,single_bed,sublet',
            'contact_number' => 'required|string|max:20',
            'rent' => 'required|integer|min:0',
            'location' => 'required|string|max:255',
            'address_detail' => 'nullable|string',
            'bedrooms' => 'nullable|integer|min:1',
            'washrooms' => 'nullable|integer|min:1',
            'available_from' => 'nullable|date',
            'is_available' => 'boolean',
        ]);

        $validated['user_id'] = Auth::id();

        RentPost::create($validated);

        return redirect()->route('admin.rent-posts.index')
            ->with('success', 'Rent post created successfully.');
    }

    /**
     * Display the specified rent post.
     */
    public function show(RentPost $rent)
    {
        $rent->load('user:id,name,email,created_at');

        $similarPosts = RentPost::where('id', '!=', $rent->id)
            ->where('category', $rent->category)
            ->where('is_available', true)
            ->where('location', 'like', "%{$rent->location}%")
            ->take(4)
            ->get();

        // Check if current user is authorized (owner or admin)
        $isAuthorized = Auth::check() && (Auth::id() === $rent->user_id || Auth::user()->hasRole('admin'));

        return Inertia::render('admin/RentPosts/Show', [
            'post' => $rent,
            'similarPosts' => $similarPosts,
            'userPostsCount' => RentPost::where('user_id', $rent->user_id)->count(),
            'isAuthorized' => $isAuthorized, // Pass authorization status to frontend
        ]);
    }

    /**
     * Show the form for editing the specified rent post.
     */
    public function edit(RentPost $rent)
    {
        return Inertia::render('admin/RentPosts/Edit', [
            'post' => $rent,
            'categories' => ['room', 'flat', 'single_bed', 'sublet'],
        ]);
    }

    /**
     * Update the specified rent post.
     */
    public function update(Request $request, RentPost $rent)
    {
        // Check authorization
        if (Auth::id() !== $rent->user_id && !Auth::user()->hasRole('admin')) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|in:room,flat,single_bed,sublet',
            'contact_number' => 'required|string|max:20',
            'rent' => 'required|integer|min:0',
            'location' => 'required|string|max:255',
            'address_detail' => 'nullable|string',
            'bedrooms' => 'nullable|integer|min:1',
            'washrooms' => 'nullable|integer|min:1',
            'available_from' => 'nullable|date',
            'is_available' => 'boolean',
        ]);

        $rent->update($validated);

        return redirect()->route('admin.rent-posts.show', $rent)
            ->with('success', 'Rent post updated successfully.');
    }

    /**
     * Remove the specified rent post.
     */
    public function destroy(RentPost $rent)
    {
        // Check authorization
        if (Auth::id() !== $rent->user_id && !Auth::user()->hasRole('admin')) {
            abort(403, 'Unauthorized action.');
        }

        $rent->delete();

        return redirect()->route('admin.rent-posts.index')
            ->with('success', 'Rent post deleted successfully.');
    }

    /**
     * Toggle availability of rent post.
     */
    public function toggleAvailability(int $rent)
    {
        $rent = RentPost::findOrFail($rent);

        // Check authorization
        if (Auth::id() !== $rent->user_id && !Auth::user()->hasRole('admin')) {
            abort(403, 'Unauthorized action.');
        }

        $rent->update([
            'is_available' => !$rent->is_available,
        ]);

        return back()->with('success', 'Availability updated successfully.');
    }
}