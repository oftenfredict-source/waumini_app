<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;

class MemberDetailResource extends MemberResource
{
    public function toArray(Request $request): array
    {
        return array_merge(parent::toArray($request), [
            'education_level' => $this->enumValue($this->education_level),
            'profession' => $this->profession,
            'is_baptized' => (bool) $this->is_baptized,
            'baptism_date' => $this->baptism_date?->toDateString(),
            'baptism_place' => $this->baptism_place,
            'baptized_by' => $this->baptized_by,
            'is_kipaimara' => (bool) $this->is_kipaimara,
            'kipaimara_date' => $this->kipaimara_date?->toDateString(),
            'kipaimara_place' => $this->kipaimara_place,
            'kipaimara_by' => $this->kipaimara_by,
            'nida_number' => $this->nida_number,
            'region' => $this->region,
            'district' => $this->district,
            'ward' => $this->ward,
            'street' => $this->street,
            'po_box' => $this->po_box,
            'tribe' => $this->tribe,
            'other_tribe' => $this->other_tribe,
            'residence_region' => $this->residence_region,
            'residence_district' => $this->residence_district,
            'residence_ward' => $this->residence_ward,
            'residence_street' => $this->residence_street,
            'residence_road' => $this->residence_road,
            'residence_house_number' => $this->residence_house_number,
            'address' => $this->address,
            'city' => $this->city,
            'marital_status' => $this->enumValue($this->marital_status),
            'wedding_type' => $this->enumValue($this->wedding_type),
            'wedding_date' => $this->wedding_date?->toDateString(),
            'spouse_full_name' => $this->spouse_full_name,
            'spouse_gender' => $this->spouse_gender,
            'spouse_date_of_birth' => $this->spouse_date_of_birth?->toDateString(),
            'spouse_education_level' => $this->enumValue($this->spouse_education_level),
            'spouse_profession' => $this->spouse_profession,
            'spouse_nida_number' => $this->spouse_nida_number,
            'spouse_email' => $this->spouse_email,
            'spouse_phone_number' => $this->spouse_phone_number,
            'spouse_tribe' => $this->spouse_tribe,
            'spouse_other_tribe' => $this->spouse_other_tribe,
            'spouse_church_member' => $this->spouse_church_member,
            'spouse_member_id' => $this->spouse_member_id,
            'spouse_envelope_number' => $this->spouse_envelope_number,
            'family_member_id' => $this->family_member_id,
            'guardian_full_name' => $this->guardian_full_name,
            'guardian_phone' => $this->guardian_phone,
            'guardian_relationship' => $this->guardian_relationship,
            'temporary_duration_value' => $this->temporary_duration_value,
            'temporary_duration_unit' => $this->enumValue($this->temporary_duration_unit),
            'departments' => $this->whenLoaded('departments', fn () => $this->departments->map(fn ($department) => [
                'id' => $department->id,
                'name' => $department->name,
            ])->values()->all()),
            'dependants' => $this->whenLoaded('dependants', fn () => MemberDependantResource::collection($this->dependants)->resolve()),
            'spouse_member' => $this->whenLoaded('spouseMember', fn () => $this->spouseMember ? [
                'id' => $this->spouseMember->id,
                'full_name' => $this->spouseMember->full_name,
                'member_number' => $this->spouseMember->member_number,
            ] : null),
        ]);
    }
}
