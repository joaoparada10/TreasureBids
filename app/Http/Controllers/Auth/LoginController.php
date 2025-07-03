<?php
 
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

use Illuminate\View\View;

use App\Models\Admin;

use App\Models\Member;

class LoginController extends Controller
{

    /**
     * Display a login form.
     */
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect('/home');
        } else {
            return view('auth.login');
        }
    }

    /**
     * Handle an authentication attempt.
     */
        public function authenticate(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'login' => ['required'], // Either email or username
            'password' => ['required'],
        ]);

        $loginField = filter_var($credentials['login'], FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        // Determine the guard to use (admin or member)
        $guard = $this->determineGuard($loginField, $credentials['login']);

        if ($guard && Auth::guard($guard)->attempt([$loginField => $credentials['login'], 'password' => $credentials['password']], $request->filled('remember'))) {
            $request->session()->regenerate();

            if ($guard !== 'admin' && Auth::user()->blocked) {
            Auth::logout();
            return back()->withErrors([
                'login' => 'Your account has been blocked by an admin.',
            ])->onlyInput('login');
            }

            return $guard === 'admin'
            ? redirect()->route('admin.dashboard')->withSuccess('You have logged in as admin successfully!')
            : redirect()->route('auctions.home')->withSuccess('You have logged in successfully!');
        }

        return back()->withErrors([
            'login' => 'The provided credentials do not match our records.',
        ])->onlyInput('login');
    }

    /**
     * Determine the guard to use based on the login field and value.
     */
    protected function determineGuard(string $loginField, string $loginValue): ?string
{
    if ($loginField === 'email' && Member::where('email', $loginValue)->exists()) {
        return 'web';
    }

    if ($loginField === 'username') {
        if (Admin::where('username', $loginValue)->exists()) {
            return 'admin';
        }

        if (Member::where('username', $loginValue)->exists()) {
            return 'web';
        }
    }

    return null;
}


    /**
     * Log out the user from application.
     */
    public function logout(Request $request)
    {
        Auth::guard('web')->logout();
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login')
            ->withSuccess('You have logged out successfully!');
    } 
}
