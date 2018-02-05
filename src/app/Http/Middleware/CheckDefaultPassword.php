<?php

namespace App\Http\Middleware;

use Closure;
use Hash;
use Auth;

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
