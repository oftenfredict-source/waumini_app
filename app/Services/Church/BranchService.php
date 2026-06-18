<?php

namespace App\Services\Church;

use App\Models\Church;
use App\Models\ChurchBranch;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class BranchService
{
    public function ensureHeadquartersBranch(Church $church): ChurchBranch
    {
        $existing = ChurchBranch::forChurch($church->id)
            ->where('is_headquarters', true)
            ->first();

        if ($existing) {
            return $existing;
        }

        return ChurchBranch::create([
            'church_id' => $church->id,
            'name' => $church->name.' - Headquarters',
            'code' => 'HQ',
            'is_headquarters' => true,
            'address' => $church->address,
            'city' => $church->city,
            'phone' => $church->phone,
            'email' => $church->email,
            'pastor_name' => $church->pastor_name,
            'logo_path' => $church->logo_path,
            'is_active' => true,
        ]);
    }

    public function create(Church $church, array $data, ?UploadedFile $logo = null): ChurchBranch
    {
        if (! empty($data['is_headquarters'])) {
            $this->clearHeadquartersFlag($church);
        }

        $branch = ChurchBranch::create([
            'church_id' => $church->id,
            'name' => $data['name'],
            'code' => strtoupper($data['code']),
            'is_headquarters' => (bool) ($data['is_headquarters'] ?? false),
            'address' => $data['address'] ?? null,
            'city' => $data['city'] ?? null,
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'] ?? null,
            'pastor_name' => $data['pastor_name'] ?? null,
            'is_active' => (bool) ($data['is_active'] ?? true),
        ]);

        if ($logo) {
            $this->uploadLogo($branch, $logo);
        }

        return $branch->fresh();
    }

    public function update(ChurchBranch $branch, array $data, ?UploadedFile $logo = null): ChurchBranch
    {
        if (! empty($data['is_headquarters']) && ! $branch->is_headquarters) {
            $this->clearHeadquartersFlag($branch->church);
        }

        if (! empty($data['remove_logo'])) {
            $this->deleteLogo($branch);
        }

        if ($logo) {
            $this->uploadLogo($branch, $logo);
        }

        if ($branch->is_headquarters && isset($data['is_active']) && ! $data['is_active']) {
            throw ValidationException::withMessages([
                'is_active' => 'Headquarters cannot be deactivated.',
            ]);
        }

        $branch->update([
            'name' => $data['name'],
            'code' => strtoupper($data['code']),
            'is_headquarters' => (bool) ($data['is_headquarters'] ?? $branch->is_headquarters),
            'address' => $data['address'] ?? null,
            'city' => $data['city'] ?? null,
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'] ?? null,
            'pastor_name' => $data['pastor_name'] ?? null,
            'is_active' => (bool) ($data['is_active'] ?? $branch->is_active),
        ]);

        return $branch->fresh();
    }

    public function deactivate(ChurchBranch $branch): void
    {
        if ($branch->is_headquarters) {
            throw ValidationException::withMessages([
                'branch' => 'Headquarters cannot be deactivated.',
            ]);
        }

        if ($branch->members()->exists()) {
            throw ValidationException::withMessages([
                'branch' => 'This branch has members. Reassign members first.',
            ]);
        }

        $branch->update(['is_active' => false]);
    }

    private function clearHeadquartersFlag(Church $church): void
    {
        ChurchBranch::forChurch($church->id)->update(['is_headquarters' => false]);
    }

    private function uploadLogo(ChurchBranch $branch, UploadedFile $logo): void
    {
        $this->deleteLogo($branch);

        $branch->update([
            'logo_path' => $logo->store("churches/{$branch->church_id}/branches", 'public'),
        ]);
    }

    private function deleteLogo(ChurchBranch $branch): void
    {
        if ($branch->logo_path) {
            Storage::disk('public')->delete($branch->logo_path);
            $branch->update(['logo_path' => null]);
        }
    }
}
