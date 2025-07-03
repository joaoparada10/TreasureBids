<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Auction;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    /* // Display all transactions for the authenticated user
    public function index()
    {
        $transactions = Transaction::with(['auction', 'buyer'])
            ->where('buyer_id', auth()->id())
            ->orWhereHas('auction', function ($query) {
                $query->where('owner_id', auth()->id());
            })
            ->orderBy('date', 'desc')
            ->get();

        return view('transactions.index', compact('transactions'));
    } */

    // Display the transaction form for the auction owner
    public function create(Auction $auction)
    {
        if (auth()->id() !== $auction->owner_id) {
            abort(403, 'Unauthorized action.');
        }

        $highestBid = $auction->highestbid;

        if (!$highestBid) {
            return redirect()->back()->withErrors(['error' => 'No highest bidder found for this auction.']);
        }

        return view('pages.create_transaction', compact('auction', 'highestBid'));
    }

    // Store the transaction and send confirmation to the highest bidder
    public function store(Request $request, Auction $auction)
    {
        $request->validate([
            'price' => 'required|numeric|min:0.01',
        ]);

        // Ensure the user is the auction owner
        if (auth()->id() !== $auction->owner_id) {
            abort(403, 'Unauthorized action.');
        }

        // Create the transaction
        $transaction = Transaction::create([
            'buyer_id' => $auction->highestbid->user_id,
            'auction_id' => $auction->id,
            'price' => $request->input('price'),
            'date' => now(),
        ]);

        return redirect()->route('pages.show_transaction', $transaction)->with('success', 'Transaction created and sent to the highest bidder.');
    }

    public function show(Transaction $transaction)
    {

        if (auth()->id() !== $transaction->buyer_id && auth()->id() !== $transaction->auction->owner_id) {
            abort(403, 'Unauthorized action.');
        }

        return view('pages.show_transaction', ['transaction' => $transaction]);
    }

}

