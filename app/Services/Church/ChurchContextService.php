<?php

namespace App\Services\Church;

use App\Models\Church;
use App\Models\ChurchDomain;
use Illuminate\Http\Request;

class ChurchContextService
{
    public function resolveFromRequest(Request $request): ?Church
    {
        $host = strtolower($request->getHost());

        $domain = ChurchDomain::query()
            ->where('domain', $host)
            ->with('church')
            ->first();

        if ($domain?->church) {
            return $domain->church;
        }

        $baseDomain = strtolower((string) config('waumini.base_domain'));

        if ($baseDomain !== '' && str_ends_with($host, '.'.$baseDomain)) {
            $slug = substr($host, 0, -(strlen($baseDomain) + 1));

            if ($slug !== '' && $slug !== 'www') {
                $church = Church::query()->where('slug', $slug)->first();

                if ($church) {
                    return $church;
                }
            }
        }

        if ($slug = $request->route('church')) {
            if ($church = Church::query()->where('slug', $slug)->first()) {
                return $church;
            }
        }

        if ($slug = $request->string('church')->trim()->toString()) {
            if ($church = Church::query()->where('slug', $slug)->first()) {
                return $church;
            }
        }

        $churches = Church::query()->orderBy('id')->get();

        return $churches->count() === 1 ? $churches->first() : null;
    }

    public function bindCurrentChurch(?Church $church): void
    {
        if ($church) {
            app()->instance('currentChurch', $church);
        }
    }

    public function current(): ?Church
    {
        if (app()->bound('currentChurch')) {
            return app('currentChurch');
        }

        return null;
    }

    public function registrationUrl(Church $church): string
    {
        return route('church.register', ['church' => $church->slug]);
    }
}
