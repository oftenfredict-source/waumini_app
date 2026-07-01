@php
    $swalMessages = [];

    $flashTitleKeys = [
        'success' => 'flash.success_title',
        'error' => 'flash.error_title',
        'info' => 'flash.info_title',
        'warning' => 'flash.warning_title',
    ];

    foreach ($flashTitleKeys as $sessionKey => $titleKey) {
        if (session($sessionKey)) {
            $swalMessages[] = [
                'type' => $sessionKey,
                'title' => __($titleKey),
                'message' => session($sessionKey),
            ];
        }
    }

    $credentialsHtml = function (string $email, string $password): string {
        return '<div class="text-left">'
            .'<p class="mb-2">'.__('flash.save_credentials_securely').'</p>'
            .'<p class="mb-1"><strong>'.__('flash.login_label').':</strong> <code>'.e($email).'</code></p>'
            .'<p class="mb-0"><strong>'.__('flash.password_label').':</strong> <code>'.e($password).'</code></p>'
            .'</div>';
    };

    if (session('staff_credentials')) {
        $swalMessages[] = [
            'type' => 'info',
            'title' => __('flash.staff_credentials_title'),
            'html' => $credentialsHtml(
                session('staff_credentials.email'),
                session('staff_credentials.password'),
            ),
            'confirmText' => __('flash.ok'),
        ];
    }

    if (session('admin_credentials')) {
        $swalMessages[] = [
            'type' => 'info',
            'title' => __('flash.admin_credentials_title'),
            'html' => $credentialsHtml(
                session('admin_credentials.email'),
                session('admin_credentials.password'),
            ),
            'confirmText' => __('flash.ok'),
        ];
    }

    if (session('registered_accounts') && auth()->check() && auth()->user()->canManageMemberPasswords()) {
        $accountsHtml = '<p class="text-left mb-3">'.__('flash.registered_accounts_intro').'</p><ul class="text-left mb-0 pl-3">';
        foreach (session('registered_accounts') as $account) {
            $accountsHtml .= '<li><strong>'.e($account['name'] ?? '').'</strong> — '.__('flash.member_id_label').': <code>'.e($account['member_id'] ?? '').'</code>, '.__('flash.password_label').': <code>'.e($account['password'] ?? '').'</code></li>';
        }
        $accountsHtml .= '</ul>';

        $swalMessages[] = [
            'type' => 'success',
            'title' => __('flash.login_accounts_created'),
            'html' => $accountsHtml,
        ];
    }

    if (isset($errors) && $errors->any() && ! request()->routeIs('church.login', 'owner.login')) {
        $swalMessages[] = [
            'type' => 'error',
            'title' => __('flash.fix_errors_title'),
            'html' => '<ul class="text-left mb-0 pl-3">' . collect($errors->all())->map(fn ($error) => '<li>' . e($error) . '</li>')->implode('') . '</ul>',
        ];
    }
@endphp

@if(! empty($swalMessages))
    <script id="swal-flash-data" type="application/json">@json($swalMessages)</script>
@endif
