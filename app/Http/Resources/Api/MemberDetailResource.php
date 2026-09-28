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
            'is_kipaimara' => (bool) $this->is_kipaimara,
            'kipaimara_date' => $this->kipaimara_date?->toDateString(),
            'region' => $this->region,
            'district' => $this->district,
            'ward' => $this->ward,
            'street' => $this->street,
            'residence_region' => $this->residence_region,
            'residence_district' => $this->residence_district,
            'residence_ward' => $this->residence_ward,
            'residence_street' => $this->residence_street,
            'address' => $this->address,
            'city' => $this->city,
            'marital_status' => $this->enumValue($this->marital_status),
            'wedding_type' => $this->enumValue($this->wedding_type),
            'wedding_date' => $this->wedding_date?->toDateString(),
            'spouse_full_name' => $this->spouse_full_name,
            'spouse_phone_number' => $this->spouse_phone_number,
            'spouse_member_id' => $this->spouse_member_id,
            'family_member_id' => $this->family_member_id,
        ]);
    }
}
