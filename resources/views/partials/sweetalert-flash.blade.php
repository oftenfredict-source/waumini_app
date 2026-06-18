@php
    $swalMessages = [];

    if (session('success')) {
        $swalMessages[] = ['type' => 'success', 'message' => session('success')];
    }

    if (session('error')) {
        $swalMessages[] = ['type' => 'error', 'message' => session('error')];
    }

    if (session('info')) {
        $swalMessages[] = ['type' => 'info', 'message' => session('info')];
    }

    if (session('warning')) {
        $swalMessages[] = ['type' => 'warning', 'message' => session('warning')];
    }

    if (session('staff_credentials')) {
        $swalMessages[] = [
            'type' => 'credentials',
            'title' => 'Staff user credentials',
            'email' => session('staff_credentials.email'),
            'password' => session('staff_credentials.password'),
        ];
    }

    if (session('admin_credentials')) {
        $swalMessages[] = [
            'type' => 'credentials',
            'title' => 'Church admin credentials',
            'email' => session('admin_credentials.email'),
            'password' => session('admin_credentials.password'),
        ];
    }

    if (session('registered_accounts') && auth()->check() && auth()->user()->canManageMemberPasswords()) {
        $accountsHtml = '<p class="text-left mb-3">Share these credentials with the member(s). Password is the <strong>last name in CAPITAL letters</strong>.</p><ul class="text-left mb-0 pl-3">';
        foreach (session('registered_accounts') as $account) {
            $accountsHtml .= '<li><strong>'.e($account['name'] ?? '').'</strong> — Member ID: <code>'.e($account['member_id'] ?? '').'</code>, Password: <code>'.e($account['password'] ?? '').'</code></li>';
        }
        $accountsHtml .= '</ul>';

        $swalMessages[] = [
            'type' => 'success',
            'title' => 'Login accounts created',
            'html' => $accountsHtml,
        ];
    }

    if (isset($errors) && $errors->any() && ! request()->routeIs('church.login', 'owner.login')) {
        $swalMessages[] = [
            'type' => 'error',
            'title' => 'Please fix the following',
            'html' => '<ul class="text-left mb-0 pl-3">' . collect($errors->all())->map(fn ($error) => '<li>' . e($error) . '</li>')->implode('') . '</ul>',
        ];
    }
@endphp

@if(! empty($swalMessages))
    <script id="swal-flash-data" type="application/json">@json($swalMessages)</script>
@endif
