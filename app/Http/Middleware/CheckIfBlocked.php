<?php
namespace App\Http\Middleware;

use Closure;

class CheckIfBlocked
{
    public function handle($request, Closure $next)
    {
        if (auth()->check() && auth()->user()->blocked) {
            auth()->logout();
            return redirect()->route('auctions.home')->with('error', 'Your account has been blocked by an admin.');
        }
        return $next($request);
    }
}