<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Auction;
use App\Models\Bid;
use App\Events\NewBidNotification;
use App\Events\OutbiddedNotification;


class BidController extends Controller
{
    public function store(Request $request, Auction $auction)
{
    // Validate the bid input
    $request->validate([
        'bid' => 'required|numeric|min:' . ($auction->highestbid ? ($auction->highestbid->value + 1) : 1),
    ]);

    $user = auth()->user();

    if ($auction->status === 'Concluded') {
        return redirect()->back()->withErrors(['bid' => 'This auction has already concluded.']);
    }

    if ($auction->status === 'Cancelled') {
        return redirect()->back()->withErrors(['bid' => 'This auction has been canceled.']);
    }

    if ($auction->starting_date > now()) {
        return redirect()->back()->withErrors(['bid' => 'This auction has not started yet.']);
    }

    if ($auction->end_date < now()) {
        return redirect()->back()->withErrors(['bid' => 'The auction has already ended.']);
    }

    if ($auction->owner_id === $user->id) {
        return redirect()->back()->withErrors(['bid' => 'You cannot bid on your own auction.']);
    }
    if ($user->blocked) {
        return redirect()->back()->withErrors(['bid' => 'You are blocked and cannot bid on auctions.']);
    }
    if (!$user->address){
        return redirect()->back()->withErrors(['bid' => 'Please add your shipping address to your profile before placing a bid.']);
    }

    if ($auction->highestbid && $auction->highestbid->user_id === $user->id) {
        return redirect()->back()->withErrors(['bid' => 'You are already the highest bidder!']);
    }

    if ($request->input('bid') <= $auction->starting_price) {
        return redirect()->back()->withErrors(['bid' => 'Your bid must be higher than the auction starting price.']);
    }
    if ($auction->highestbid && $request->input('bid') < $auction->highestbid->value * 1.05) {
        return redirect()->back()->withErrors(['bid' => 'Your bid must be at least 5% higher than the current highest bid.']);
    }

    if ($user->credit < $request->input('bid')) {
        return redirect()->back()->withErrors(['bid' => 'You do not have enough money to place this bid!']);
    }

    // Bid is valid

    // Return credit to the previous highest bidder (if exists)
    if ($auction->highestbid) {
        $previousHighestBidder = $auction->highestbid->bidder;
        $previousHighestBidder->credit += $auction->highestbid->value;
        $previousHighestBidder->save();
        event(new OutbiddedNotification($auction->id, $previousHighestBidder->id));
    }

    // Deduct credit from bidder
    $user->credit -= $request->bid;
    $user->save();

    // Create the bid
    $bid = new Bid();
    $bid->value = $request->input('bid');
    $bid->auction_id = $auction->id;
    $bid->user_id = $user->id; // Assuming the user is logged in
    $bid->save();

    event(new NewBidNotification($auction->id, $auction->owner_id));

    // Redirect back with a success message
    return redirect()->back()->with('success', 'Your bid has been placed successfully!');
}

}
?>
