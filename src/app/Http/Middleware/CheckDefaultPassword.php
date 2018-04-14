<?php

namespace App\Http\Middleware;

use Closure;
use Hash;
use Auth;

/**
 * Middleware that checks if the user has already changed their password.
 * This will redirect the user to the change password page if not.
 */
class CheckDefaultPassword
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (!env('DISABLE_PASSWORD_RESET', false)) {
            if (Hash::check(env('DEFAULT_ADMIN_PASSWORD', 'admin'), Auth::user()->password)) {
                return redirect('password/change');
            }
        }
        return $next($request);
    }
}
