<?php

namespace App\Http\Middleware;

use App\Services\Church\ChurchContextService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function __construct(
        private readonly ChurchContextService $churchContextService,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        App::setLocale($this->resolveLocale($request));

        return $next($request);
    }

    private function resolveLocale(Request $request): string
    {
        $supported = array_keys(config('locales.supported', ['en' => [], 'sw' => []]));

        if ($request->hasSession()) {
            $sessionLocale = $request->session()->get('locale');

            if (is_string($sessionLocale) && in_array($sessionLocale, $supported, true)) {
                return $sessionLocale;
            }
        }

        $church = auth()->user()?->church
            ?? $this->churchContextService->current()
            ?? $this->churchContextService->resolveFromRequest($request);

        if ($church && in_array($church->locale, $supported, true)) {
            return $church->locale;
        }

        $appLocale = config('app.locale', 'en');

        return in_array($appLocale, $supported, true) ? $appLocale : config('locales.default', 'en');
    }
}
