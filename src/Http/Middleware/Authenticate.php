<?php

namespace PhpNl\LaravelPayloadEditor\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Gate;

class Authenticate
{
    /**
     * Handle the incoming request.
     */
    public function handle($request, Closure $next)
    {
        if (Gate::check('viewLaravelPayloadEditor', [$request->user()])) {
            return $next($request);
        }

        abort(403);
    }
}
