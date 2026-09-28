<?php

namespace App\Http\Controllers\Api\MemberPortal;

use App\Http\Controllers\Controller;
use App\Models\Member;

abstract class MemberPortalController extends Controller
{
    protected function member(): Member
    {
        $member = request()->user()?->member;

        abort_unless($member, 403, 'This area is for church members only.');

        return $member;
    }
}
