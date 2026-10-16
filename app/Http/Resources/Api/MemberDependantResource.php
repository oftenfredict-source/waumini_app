<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\MemberDependant */
class MemberDependantResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $status = $this->isConverted()
            ? 'converted'
            : ($this->isEligibleForIndependence() ? 'eligible' : 'active');

        return [
            'id' => $this->id,
            'full_name' => $this->full_name,
            'gender' => $this->gender,
            'date_of_birth' => $this->date_of_birth?->toDateString(),
            'age' => $this->age(),
            'relationship' => $this->relationship?->value,
            'relationship_label' => $this->relationship?->label(),
            'relationship_note' => $this->relationship_note,
            'is_baptized' => (bool) $this->is_baptized,
            'baptism_date' => $this->baptism_date?->toDateString(),
            'baptism_place' => $this->baptism_place,
            'baptized_by' => $this->baptized_by,
            'is_kipaimara' => (bool) $this->is_kipaimara,
            'kipaimara_date' => $this->kipaimara_date?->toDateString(),
            'kipaimara_place' => $this->kipaimara_place,
            'kipaimara_by' => $this->kipaimara_by,
            'is_student' => (bool) $this->is_student,
            'education_level' => $this->education_level?->value,
            'education_level_label' => $this->education_level?->label(),
            'school_name' => $this->school_name,
            'school_region' => $this->school_region,
            'school_district' => $this->school_district,
            'school_ward' => $this->school_ward,
            'school_street' => $this->school_street,
            'guardian_name' => $this->when(
                $this->relationLoaded('member') || $this->guardian_full_name,
                fn () => $this->guardianDisplayName(),
            ),
            'guardian_phone' => $this->member?->phone_number ?? $this->guardian_phone,
            'guardian_relationship' => $this->guardian_relationship,
            'guardian_is_member' => $this->hasMemberParent(),
            'status' => $status,
            'sunday_school' => $this->shouldAttendSundaySchool(),
            'main_service' => $this->shouldAttendMainService(),
            'departments' => $this->whenLoaded(
                'departments',
                fn () => $this->departments->pluck('name')->filter()->values()->all(),
            ),
            'member' => $this->whenLoaded('member', fn () => $this->member ? [
                'id' => $this->member->id,
                'full_name' => $this->member->full_name,
                'member_number' => $this->member->member_number,
                'phone_number' => $this->member->phone_number,
            ] : null),
            'linked_member' => $this->whenLoaded('linkedMember', fn () => $this->linkedMember ? [
                'id' => $this->linkedMember->id,
                'full_name' => $this->linkedMember->full_name,
                'member_number' => $this->linkedMember->member_number,
            ] : null),
        ];
    }
}
