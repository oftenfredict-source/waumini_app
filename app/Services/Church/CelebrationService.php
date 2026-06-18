<?php

namespace App\Services\Church;

use App\Enums\CelebrationSource;
use App\Enums\CelebrationStatus;
use App\Enums\CelebrationType;
use App\Enums\MaritalStatus;
use App\Enums\WeddingType;
use App\Models\Celebration;
use App\Models\Church;
use App\Models\Member;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonInterface;

class CelebrationService
{
    public function syncForChurch(Church $church): void
    {
        Member::query()
            ->forChurch($church->id)
            ->activeMembers()
            ->each(fn (Member $member) => $this->syncMember($member));

        $this->rollPastManualCelebrations($church);
    }

    public function syncMember(Member $member): void
    {
        $member->loadMissing('church');

        if ($member->date_of_birth) {
            $this->syncBirthday($member);
        } else {
            $this->removeAutoCelebration($member, CelebrationType::Birthday);
        }

        if ($member->marital_status === MaritalStatus::Married && $member->wedding_date) {
            $this->syncWeddingAnniversary($member);
        } else {
            $this->removeAutoCelebration($member, CelebrationType::WeddingAnniversary);
        }
    }

    public function createManual(Church $church, array $data, ?User $creator = null): Celebration
    {
        $data['church_id'] = $church->id;
        $data['source'] = CelebrationSource::Manual;
        $data['status'] = $data['status'] ?? CelebrationStatus::Upcoming;
        $data['created_by'] = $creator?->id;

        if (empty($data['title']) && ! empty($data['member_id'])) {
            $member = Member::forChurch($church->id)->findOrFail($data['member_id']);
            $data['title'] = $this->defaultTitle($member, CelebrationType::from($data['celebration_type']));
        }

        return Celebration::create($data);
    }

    public function update(Celebration $celebration, array $data): Celebration
    {
        if ($celebration->source === CelebrationSource::Manual) {
            $celebration->update($data);

            return $celebration->fresh(['member', 'creator']);
        }

        $celebration->update([
            'status' => $data['status'] ?? $celebration->status,
            'notes' => $data['notes'] ?? $celebration->notes,
        ]);

        return $celebration->fresh(['member', 'creator']);
    }

    public function delete(Celebration $celebration): void
    {
        if ($celebration->source === CelebrationSource::Auto) {
            $celebration->update(['status' => CelebrationStatus::Cancelled]);

            return;
        }

        $celebration->delete();
    }

    private function syncBirthday(Member $member): void
    {
        $original = $member->date_of_birth;
        $occurrence = $this->resolveOccurrence($original);

        Celebration::updateOrCreate(
            [
                'church_id' => $member->church_id,
                'member_id' => $member->id,
                'celebration_type' => CelebrationType::Birthday,
                'source' => CelebrationSource::Auto,
            ],
            [
                'title' => $member->full_name."'s Birthday",
                'celebration_date' => $occurrence,
                'original_date' => $original,
                'status' => CelebrationStatus::Upcoming,
            ],
        );
    }

    private function syncWeddingAnniversary(Member $member): void
    {
        $original = $member->wedding_date;
        $occurrence = $this->resolveOccurrence($original);

        Celebration::updateOrCreate(
            [
                'church_id' => $member->church_id,
                'member_id' => $member->id,
                'celebration_type' => CelebrationType::WeddingAnniversary,
                'source' => CelebrationSource::Auto,
            ],
            [
                'title' => $member->full_name."'s Wedding Anniversary",
                'celebration_date' => $occurrence,
                'original_date' => $original,
                'wedding_type' => $member->wedding_type,
                'status' => CelebrationStatus::Upcoming,
            ],
        );
    }

    private function rollPastManualCelebrations(Church $church): void
    {
        Celebration::query()
            ->forChurch($church->id)
            ->where('source', CelebrationSource::Manual)
            ->where('status', CelebrationStatus::Upcoming)
            ->whereDate('celebration_date', '<', now()->toDateString())
            ->whereYear('celebration_date', now()->year)
            ->update(['status' => CelebrationStatus::Celebrated]);
    }

    private function removeAutoCelebration(Member $member, CelebrationType $type): void
    {
        Celebration::query()
            ->forChurch($member->church_id)
            ->where('member_id', $member->id)
            ->where('celebration_type', $type)
            ->where('source', CelebrationSource::Auto)
            ->delete();
    }

    /**
     * Next occurrence in the current year, or next year if this year's date has passed.
     */
    private function resolveOccurrence(CarbonInterface $original): Carbon
    {
        $today = now()->startOfDay();
        $year = (int) $today->year;
        $thisYear = $original->copy()->year($year)->startOfDay();

        if ($thisYear->gte($today)) {
            return $thisYear;
        }

        return $original->copy()->year($year + 1)->startOfDay();
    }

    private function defaultTitle(Member $member, CelebrationType $type): string
    {
        return match ($type) {
            CelebrationType::Birthday => $member->full_name."'s Birthday",
            CelebrationType::WeddingAnniversary => $member->full_name."'s Wedding Anniversary",
            CelebrationType::Other => $member->full_name.' Celebration',
        };
    }
}
