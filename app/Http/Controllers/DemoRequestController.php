<?php

namespace App\Http\Controllers;

use App\Services\Owner\DemoRequestService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DemoRequestController extends Controller
{
    public function __construct(
        private readonly DemoRequestService $demoRequestService,
    ) {}

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'church_name' => ['required', 'string', 'max:255'],
            'contact_name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'city' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'],
            'package_id' => ['nullable', 'integer', Rule::exists('subscription_packages', 'id')->where('is_active', true)],
            'message' => ['nullable', 'string', 'max:2000'],
        ]);

        $this->demoRequestService->submit($data);

        return redirect()
            ->route('landing')
            ->withFragment('demo')
            ->with('demo_success', __('landing.demo_success'));
    }
}
