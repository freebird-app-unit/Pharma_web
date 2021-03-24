<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Auth;
use App\Providers\RouteServiceProvider;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        if (! $request->expectsJson()) {
            // if(basename($request->url()) == 'logistic'){
            //     return route('logistic_login');
            // } else if ($request->getRequestUri() == '/pharma/pharmacy/login') {
            //     return route('pharmacy_login');
            // } else {
                return redirect(RouteServiceProvider::HOME);
            // }
        }
    }
}
