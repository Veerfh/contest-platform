<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class JuryMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check() || !auth()->user()->isJury()) {
            abort(403, 'Доступ только для жюри');
        }
        
        return $next($request);
    }
}