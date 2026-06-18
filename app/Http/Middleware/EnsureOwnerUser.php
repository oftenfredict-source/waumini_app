<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOwnerUser
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->isOwnerUser()) {
            if ($request->expectsJson()) {
                abort(403, 'Unauthorized. Owner access required.');
            }

            return redirect()->route('owner.login')
                ->with('error', 'Please log in with an owner account.');
        }

        return $next($request);
    }
}
