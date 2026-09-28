<?php

namespace App\Http\Middleware;

use App\Enums\ChurchStatus;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureChurchApiUser
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->isChurchPortalUser()) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden. Church access required.',
            ], 403);
        }

        $church = $user->church;

        if (! $church) {
            return response()->json([
                'success' => false,
                'message' => 'Your account is not linked to a church.',
            ], 403);
        }

        if (in_array($church->status, [ChurchStatus::Suspended, ChurchStatus::Expired], true)) {
            return response()->json([
                'success' => false,
                'message' => 'This church account is '.$church->status->value.'. Contact platform support.',
            ], 403);
        }

        app()->instance('currentChurch', $church);

        return $next($request);
    }
}
