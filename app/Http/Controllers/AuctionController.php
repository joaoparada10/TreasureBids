<?php

namespace App\Http\Controllers;

use App\Models\Auction;

use App\Models\Category;

use App\Models\FollowedAuction;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

use App\Http\Controllers\FileController;

use App\Events\FollowedAuctionCancelled;
use App\Events\OwnedAuctionCancelledNotification;


class AuctionController extends Controller
{

    public function home()
    {
        $auctions = Auction::with(['owner', 'highestBid', 'category', 'itemRarity', 'rating'])
            ->where('status', '!=', 'Concluded')
            ->where('status', '!=', 'Cancelled')
            ->where('end_date', '>', now())
            ->get();

        $categories = Category::all();

        return view('pages.homepage', compact('categories', 'auctions'));
    }

    /**
     * Display a listing of the auctions.
     */
    public function index()
    {
        // Fetch all valid auctions along with related data
        $auctions = Auction::with(['owner', 'highestBid', 'category', 'itemRarity', 'rating'])
            ->where('status', '!=', 'Concluded')
            ->where('status', '!=', 'Cancelled')
            ->where('end_date', '>', now())
            ->get();

        $categories = Category::all();

        return view('pages.homepage', compact('categories', 'auctions'));
    }

    /**
     * Display the details of a specific auction.
     */
    public function show($id)
    {
        $auction = Auction::with([
            'owner',
            'bids.bidder',
            'highestBid',
            'itemRarity',
            'followingMembers',
        ])->findOrFail($id);

        $averageRating = $auction->owner->averageRating;

        $isFollowing = $auction->isFollowedByAuthUser();

        return view('pages.auction_details', compact('auction', 'isFollowing', 'averageRating'));
    }


    public function create()
    {
        if (!Auth::check()) {
            return redirect('/login')->with('error', 'You must be logged in to create an auction.');
        }

        $categories = Category::all();
        return view('pages.auction_create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'starting_price' => 'required|numeric|min:0',
            'buyout_price' => 'nullable|numeric|gte:starting_price',
            'starting_date' => 'required|date',
            'end_date' => 'required|date|after:starting_date',
            'picture' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
            'categories.*' => 'exists:category,id',
            'categories' => 'required|array|max:2',
        ]);

        if ($request->hasFile('picture')) {
            $file = $request->file('picture');
            $fileName = $file->hashName();
            $file->storeAs('auction_type', $fileName, 'lbaw24114');
            $validated['picture'] = $fileName;
        }

        $now = now();
        if ($validated['starting_date'] > $now) {
            $validated['status'] = 'Scheduled';
        } elseif ($validated['starting_date'] <= $now && $validated['end_date'] > $now) {
            $validated['status'] = 'Active';
        }

        $validated['owner_id'] = auth()->id();

        $auction = Auction::create($validated);

        $auction->category()->attach($request->categories);

        return redirect()->route('auction.show', ['id' => $auction->id])->with('success', 'Auction created successfully.');
    }


    public function toggleFollowStatus(Request $request, $id)
    {

        $user = Auth::user();

        $auction = Auction::find($id);
        if (!$auction) {
            return response()->json(['error' => 'Auction not found'], 404);
        }

        if ($auction->followingMembers()->where('follower_id', $user->id)->exists()) {
            $auction->followingMembers()->detach($user->id);
            return response()->json(['message' => 'Auction unfollowed'], 200);
        } else {
            $auction->followingMembers()->attach($user->id);
            return response()->json(['message' => 'Auction followed'], 200);
        }
    }

    /**
     * Update an auction.
     */
    public function update(Request $request, $id)
    {
        $auction = Auction::findOrFail($id);
        if ($auction->status === 'Concluded') {
            return redirect()->back()->with('error', 'Cannot update a concluded auction.');
        }
        if ($auction->status === 'Cancelled') {
            return redirect()->back()->with('error', 'Cannot update a cancelled auction.');
        }
        if ($auction->bids()->exists()) {
            return redirect()->back()->with('error', 'Cannot update an auction with active bids.');
        }
        $this->authorize('update', $auction);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'starting_price' => 'required|numeric|min:0',
            'buyout_price' => 'nullable|numeric|gte:starting_price',
            'starting_date' => 'required|date',
            'end_date' => 'required|date|after:starting_date',
            'picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'categories' => 'nullable|array|max:2',
            'categories.*' => 'exists:category,id',
        ]);

        // Handle picture upload
        if ($request->hasFile('picture')) {
            $validated['picture'] = FileController::upload($request);
        }

        // Update auction
        $auction->update($validated);

        // Sync categories
        $auction->category()->sync($request->categories ?? []);

        return redirect()->route('auction.show', $auction->id)->with('success', 'Auction updated successfully!');
    }


    public function admin_update(Request $request, $id)
    {
        $auction = Auction::findOrFail($id);

        $this->authorize('update', $auction);

        // Validation rules
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'starting_price' => 'required|numeric|min:0',
            'buyout_price' => 'nullable|numeric|gte:starting_price',
            'starting_date' => 'required|date',
            'end_date' => 'required|date|after:starting_date',
        ]);

        $auction->update($validated);

        return redirect()->back()->with('success', 'Auction updated successfully!');
    }



    /**
     * Delete an auction.
     */
    public function destroy($id)
    {
        $auction = Auction::findOrFail($id);

        event(new OwnedAuctionCancelledNotification($auction->id, $auction->owner_id));

        $this->authorize('cancel', $auction);

        if ($auction->bids()->exists()) {
            return redirect()->back()->with('error', 'Cannot cancel an auction with active bids.');
        }

        if ($auction->status === 'Cancelled') {
            return redirect()->back()->with('error', 'Auction is already cancelled.');
        }
        if ($auction->status === 'Concluded') {
            return redirect()->back()->with('error', 'Auction is already concluded.');
        }

        $followers = FollowedAuction::where('auction_id', $auction->id)->pluck('follower_id');

        event(new FollowedAuctionCancelled($auction->id, $followers));

        if ($auction->status === 'Scheduled') {
            $auction->starting_date = now()->subsecond();
        }

        $auction->status = 'Cancelled';
        $auction->end_date = now();
        $auction->save();

        return redirect()->back()->with('success', 'Auction removed successfully!');
    }

    public function search(Request $request)
    {
        $query = $request->input('q');

        if (!$query) {
            return response()->json(['auctions' => []]);
        }


        $auctions = Auction::query()
            ->whereRaw("search_vector @@ plainto_tsquery('english', ?)", [$query])
            ->where('end_date', '>', now()) // Exclude concluded auctions
            ->orWhere('title', 'ILIKE', "%{$query}%")
            ->get(['id', 'title']);

        return response()->json(['auctions' => $auctions]);
    }


    public function edit($id)
    {
        $auction = Auction::with('category')->findOrFail($id);

        if ($auction->owner_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        if ($auction->bids()->exists()) {
            return redirect()->back()->with('error', 'You cannot edit an auction with active bids.');
        }
        if ($auction->status === 'Concluded') {
            return redirect()->back()->with('error', 'You cannot edit a concluded auction.');
        }
        if ($auction->status === 'Cancelled') {
            return redirect()->back()->with('error', 'You cannot edit a cancelled auction.');
        }

        $categories = Category::all(); // Fetch all categories

        return view('pages.auction_edit', compact('auction', 'categories'));
    }

    public function cancel(Auction $auction)
    {
        if (!Auth::check()) {
            abort(403, 'User is not authenticated.');
        }

        $this->authorize('cancel', $auction);

        if ($auction->bids()->exists()) {
            return redirect()->back()->with('error', 'Cannot cancel an auction with active bids.');
        }

        $followers = FollowedAuction::where('auction_id', $auction->id)->pluck('follower_id');

        event(new FollowedAuctionCancelled($auction->id, $followers));

        if ($auction->status === 'Scheduled') {
            $auction->starting_date = now()->subsecond();
        }

        $auction->status = 'Cancelled';
        $auction->end_date = now();
        $auction->save();

        return redirect()->route('auctions.home')->with('success', 'Auction cancelled successfully.');
    }



    public function showSearchResults(Request $request)
{
    $query = $request->input('q');
    $category = $request->input('category');
    $minPrice = $request->input('min_price');
    $maxPrice = $request->input('max_price');
    $status = $request->input('status');

    $auctions = Auction::query()
        ->selectRaw("
            id, 
            title, 
            description,
            picture, 
            starting_date, 
            end_date,
            ts_rank(
                setweight(to_tsvector('english', coalesce(title, '')), 'A') ||
                setweight(to_tsvector('english', coalesce(description, '')), 'B'),
                to_tsquery('english', ?)
            ) AS rank", [$query . ':*']) // Add prefix match with :*
        ->where('end_date', '>', now()) // Always apply this filter
        ->where(function ($queryBuilder) use ($query) {
            $queryBuilder->whereRaw("
                (setweight(to_tsvector('english', coalesce(title, '')), 'A') ||
                setweight(to_tsvector('english', coalesce(description, '')), 'B')) @@ to_tsquery('english', ?)
            ", [$query . ':*']) // Add prefix match with :*
            ->orWhere('title', 'ILIKE', '%' . $query . '%') // Fallback for partial match in title
            ->orWhere('description', 'ILIKE', '%' . $query . '%'); // Fallback for partial match in description
        });

    if ($category) {
        $auctions->where('category', $category);
    }

    if ($status) {
        $auctions->where('status', $status);
    }

    $auctions = $auctions->orderByDesc('rank')
                         ->paginate(12);

    return view('pages.search_results', compact('auctions', 'query'));
}

}
