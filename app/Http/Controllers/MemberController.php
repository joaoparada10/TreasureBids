<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\Member;

class MemberController extends Controller
{
    public function index()
    {
        // Fetch all members
        $members = Member::all();

        // Pass the members to the view
        return view('members.index', ['members' => $members]);
    }

    public function update(Request $request, $id)
{
    $member = Member::findOrFail($id);

    $this->authorize('update', $member);

    $validated = $request->validate([
        'username' => 'required|string|max:255|unique:member,username,' . $id,
        'email' => 'required|email|unique:member,email,' . $id,
        'first_name' => 'nullable|string|max:255',
        'last_name' => 'nullable|string|max:255',
        'address' => 'nullable|string|max:255',
        'credit' => 'nullable|numeric|min:0',
        'blocked' => 'required|boolean',
    ]);
    $member->update($validated);

    return redirect()->back()->with('success', 'Member updated successfully!');
}

    // é igual à update xd? usem a update
    public function edit(Request $request, $id)
{
    $member = Member::findOrFail($id);

    $this->authorize('update', $member);

    $validated = $request->validate([
        'username' => 'required|string|max:255|unique:members,username,' . $id,
        'email' => 'required|email|unique:members,email,' . $id,
        'first_name' => 'nullable|string|max:255',
        'last_name' => 'nullable|string|max:255',
        'address' => 'nullable|string|max:255',
        'credit' => 'nullable|numeric|min:0',
        'blocked' => 'required|boolean',
    ]);

    $member->update($validated);

    return response()->json(['success' => true, 'message' => 'Member updated successfully!']);
}

public function filter(Request $request)
{
    $query = $request->input('q');

    // Filter members based on query
    $members = Member::query()
        ->where('username', 'LIKE', "%{$query}%")
        ->orWhere('email', 'LIKE', "%{$query}%")
        ->orWhere('first_name', 'LIKE', "%{$query}%")
        ->orWhere('last_name', 'LIKE', "%{$query}%")
        ->get();

    return response()->json($members);
}
}