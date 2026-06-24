<?php

namespace App\Services\Church;

use App\Models\Church;
use Illuminate\Validation\ValidationException;

class MemberIdPrefixService
{
    /** @var list<string> */
    private const STOP_WORDS = [
        'tag', 'the', 'of', 'and', 'holy', 'saint', 'st', 'church', 'chapel',
        'assembly', 'parish', 'ministry', 'fellowship', 'community', 'international',
    ];

    public function suggestFromName(string $churchName): string
    {
        $words = preg_split('/\s+/', trim($churchName)) ?: [];
        $significant = [];

        foreach ($words as $word) {
            $clean = preg_replace('/[^A-Za-z]/', '', $word) ?? '';

            if ($clean === '' || in_array(strtolower($clean), self::STOP_WORDS, true)) {
                continue;
            }

            $significant[] = $clean;
        }

        if ($significant === []) {
            $significant = array_values(array_filter(array_map(
                fn (string $word): string => preg_replace('/[^A-Za-z]/', '', $word) ?? '',
                $words
            )));
        }

        $source = $significant !== [] ? (string) end($significant) : (preg_replace('/[^A-Za-z]/', '', $churchName) ?? '');

        if ($source === '') {
            $source = 'CH';
        }

        return strtoupper(substr($source, 0, 2));
    }

    public function generateUnique(string $churchName, ?int $exceptChurchId = null): string
    {
        $word = $this->primaryWord($churchName);
        $base = $this->suggestFromName($churchName);
        $candidates = [];

        for ($length = 2; $length <= min(6, strlen($word)); $length++) {
            $candidates[] = strtoupper(substr($word, 0, $length));
        }

        $candidates[] = $base;

        for ($suffix = 1; $suffix <= 99; $suffix++) {
            $candidates[] = $base.$suffix;
        }

        foreach (array_unique($candidates) as $candidate) {
            if (strlen($candidate) >= 2 && strlen($candidate) <= 6 && ! $this->isUsed($candidate, $exceptChurchId)) {
                return $candidate;
            }
        }

        throw new \RuntimeException('Could not generate a unique member ID prefix for "'.$churchName.'".');
    }

    public function isUsed(string $prefix, ?int $exceptChurchId = null): bool
    {
        $prefix = strtoupper(trim($prefix));

        return Church::query()
            ->when($exceptChurchId, fn ($query) => $query->where('id', '!=', $exceptChurchId))
            ->get(['id', 'settings'])
            ->contains(function (Church $church) use ($prefix): bool {
                $stored = strtoupper((string) data_get($church->settings, 'member_id_prefix', ''));

                return $stored !== '' && $stored === $prefix;
            });
    }

    public function assertAvailable(string $prefix, ?int $exceptChurchId = null): string
    {
        $prefix = strtoupper(trim($prefix));

        if ($prefix === '' || ! preg_match('/^[A-Z0-9]{2,6}$/', $prefix)) {
            throw ValidationException::withMessages([
                'member_id_prefix' => 'Member ID prefix must be 2-6 letters or numbers.',
            ]);
        }

        if ($this->isUsed($prefix, $exceptChurchId)) {
            throw ValidationException::withMessages([
                'member_id_prefix' => 'This member ID prefix is already used by another church.',
            ]);
        }

        return $prefix;
    }

    public function exampleMemberId(string $prefix): string
    {
        return strtoupper($prefix).'-'.now()->format('Y').'-0001';
    }

    private function primaryWord(string $churchName): string
    {
        $words = preg_split('/\s+/', trim($churchName)) ?: [];
        $significant = [];

        foreach ($words as $word) {
            $clean = preg_replace('/[^A-Za-z]/', '', $word) ?? '';

            if ($clean === '' || in_array(strtolower($clean), self::STOP_WORDS, true)) {
                continue;
            }

            $significant[] = $clean;
        }

        if ($significant === []) {
            return preg_replace('/[^A-Za-z]/', '', $churchName) ?: 'CHURCH';
        }

        return (string) end($significant);
    }
}
