<?php

namespace App\Http\Controllers\Api\MemberPortal;

use App\Models\MemberDiaryEntry;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DiaryController extends MemberPortalController
{
    public function index(): JsonResponse
    {
        $member = $this->member();
        $entries = MemberDiaryEntry::forChurch($member->church_id)
            ->where('member_id', $member->id)
            ->latest()
            ->get()
            ->map(fn (MemberDiaryEntry $entry) => $this->payload($entry))
            ->values()
            ->all();

        return ApiResponse::success($entries);
    }

    public function store(Request $request): JsonResponse
    {
        $member = $this->member();
        $data = $this->validated($request);

        $entry = MemberDiaryEntry::query()->create([
            'church_id' => $member->church_id,
            'member_id' => $member->id,
            'title' => $data['title'] ?? null,
            'body' => $data['body'],
        ]);

        return ApiResponse::success($this->payload($entry), 'Saved', 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $entry = $this->ownedEntry($id);
        $entry->update($this->validated($request));

        return ApiResponse::success($this->payload($entry->fresh()));
    }

    public function destroy(int $id): JsonResponse
    {
        $this->ownedEntry($id)->delete();

        return ApiResponse::success(['deleted' => true]);
    }

    /**
     * @return array{title: string|null, body: string}
     */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'title' => ['nullable', 'string', 'max:120'],
            'body' => ['required', 'string', 'max:5000'],
        ]);

        $title = isset($data['title']) ? trim((string) $data['title']) : '';
        $data['title'] = $title === '' ? null : $title;
        $data['body'] = trim((string) $data['body']);

        return $data;
    }

    private function ownedEntry(int $id): MemberDiaryEntry
    {
        $member = $this->member();
        $entry = MemberDiaryEntry::forChurch($member->church_id)
            ->where('member_id', $member->id)
            ->find($id);

        abort_unless($entry, 404, 'Diary entry not found.');

        return $entry;
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(MemberDiaryEntry $entry): array
    {
        return [
            'id' => $entry->id,
            'title' => $entry->title,
            'body' => $entry->body,
            'created_at' => $entry->created_at?->toIso8601String(),
            'updated_at' => $entry->updated_at?->toIso8601String(),
        ];
    }
}
