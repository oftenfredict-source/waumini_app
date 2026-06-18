<?php

namespace App\Services\Church;

use App\Enums\AnnouncementTargetType;
use App\Models\Announcement;
use App\Models\Church;
use App\Models\Department;
use App\Services\Sms\ChurchSmsService;

class AnnouncementService
{
    public function __construct(
        private readonly ChurchSmsService $churchSmsService,
    ) {}

    public function create(Church $church, array $data, array $memberIds = []): Announcement
    {
        $data['church_id'] = $church->id;
        $data['created_by'] = auth()->id();

        if ($data['target_type'] === AnnouncementTargetType::Department->value) {
            $department = Department::forChurch($church->id)->findOrFail($data['department_id']);
            $memberIds = $department->members()->pluck('members.id')->all();
        } elseif ($data['target_type'] !== AnnouncementTargetType::Specific->value) {
            $data['department_id'] = null;
            $memberIds = [];
        } else {
            $data['department_id'] = null;
        }

        unset($data['member_ids']);

        $announcement = Announcement::create($data);

        if (in_array($announcement->target_type, [AnnouncementTargetType::Specific, AnnouncementTargetType::Department], true)) {
            $announcement->targetedMembers()->sync($memberIds);
        }

        $announcement = $announcement->load(['creator', 'targetedMembers', 'department']);

        $this->churchSmsService->sendAnnouncement($church, $announcement);

        return $announcement;
    }
}
