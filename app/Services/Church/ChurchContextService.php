<?php

namespace App\Services\Church;

use App\Models\Church;
use App\Models\ChurchBranch;
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

        if (preg_match('/^([a-z0-9-]+)\.wauminilink\.[a-z.]+$/i', $host, $matches)) {
            $slug = strtolower($matches[1]);

            if ($slug !== 'www' && ($church = Church::query()->where('slug', $slug)->first())) {
                return $church;
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

    public function resolveRegistrationBranch(Request $request, Church $church): ?ChurchBranch
    {
        if (! $church->branches_enabled) {
            return null;
        }

        $code = $request->string('branch')->trim()->toString();

        if ($code === '') {
            return null;
        }

        return $church->branches()
            ->active()
            ->where('code', $code)
            ->first();
    }

    public function registrationUrl(Church $church, ?ChurchBranch $branch = null): string
    {
        return $this->appendRegistrationBranch($church->portalUrl('/register'), $branch);
    }

    public function registrationSubdomainUrl(Church $church, ?ChurchBranch $branch = null): string
    {
        return $this->appendRegistrationBranch($church->subdomainUrl('/register'), $branch);
    }

    private function appendRegistrationBranch(string $url, ?ChurchBranch $branch): string
    {
        if (! $branch?->code) {
            return $url;
        }

        $separator = str_contains($url, '?') ? '&' : '?';

        return $url.$separator.http_build_query(['branch' => $branch->code]);
    }

    public function loginUrl(Church $church): string
    {
        return $church->portalUrl('/login');
    }

    public function loginSubdomainUrl(Church $church): string
    {
        return $church->subdomainUrl('/login');
    }
}
