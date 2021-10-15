<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class MainTokenMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (
            App::environment() === 'production' &&
            (!$request->get('token') || $request->get('token') !== config('app.main_token'))
        ) {
            return redirect(RouteServiceProvider::HOME);
        }

        return $next($request);
    }
}
