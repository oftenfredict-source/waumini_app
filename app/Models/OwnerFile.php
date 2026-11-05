<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class OwnerFile extends Model
{
    use HasUuid, SoftDeletes;

    public const CATEGORIES = [
        'document',
        'image',
        'archive',
        'other',
    ];

    protected $fillable = [
        'title',
        'description',
        'original_name',
        'path',
        'disk',
        'mime_type',
        'extension',
        'size_bytes',
        'category',
        'uploaded_by',
    ];

    protected function casts(): array
    {
        return [
            'size_bytes' => 'integer',
        ];
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function formattedSize(): string
    {
        $bytes = max(0, (int) $this->size_bytes);

        if ($bytes < 1024) {
            return $bytes.' B';
        }

        if ($bytes < 1048576) {
            return number_format($bytes / 1024, 1).' KB';
        }

        return number_format($bytes / 1048576, 2).' MB';
    }

    public function categoryLabel(): string
    {
        return match ($this->category) {
            'document' => __('owner.files.categories.document'),
            'image' => __('owner.files.categories.image'),
            'archive' => __('owner.files.categories.archive'),
            default => __('owner.files.categories.other'),
        };
    }

    public function existsOnDisk(): bool
    {
        return $this->path !== '' && Storage::disk($this->disk)->exists($this->path);
    }

    public static function detectCategory(?string $mime, ?string $extension): string
    {
        $mime = strtolower((string) $mime);
        $extension = strtolower((string) $extension);

        $documentExt = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'rtf', 'odt', 'ods', 'csv'];
        $imageExt = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp'];
        $archiveExt = ['zip', 'rar', '7z', 'tar', 'gz'];

        if (str_starts_with($mime, 'image/') || in_array($extension, $imageExt, true)) {
            return 'image';
        }

        if (
            str_contains($mime, 'pdf')
            || str_contains($mime, 'msword')
            || str_contains($mime, 'officedocument')
            || str_contains($mime, 'text/')
            || str_contains($mime, 'spreadsheet')
            || str_contains($mime, 'presentation')
            || in_array($extension, $documentExt, true)
        ) {
            return 'document';
        }

        if (
            str_contains($mime, 'zip')
            || str_contains($mime, 'compressed')
            || str_contains($mime, 'tar')
            || in_array($extension, $archiveExt, true)
        ) {
            return 'archive';
        }

        return 'other';
    }
}
