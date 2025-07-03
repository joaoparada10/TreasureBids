<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Admin;
use App\Models\Member;
use App\Models\Auction;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    /**
     * Show the admin dashboard.
     */
    public function dashboard()
    {
        return view('pages.admin_dashboard');
    }

    public function index()
    {
        $admins = Admin::all();
        return view('pages.admin_dashboard', compact('admins'));
    }

    public function create()
    {
        return view('admins.create');
    }

    public function store(Request $request)
{
    $validated = $request->validate([
        'username' => [
            'required',
            'string',
            'max:255',
            function ($attribute, $value, $fail) {
                if (Admin::where('username', $value)->exists() || Member::where('username', $value)->exists()) {
                    $fail("The username has already been taken by another admin or member.");
                }
            },
        ],
        'password' => 'required|string|min:8',
    ]);

    Admin::create([
        'username' => $validated['username'],
        'password' => bcrypt($validated['password']),
    ]);

    return redirect()->route('admin.manageAdmins')->with('success', 'Admin created successfully!');
}


    public function edit($id)
    {
        $admin = Admin::findOrFail($id);
        return view('admins.edit', compact('admin'));
    }

    public function update(Request $request, $id)
{
    $admin = Admin::findOrFail($id);

    $validated = $request->validate([
        'username' => [
            'required',
            'string',
            'max:255',
            function ($attribute, $value, $fail) use ($id) {
                $adminExists = Admin::where('username', $value)->where('id', '!=', $id)->exists();
                $memberExists = Member::where('username', $value)->exists();

                if ($adminExists || $memberExists) {
                    $fail("The username has already been taken by another admin or member.");
                }
            },
        ],
        'password' => 'nullable|string|min:8',
    ]);

    $admin->update([
        'username' => $validated['username'],
    ]);

    return redirect()->route('admin.manageAdmins')->with('success', 'Admin updated successfully!');
}


    public function destroy($id)
    {
        $admin = Admin::findOrFail($id);
        $admin->delete();

        return redirect()->route('admin.manageAdmins')->with('success', 'Admin deleted successfully!');
    }
    
    public function manageMembers()
    {
        $members = Member::paginate(10); 

        return view('pages.manage_members', ['members' => $members]);
    }

    public function createMember(Request $request)
    {   
        if($request->input('blocked') == 'on'){
            $blocked = true;
        } else {
            $blocked = false;
        }

        $validated = $request->validate([
            'username' => 'required|string|max:255|unique:member,username',
            'email' => 'required|email|unique:member,email',
            'password' => 'required|string|min:8|confirmed',
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'credit' => 'nullable|numeric|min:0',
        ]);

        $member = new Member();
        $member->username = $validated['username'];
        $member->email = $validated['email'];
        $member->password = bcrypt($validated['password']);
        $member->first_name = $validated['first_name'] ?? null;
        $member->last_name = $validated['last_name'] ?? null;
        $member->address = $validated['address'] ?? null;
        $member->credit = $validated['credit'] ?? 0;
        $member->blocked = $blocked;
        $member->save();

        return redirect()->route('admin.manageMembers')->with('success', 'Member created successfully.');
    }
    


    public function removeMember($id)
    {
        $member = Member::findOrFail($id);
    
        // Check if the member is the highest bidder in any active auction
        $highestBidderAuctions = Auction::whereHas('highestBid', function ($query) use ($member) {
            $query->where('user_id', $member->id);
        })->where('end_date', '>', now())->exists();
    
        if ($highestBidderAuctions) {
            return redirect()->route('admin.manageMembers')->withErrors(['error' => 'You cannot remove this member as they are the highest bidder for an active auction.']);
        }
    
        // Check if the member is the owner of any active or scheduled auctions
        $ownerAuctions = Auction::where('owner_id', $member->id)
            ->where('end_date', '>', now())
            ->exists();
    
        if ($ownerAuctions) {
            return redirect()->route('admin.manageMembers')->withErrors(['error' => 'You cannot remove this member as they are the owner of a scheduled or active auction.']);
        }
    
        // If the member has a profile picture, delete it from storage
        if ($member->profile_pic) {
            Storage::disk('lbaw24114')->delete('profile_type/' . $member->profile_pic);
        }
    
        // Anonymize member data
        $member->update([
            'first_name' => 'Deleted',
            'last_name' => 'User',
            'email' => 'deleted_user_' . $member->id . '@example.com',
            'username' => 'deleted_user_' . $member->id,
            'password' => hash('sha256', $member->id),
            'profile_pic' => 'no_image.png',
            'address' => null,
            'blocked' => true,
            'credit' => 0,
            'remember_token' => null,
        ]);
    
        // Remove notifications
        $member->notifications()->delete();
    
        return redirect()->route('admin.manageMembers')->with('success', 'Member anonymized successfully.');
    }

    /**
     * Manage auctions (example method).
     */
    public function manageAuctions()
    {
        $auctions = Auction::paginate(10); 

        return view('pages.manage_auctions', ['auctions' => $auctions]);
    }

    public function manageAdmins()
    {
        $admins = Admin::all();

        return view('pages.manage_admins', ['admins' => $admins]);
    }

}