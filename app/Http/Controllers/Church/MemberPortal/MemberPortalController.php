<?php

namespace App\Http\Controllers\Church\MemberPortal;

use App\Http\Controllers\Controller;
use App\Models\Member;

abstract class MemberPortalController extends Controller
{
    protected function member(): Member
    {
        return auth()->user()->member;
    }
}
