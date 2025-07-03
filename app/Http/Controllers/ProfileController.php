<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;


use App\Http\Controllers\FileController;

use App\Models\Member;
use App\Models\Auction;

class ProfileController extends Controller
{
    /**
     * Show the member for a given username.
     */
    public function show($username)
    {

        $user = Member::where('username', $username)->firstOrFail();
        
        $averageRating = $user->averageRating;

        return view('pages.profile', compact('user', 'averageRating'));
    }

    public function edit()
    {
        $user = auth()->user();
        return view('pages.edit-profile', compact('user'));
    }

    public function update(Request $request)
{
    $user = auth()->user();

    $validated = $request->validate([
        'username' => 'required|string|max:64|unique:member,username,' . $user->id,
        'first_name' => 'nullable|string|max:64',
        'last_name' => 'nullable|string|max:64',
        'email' => 'required|email|max:255|unique:member,email,' . $user->id,
        'address' => 'nullable|string|max:255',
        'current_password' => 'nullable|string',
        'password' => 'nullable|string|min:8|confirmed',
        'picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        'type' => 'string|max:64',
    ]);

    // Update password if needed
    if ($request->filled('password')) {
        if (!$request->filled('current_password') || !Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Your current password is incorrect.']);
        }
        $user->password = bcrypt($request->password);
    }

    // Handle profile picture upload
    if ($request->hasFile('picture')) {
        $user->profile_pic = FileController::upload($request);
    }

        // Update other fields
        $user->update([
            'username' => $validated['username'],
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'address' => $validated['address'],
        ]);

    return redirect()->route('profile', ['username' => $user->username])
        ->with('success', 'Profile updated successfully!');
}



    public function showAuctionsProfile($username)
{
    $user = Member::where('username', $username)->firstOrFail();

    // Paginate created auctions
    $createdAuctions = Auction::where('owner_id', $user->id)
        ->with(['highestBid', 'category', 'itemRarity'])
        ->distinct()
        ->paginate(5, ['*'], 'createdAuctionsPage'); 

    // Paginate bidded auctions
    $biddedAuctions = Auction::whereHas('bids', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
        ->with(['highestBid', 'category', 'itemRarity'])
        ->distinct()
        ->paginate(5, ['*'], 'biddedAuctionsPage');

    // Paginate followed auctions
    $followedAuctions = Auction::whereHas('followingMembers', function ($query) use ($user) {
            $query->where('follower_id', $user->id);
        })
        ->where('end_date', '>', now())
        ->with(['highestBid', 'category', 'itemRarity'])
        ->distinct()
        ->paginate(5, ['*'], 'followedAuctionsPage');

    $averageRating = $user->averageRating;

    return view('pages.profile', compact('user', 'createdAuctions', 'biddedAuctions', 'followedAuctions', 'averageRating'));
}


    public function deleteAccount(Request $request)
{
    $user = auth()->user();

    $highestBidderAuctions = Auction::whereHas('highestBid', function ($query) use ($user) {
        $query->where('user_id', $user->id);
    })->where('end_date', '>', now())->exists();

    if ($highestBidderAuctions) {
        return back()->withErrors(['error' => 'You cannot delete your account while you are the highest bidder for an active auction.']);
    }
    $ownerAuctions = Auction::where('owner_id', $user->id)
        ->where('end_date', '>', now())
        ->exists();

    if ($ownerAuctions) {
        return back()->withErrors(['error' => 'You cannot delete your account while you are the owner of a scheduled or active auction.']);
    }

    if ($user->profile_pic){
        Storage::disk('lbaw24114')-> delete('profile_type/' . $user->profile_pic);
    }

    // Anonymize user data
    $user->update([
        'first_name' => 'Deleted',
        'last_name' => 'User',
        'email' => 'deleted_user_' . $user->id . '@example.com',
        'username' => 'deleted_user_' . $user->id,
        'password' => hash('sha256', $user->id),
        'profile_pic' => 'no_image.png',
        'address' => null,
        'blocked' => true,
        'credit' => 0,
        'remember_token' => null,
    ]);

    $user->notifications()->delete();
    auth()->logout();

    return redirect()->route('auctions.home')->with('success', 'Your account has been deleted and data anonymized.');
}

}