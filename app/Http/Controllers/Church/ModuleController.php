<?php

namespace App\Http\Controllers\Church;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class ModuleController extends Controller
{
    public function placeholder(string $module, ?string $section = null): View
    {
        $titles = [
            'leadership' => 'Leadership',
            'departments' => 'Departments',
            'announcements' => 'Announcements',
            'services' => 'Services',
            'special-events' => 'Special Events',
            'attendance' => 'Attendance',
            'bereavements' => 'Bereavements',
            'finance-dashboard' => 'Finance Dashboard',
            'finance-approvals' => 'Approval Dashboard',
            'tithes' => 'Tithes',
            'offerings' => 'Offerings',
            'pledges' => 'Pledges',
            'budget-expenses' => 'Budget & Expenses',
            'reports' => 'Reports',
            'analytics' => 'Analytics',
        ];

        $key = $section ? "{$module}-{$section}" : $module;
        $title = $titles[$key] ?? $titles[$module] ?? ucwords(str_replace('-', ' ', $key));

        return view('church.modules.placeholder', [
            'title' => $title,
            'module' => $module,
        ]);
    }
}
