<?php

namespace PlusinfoLab\Logstation\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class Authorize
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        if (!Gate::allows('viewLogstation')) {
            abort(403, 'Unauthorized access to LogStation.');
        }

        return $next($request);
    }
}
