<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

use Illuminate\View\View;

use App\Models\Member;

class RegisterController extends Controller
{
    /**
     * Display a login form.
     */
    public function showRegistrationForm(): View
    {
        return view('auth.register');
    }

    /**
     * Register a new member.
     */
    public function register(Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:250|unique:member|unique:admin,username',
            'email' => 'required|email|max:250|unique:member,email',
            'password' => 'required|min:8|confirmed',
        ]);

        Member::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password]) ||
            Auth::attempt(['username' => $request->username, 'password' => $request->password])) {
            $request->session()->regenerate();

            return redirect()->route('profile.redirect')->withSuccess('You have successfully registered & logged in!');
        }
    }
}
