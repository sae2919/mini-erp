<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminOnly
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check() || !auth()->user()->hasRole('admin')) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Forbidden'], 403);
            }

            return redirect()->route('dashboard')
                ->with('error', 'Access denied. Admin privileges required.');
        }

        return $next($request);
    }
}
