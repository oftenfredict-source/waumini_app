<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePastorApiUser
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->isPastor()) {
            return response()->json([
                'success' => false,
                'message' => 'This area is for pastors only.',
            ], 403);
        }

        return $next($request);
    }
}
