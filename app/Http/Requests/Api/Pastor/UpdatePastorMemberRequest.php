<?php

namespace App\Http\Requests\Api\Pastor;

use App\Http\Requests\Church\UpdateMemberRequest;
use App\Models\Member;

class UpdatePastorMemberRequest extends UpdateMemberRequest
{
    public function authorize(): bool
    {
        $member = $this->memberRecord();

        if ($member) {
            $this->route()->setParameter('member', $member);
        }

        return $member instanceof Member && $this->user()->can('update', $member);
    }

    public function memberRecord(): ?Member
    {
        $existing = $this->route('member');
        if ($existing instanceof Member) {
            return $existing;
        }

        $id = (int) $this->route('id');
        if ($id < 1 || ! $this->user()?->church_id) {
            return null;
        }

        return Member::forChurch($this->user()->church_id)->find($id);
    }
}
