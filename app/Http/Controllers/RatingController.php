<?php

namespace App\Http\Controllers;

use App\Models\Rating;
use App\Models\Auction;
use Illuminate\Http\Request;

class RatingController extends Controller
{
    public function store(Request $request, Auction $auction)
    {
        $request->validate([
            'rating_value' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string',
        ]);

        $user = auth()->user();

        if ($auction->end_date > now()) {
            return redirect()->back()->withErrors(['error' => 'You can only rate the auction after it ends.']);
        }
        
        if ($auction->highestbid->user_id !== $user->id) {
            return redirect()->back()->withErrors(['error' => 'Only the auction winner can rate the owner.']);
        }

        if (Rating::where('rated_auction_id', $auction->id)->where('rater_id', $user->id)->exists()) {
            return redirect()->back()->withErrors(['error' => 'You have already rated this auction.']);
        }

        Rating::create([
            'rating_value' => $request->input('rating_value'),
            'comment' => $request->input('comment'),
            'rater_id' => $user->id,
            'rated_auction_id' => $auction->id,
        ]);

        return redirect()->back()->with('success', 'Your rating has been submitted.');
    }
}
