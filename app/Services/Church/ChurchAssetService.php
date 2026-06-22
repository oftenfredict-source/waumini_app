<?php

namespace App\Services\Church;

use App\Enums\AssetStatus;
use App\Models\Church;
use App\Models\ChurchAsset;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ChurchAssetService
{
    /**
     * @return array{asset: ChurchAsset, created_count: int, first_tag: string, last_tag: string|null}
     */
    public function register(Church $church, array $data, ?UploadedFile $photo = null, ?int $recordedBy = null): array
    {
        $quantity = max(1, (int) ($data['quantity'] ?? 1));
        $mode = $data['registration_mode'] ?? 'lot';
        unset($data['quantity'], $data['registration_mode'], $data['asset_tag']);

        if ($mode === 'individual' && $quantity > 1) {
            return $this->createIndividualItems($church, $data, $quantity, $photo, $recordedBy);
        }

        $data['quantity'] = $quantity;
        $asset = $this->create($church, $data, $photo, $recordedBy);

        return [
            'asset' => $asset,
            'created_count' => 1,
            'first_tag' => $asset->asset_tag,
            'last_tag' => null,
        ];
    }

    public function create(Church $church, array $data, ?UploadedFile $photo = null, ?int $recordedBy = null): ChurchAsset
    {
        $data['church_id'] = $church->id;
        $data['asset_tag'] = $this->generateAssetTag($church);
        $data['recorded_by'] = $recordedBy;
        $data['quantity'] = max(1, (int) ($data['quantity'] ?? 1));

        if ($photo) {
            $data['photo_path'] = $photo->store("churches/{$church->id}/assets", 'public');
        }

        if (($data['status'] ?? AssetStatus::Active->value) === AssetStatus::Disposed->value) {
            $data['disposed_at'] = $data['disposed_at'] ?? now()->toDateString();
        }

        return ChurchAsset::create($data);
    }

    /**
     * @return array{asset: ChurchAsset, created_count: int, first_tag: string, last_tag: string}
     */
    private function createIndividualItems(
        Church $church,
        array $data,
        int $quantity,
        ?UploadedFile $photo,
        ?int $recordedBy,
    ): array {
        return DB::transaction(function () use ($church, $data, $quantity, $photo, $recordedBy) {
            $batchId = (string) Str::uuid();
            $photoPath = $photo
                ? $photo->store("churches/{$church->id}/assets", 'public')
                : null;

            $data['church_id'] = $church->id;
            $data['recorded_by'] = $recordedBy;
            $data['batch_id'] = $batchId;
            $data['quantity'] = 1;
            $data['photo_path'] = $photoPath;

            if (($data['status'] ?? AssetStatus::Active->value) === AssetStatus::Disposed->value) {
                $data['disposed_at'] = $data['disposed_at'] ?? now()->toDateString();
            }

            $first = null;
            $last = null;

            for ($i = 0; $i < $quantity; $i++) {
                $itemData = $data;
                $itemData['asset_tag'] = $this->generateAssetTag($church);
                $asset = ChurchAsset::create($itemData);

                if ($i === 0) {
                    $first = $asset;
                }
                $last = $asset;
            }

            return [
                'asset' => $first,
                'created_count' => $quantity,
                'first_tag' => $first->asset_tag,
                'last_tag' => $last->asset_tag,
            ];
        });
    }

    public function update(ChurchAsset $asset, array $data, ?UploadedFile $photo = null): ChurchAsset
    {
        unset($data['church_id'], $data['asset_tag'], $data['recorded_by'], $data['batch_id'], $data['registration_mode']);

        if ($photo) {
            $data['photo_path'] = $photo->store("churches/{$asset->church_id}/assets", 'public');
        }

        if (array_key_exists('quantity', $data)) {
            if ($asset->batch_id) {
                unset($data['quantity']);
            } else {
                $data['quantity'] = max(1, (int) $data['quantity']);
            }
        }

        $newStatus = $data['status'] ?? $asset->status?->value;

        if ($newStatus === AssetStatus::Disposed->value) {
            $data['disposed_at'] = $data['disposed_at'] ?? $asset->disposed_at?->toDateString() ?? now()->toDateString();
        } elseif ($asset->status === AssetStatus::Disposed && $newStatus !== AssetStatus::Disposed->value) {
            $data['disposed_at'] = null;
        }

        $asset->update($data);

        return $asset->fresh(['custodian', 'branch', 'recorder']);
    }

    public function delete(ChurchAsset $asset): void
    {
        $asset->delete();
    }

    public function generateAssetTag(Church $church): string
    {
        $tags = ChurchAsset::forChurch($church->id)
            ->withTrashed()
            ->pluck('asset_tag');

        $max = 0;

        foreach ($tags as $tag) {
            if (preg_match('/(\d+)$/', $tag, $matches)) {
                $max = max($max, (int) $matches[1]);
            }
        }

        return 'AST-'.str_pad((string) ($max + 1), 4, '0', STR_PAD_LEFT);
    }

    public function peekNextAssetTag(Church $church): string
    {
        return $this->generateAssetTag($church);
    }
}
