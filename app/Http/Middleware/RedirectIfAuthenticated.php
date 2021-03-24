<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        // switch ($guard) {
        //     case 'new_logistics' :
        //         if (Auth::guard($guard)->check()) {
        //             return redirect()->route('logistic_login');
        //         }
        //         break;
        //     case 'new_pharmacies' :
        //         if (Auth::guard($guard)->check()) {
        //             return redirect()->route('pharmacy_login');
        //         }
        //         break;
        //     case 'web' :
                if (Auth::guard($guard)->check()) {
                    return redirect(RouteServiceProvider::HOME);
                }
                // break;
        // }
        return $next($request);
    }
}
