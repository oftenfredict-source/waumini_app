<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', User::class);

        return view('owner.users.index', [
            'users' => User::with('roles')->latest()->paginate(15),
            'roles' => Role::withCount('users')->get(),
        ]);
    }
}
