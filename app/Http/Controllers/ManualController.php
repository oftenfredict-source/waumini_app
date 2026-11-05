<?php

namespace App\Http\Controllers;

use App\Support\PageManual;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ManualController extends Controller
{
    public function index(Request $request): View
    {
        $scope = $this->scope($request);
        $manual = PageManual::forKey(match ($scope) {
            'owner' => 'owner',
            'member' => 'member_portal',
            default => 'church_default',
        });

        return view('manual.index', [
            'scope' => $scope,
            'topics' => PageManual::catalog($scope),
            'manual' => $manual,
        ]);
    }

    public function show(Request $request, string $topic): View
    {
        $scope = $this->scope($request);
        $manual = PageManual::forKey($topic);

        return view('manual.show', [
            'scope' => $scope,
            'manual' => $manual,
            'topics' => PageManual::catalog($scope),
        ]);
    }

    private function scope(Request $request): string
    {
        if ($request->routeIs('owner.*')) {
            return 'owner';
        }

        if ($request->routeIs('church.member.*')) {
            return 'member';
        }

        return 'church';
    }
}
