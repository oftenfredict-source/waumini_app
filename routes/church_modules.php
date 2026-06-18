<?php

use Illuminate\Support\Facades\Route;
use WauminiChurch\Http\Controllers\AuthController;
use WauminiChurch\Http\Controllers\SsoBridgeController;
use WauminiChurch\Http\Controllers\DashboardController;
use WauminiChurch\Http\Controllers\CelebrationController;
use WauminiChurch\Http\Controllers\NotificationController;
use WauminiChurch\Http\Middleware\PreventBackHistory;
use WauminiChurch\Http\Controllers\MemberController;
use WauminiChurch\Http\Controllers\MemberDashboardController;
use WauminiChurch\Http\Controllers\AnnouncementController;
use WauminiChurch\Http\Controllers\LeaderController;
use WauminiChurch\Http\Controllers\WeeklyAssignmentController;
use WauminiChurch\Http\Controllers\SundayServiceController;
use WauminiChurch\Http\Controllers\SpecialEventController;
use WauminiChurch\Http\Controllers\SettingsController;
use WauminiChurch\Http\Controllers\FinancialApprovalController;
use WauminiChurch\Http\Controllers\PastorDashboardController;
use WauminiChurch\Http\Controllers\AnalyticsController;
use WauminiChurch\Http\Controllers\AdminController;
use Illuminate\Http\Request;
use WauminiChurch\Http\Controllers\AttendanceController;
use WauminiChurch\Http\Controllers\ZKTecoController;





// Leader password change routes - accessible to all leaders (pastor, secretary, treasurer, admin)
Route::middleware(['auth:church_system', PreventBackHistory::class])->group(function () {
    Route::get('/leader/change-password', [DashboardController::class, 'showChangePassword'])->name('leader.change-password');
    Route::post('/leader/change-password', [DashboardController::class, 'updatePassword'])->name('leader.password.update');
});

// Auth routes with PreventBackHistory middleware
// Treasurer middleware is applied to restrict treasurer access to finance-only routes
Route::middleware(['auth:church_system', PreventBackHistory::class, 'treasurer'])->group(function () {
    Route::get('/secretary/dashboard', [DashboardController::class, 'index'])->name('dashboard.secretary');
    Route::get('/pastor/dashboard', [PastorDashboardController::class, 'index'])->name('dashboard.pastor');
    Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics.index');
    Route::get('/members/view', [MemberController::class, 'view'])
        ->middleware('permission:members.view')
        ->name('members.view');
    // Leaders management routes
    Route::resource('leaders', LeaderController::class);
    Route::post('/leaders/{leader}/deactivate', [LeaderController::class, 'deactivate'])->name('leaders.deactivate');
    Route::post('/leaders/{leader}/reactivate', [LeaderController::class, 'reactivate'])->name('leaders.reactivate');
    // Leadership reports routes
    Route::get('/leaders-reports', [LeaderController::class, 'reports'])->name('leaders.reports');
    Route::get('/leaders-export/csv', [LeaderController::class, 'exportCsv'])->name('leaders.export.csv');
    Route::get('/leaders-export/pdf', [LeaderController::class, 'exportPdf'])->name('leaders.export.pdf');
    // Weekly Assignments routes
    Route::resource('weekly-assignments', WeeklyAssignmentController::class);
    // Member identity cards routes
    Route::get('/members/{member}/identity-card', [MemberController::class, 'identityCard'])->name('members.identity-card');

    // Leadership identity cards routes
    Route::get('/leaders/{leader}/identity-card', [LeaderController::class, 'identityCard'])->name('leaders.identity-card');
    Route::get('/leaders-identity-cards/bulk', [LeaderController::class, 'bulkIdentityCards'])->name('leaders.identity-cards.bulk');
    Route::get('/leaders-identity-cards/position/{position}', [LeaderController::class, 'positionIdentityCards'])->name('leaders.identity-cards.position');
    // Test leader appointment SMS
    Route::get('/test-leader-sms', function (Request $request) {
        $to = $request->query('to');
        $name = $request->query('name', 'John Doe');
        $position = $request->query('position', 'Pastor');
        $church = $request->query('church', 'Waumini Church');

        if (empty($to)) {
            return response()->json([
                'success' => false,
                'message' => 'Provide query params: to=+255XXXXXXXXX and optional name, position, church'
            ], 422);
        }

        try {
            $smsService = app(\WauminiChurch\Services\SmsService::class);
            $result = $smsService->sendLeaderAppointmentNotificationDebug($to, $name, $position, $church);

            return response()->json([
                'success' => $result['ok'] ?? false,
                'to' => $to,
                'name' => $name,
                'position' => $position,
                'church' => $church,
                'provider_status' => $result['status'] ?? null,
                'provider_body' => $result['body'] ?? null,
                'reason' => $result['reason'] ?? null,
                'error' => $result['error'] ?? null,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    })->name('test.leader.sms');
    // Sunday services UI route
    Route::get('/services/sunday', [SundayServiceController::class, 'index'])->name('services.sunday.index');
    // Special events UI route
    Route::get('/special-events', [SpecialEventController::class, 'index'])->name('special.events.index');
    // Celebrations UI route
    Route::get('/celebrations', [CelebrationController::class, 'index'])->name('celebrations.index');

    // Financial Management Routes
    Route::prefix('finance')->name('finance.')->group(function () {
        // Test route
        Route::get('/test', function () {
            return 'Finance test route works!';
        });

        // Dashboard
        Route::get('/dashboard', [WauminiChurch\Http\Controllers\FinanceController::class, 'dashboard'])->name('dashboard');

        // Tithes
        Route::get('/tithes', [WauminiChurch\Http\Controllers\FinanceController::class, 'tithes'])->name('tithes');
        Route::post('/tithes', [WauminiChurch\Http\Controllers\FinanceController::class, 'storeTithe'])->name('tithes.store');
        Route::post('/tithes/{tithe}/paid', [WauminiChurch\Http\Controllers\FinanceController::class, 'markTithePaid'])->name('tithes.paid');

        // Offerings
        Route::get('/offerings', [WauminiChurch\Http\Controllers\FinanceController::class, 'offerings'])->name('offerings');
        Route::post('/offerings', [WauminiChurch\Http\Controllers\FinanceController::class, 'storeOffering'])->name('offerings.store');

        // Donations
        Route::get('/donations', [WauminiChurch\Http\Controllers\FinanceController::class, 'donations'])->name('donations');
        Route::post('/donations', [WauminiChurch\Http\Controllers\FinanceController::class, 'storeDonation'])->name('donations.store');

        // Pledges
        Route::get('/pledges', [WauminiChurch\Http\Controllers\FinanceController::class, 'pledges'])->name('pledges');
        Route::post('/pledges', [WauminiChurch\Http\Controllers\FinanceController::class, 'storePledge'])->name('pledges.store');
        Route::post('/pledges/{pledge}/payment', [WauminiChurch\Http\Controllers\FinanceController::class, 'updatePledgePayment'])->name('pledges.payment');

        // Annual Fees
        Route::get('/annual-fees', [WauminiChurch\Http\Controllers\FinanceController::class, 'annualFees'])->name('annual_fees');
        Route::post('/annual-fees', [WauminiChurch\Http\Controllers\FinanceController::class, 'storeAnnualFee'])->name('annual_fees.store');

        // Budgets
        Route::get('/budgets', [WauminiChurch\Http\Controllers\FinanceController::class, 'budgets'])->name('budgets');
        Route::post('/budgets', [WauminiChurch\Http\Controllers\FinanceController::class, 'storeBudget'])->name('budgets.store');
        Route::post('/budgets/resync', [WauminiChurch\Http\Controllers\FinanceController::class, 'reSyncBudgets'])->name('budgets.resync');
        Route::post('/budgets/{budget}', [WauminiChurch\Http\Controllers\FinanceController::class, 'updateBudget'])->name('budgets.update');
        Route::delete('/budgets/{budget}', [WauminiChurch\Http\Controllers\FinanceController::class, 'destroyBudget'])->name('budgets.destroy');

        // Budget Funding
        Route::post('/budgets/{budget}/allocate-funds', [WauminiChurch\Http\Controllers\FinanceController::class, 'allocateBudgetFunds'])->name('budgets.allocate-funds');
        Route::get('/budgets/{budget}/funding-suggestions', [WauminiChurch\Http\Controllers\FinanceController::class, 'getFundingSuggestions'])->name('budgets.funding-suggestions');
        Route::post('/budgets/new/funding-suggestions', [WauminiChurch\Http\Controllers\FinanceController::class, 'getNewBudgetFundingSuggestions'])->name('budgets.new.funding-suggestions');
        Route::get('/budgets/available-offerings', [WauminiChurch\Http\Controllers\FinanceController::class, 'getAvailableOfferings'])->name('budgets.available-offerings');
        Route::get('/budgets/{budgetId}/info', [WauminiChurch\Http\Controllers\FinanceController::class, 'getBudgetInfo'])->name('budgets.info');
        Route::get('/budgets/{budgetId}/fund-breakdown', [WauminiChurch\Http\Controllers\FinanceController::class, 'getFundBreakdown'])->name('budgets.fund-breakdown');
        Route::get('/budgets/{budgetId}/fund-summary', [WauminiChurch\Http\Controllers\FinanceController::class, 'getFundSummary'])->name('budgets.fund-summary');
        Route::get('/budgets/{budgetId}/line-items', [WauminiChurch\Http\Controllers\FinanceController::class, 'getBudgetLineItems'])->name('budgets.line-items');
        Route::get('/budgets/offering-type-fund-summary', [WauminiChurch\Http\Controllers\FinanceController::class, 'getOfferingTypeFundSummary'])->name('budgets.offering-type-fund-summary');

        // Expenses
        Route::get('/expenses', [WauminiChurch\Http\Controllers\FinanceController::class, 'expenses'])->name('expenses');
        Route::post('/expenses', [WauminiChurch\Http\Controllers\FinanceController::class, 'storeExpense'])->name('expenses.store');
        Route::post('/expenses/{expense}', [WauminiChurch\Http\Controllers\FinanceController::class, 'updateExpense'])->name('expenses.update');
        Route::post('/expenses/{expense}/paid', [WauminiChurch\Http\Controllers\FinanceController::class, 'markExpensePaid'])->name('expenses.paid');
        Route::delete('/expenses/{expense}', [WauminiChurch\Http\Controllers\FinanceController::class, 'destroyExpense'])->name('expenses.destroy');
    });

    // Financial Approval Routes (Pastor only)
    Route::prefix('finance/approval')->name('finance.approval.')->group(function () {
        Route::get('/dashboard', [FinancialApprovalController::class, 'dashboard'])->name('dashboard');
        Route::post('/approve', [FinancialApprovalController::class, 'approve'])->name('approve');
        Route::post('/reject', [FinancialApprovalController::class, 'reject'])->name('reject');

        // Funding Requests
        Route::get('/funding-requests', [FinancialApprovalController::class, 'fundingRequests'])->name('funding-requests');
        Route::post('/funding-requests/{fundingRequest}/approve', [FinancialApprovalController::class, 'approveFundingRequest'])->name('funding-requests.approve');
        Route::post('/funding-requests/{fundingRequest}/reject', [FinancialApprovalController::class, 'rejectFundingRequest'])->name('funding-requests.reject');
        Route::get('/funding-requests/{fundingRequest}/details', [FinancialApprovalController::class, 'getFundingRequestDetails'])->name('funding-requests.details');
        Route::post('/bulk-approve', [FinancialApprovalController::class, 'bulkApprove'])->name('bulk-approve');
        Route::get('/pending/{type}', [FinancialApprovalController::class, 'pendingByType'])->name('pending');
        Route::get('/approved/{type}', [FinancialApprovalController::class, 'approvedByType'])->name('approved');
        Route::get('/rejected/{type}', [FinancialApprovalController::class, 'rejectedByType'])->name('rejected');
        Route::get('/daily-summary', [FinancialApprovalController::class, 'dailySummary'])->name('daily-summary');
        Route::get('/export-pending', [FinancialApprovalController::class, 'exportPending'])->name('export-pending');
        Route::get('/view-details/{type}/{id}', [FinancialApprovalController::class, 'viewDetails'])->name('view-details');
    });

    // Financial Reports Routes
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [WauminiChurch\Http\Controllers\ReportController::class, 'index'])->name('index');
        Route::get('/overview', [WauminiChurch\Http\Controllers\ReportController::class, 'overview'])->name('overview');
        Route::get('/member-giving', [WauminiChurch\Http\Controllers\ReportController::class, 'memberGiving'])->name('member-giving');
        Route::get('/department-giving', [WauminiChurch\Http\Controllers\ReportController::class, 'departmentGiving'])->name('department-giving');
        Route::get('/income-vs-expenditure', [WauminiChurch\Http\Controllers\ReportController::class, 'incomeVsExpenditure'])->name('income-vs-expenditure');
        Route::get('/budget-performance', [WauminiChurch\Http\Controllers\ReportController::class, 'budgetPerformance'])->name('budget-performance');
        Route::get('/offering-fund-breakdown', [WauminiChurch\Http\Controllers\ReportController::class, 'offeringFundBreakdown'])->name('offering-fund-breakdown');
        Route::get('/monthly-financial', [WauminiChurch\Http\Controllers\ReportController::class, 'monthlyFinancialReport'])->name('monthly-financial');
        Route::get('/weekly-financial', [WauminiChurch\Http\Controllers\ReportController::class, 'weeklyFinancialReport'])->name('weekly-financial');
        Route::get('/general', [WauminiChurch\Http\Controllers\ReportController::class, 'generalReport'])->name('general');
        // Specific routes first (for backward compatibility)
        Route::get('/export/pdf', [WauminiChurch\Http\Controllers\ReportController::class, 'exportPdf'])->name('export.pdf');
        Route::get('/export/excel', [WauminiChurch\Http\Controllers\ReportController::class, 'exportExcel'])->name('export.excel');
        // Dynamic route (handles /reports/export/pdf and /reports/export/excel)
        Route::get('/export/{format}', [WauminiChurch\Http\Controllers\ReportController::class, 'exportReport'])->name('export')->where('format', 'pdf|excel');
        Route::get('/member-receipt/{memberId}', [WauminiChurch\Http\Controllers\ReportController::class, 'generateMemberReceipt'])->name('member-receipt');
    });
});

// Member routes - Permission-based access control
Route::middleware(['auth:church_system', 'treasurer'])->group(function () {
    // Member creation - requires members.create permission
    Route::get('/members/add', function () {
        if (!auth()->user()->hasPermission('members.create') && !auth()->user()->isAdmin()) {
            abort(403, 'You do not have permission to add members.');
        }
        return view('members.add-members');
    })->name('members.add');

    Route::post('/members', [MemberController::class, 'store'])
        ->middleware('permission:members.create')
        ->name('members.store');

    // Test route for debugging
    Route::get('/test-member', function () {
        try {
            $member = new WauminiChurch\Models\Member();
            return response()->json(['status' => 'success', 'message' => 'Member model works', 'fillable' => $member->getFillable()]);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    });

    // Member viewing - requires members.view permission
    Route::get('/members', [MemberController::class, 'index'])
        ->middleware('permission:members.view')
        ->name('members.index');
    // Redirect /members/leaders to /member/leaders (correct route)
    Route::get('/members/leaders', function () {
        return redirect()->route('member.leaders');
    })->name('members.leaders.redirect');
    Route::get('/members/next-id', [MemberController::class, 'nextId'])->name('members.next_id');
    Route::get('/members/check-envelope', [MemberController::class, 'checkEnvelope'])->name('members.check-envelope');
    Route::get('/members/check-phone', [MemberController::class, 'checkPhone'])->name('members.check-phone');
    Route::get('/members/export/csv', [MemberController::class, 'exportCsv'])->name('members.export.csv');

    Route::get('/members/partial-registration/import', [WauminiChurch\Http\Controllers\PartialRegistrationController::class, 'showImportForm'])
        ->middleware('permission:members.create')
        ->name('members.partial-registration.import.form');
    Route::post('/members/partial-registration/import', [WauminiChurch\Http\Controllers\PartialRegistrationController::class, 'import'])
        ->middleware('permission:members.create')
        ->name('members.partial-registration.import');
    Route::post('/members/partial-registration/send-sms', [WauminiChurch\Http\Controllers\PartialRegistrationController::class, 'sendBulkSms'])
        ->middleware('permission:members.create')
        ->name('members.partial-registration.send-sms');
    Route::get('/members/partial-registration/template', [WauminiChurch\Http\Controllers\PartialRegistrationController::class, 'downloadTemplate'])
        ->middleware('permission:members.create')
        ->name('members.partial-registration.template');

    // PUT and DELETE routes must come before GET routes with parameters
    Route::get('/members/{member}/complete-registration', [MemberController::class, 'showCompleteRegistration'])
        ->middleware('permission:members.edit')
        ->where('member', '[0-9]+')
        ->name('members.complete_registration');
    Route::put('/members/{member}', [MemberController::class, 'update'])
        ->middleware('permission:members.edit')
        ->where('member', '[0-9]+')
        ->name('members.update');
    Route::delete('/members/{member}', [MemberController::class, 'destroy'])
        ->middleware('permission:members.delete')
        ->where('member', '[0-9]+')
        ->name('members.destroy');
    Route::delete('/members/archived/{memberId}', [MemberController::class, 'destroyArchived'])
        ->middleware('permission:members.delete')
        ->name('members.destroy.archived');
    Route::delete('/members/{member}/archive', [MemberController::class, 'archive'])
        ->middleware('permission:members.delete')
        ->where('member', '[0-9]+')
        ->name('members.archive');
    Route::post('/members/archived/{memberId}/restore', [MemberController::class, 'restore'])
        ->middleware('permission:members.edit')
        ->name('members.restore');

    // Test route to check if member exists - MUST be before /members/{id} to avoid conflicts
    Route::get('/test-member/{id}', function ($id) {
        try {
            // Validate ID is numeric
            if (!is_numeric($id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid member ID'
                ], 400);
            }

            $member = \WauminiChurch\Models\Member::find($id);
            if ($member) {
                return response()->json([
                    'success' => true,
                    'member' => [
                        'id' => $member->id,
                        'member_id' => $member->member_id,
                        'full_name' => $member->full_name
                    ]
                ]);
            }
            return response()->json([
                'success' => false,
                'message' => 'Member not found'
            ], 404);
        } catch (\Exception $e) {
            \Log::error('Error in test-member route: ' . $e->getMessage(), [
                'id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error verifying member: ' . $e->getMessage()
            ], 500);
        }
    })->where('id', '[0-9]+');

    // Password reset - Admin only (must come before GET /members/{id} route)
    Route::post('/members/{id}/reset-password', [MemberController::class, 'resetPassword'])
        ->middleware('permission:members.edit')
        ->where('id', '[0-9]+')
        ->name('members.reset-password');

    // GET route with parameter should come last
    Route::get('/members/{id}', [MemberController::class, 'show'])->name('members.show')->where('id', '[0-9]+');

    // Children routes
    Route::post('/children', [MemberController::class, 'storeChild'])->name('children.store');
    Route::post('/children/{child}/promote', [MemberController::class, 'promoteToMember'])
        ->middleware('permission:members.create')
        ->name('children.promote');
    Route::delete('/children/{child}', [MemberController::class, 'destroyChild'])->name('children.destroy');

    // List all members for debugging
    Route::get('/list-members', function () {
        $members = \WauminiChurch\Models\Member::select('id', 'member_id', 'full_name')->get();
        $archivedMembers = \WauminiChurch\Models\DeletedMember::select('id', 'member_id', 'member_snapshot')->get();

        return response()->json([
            'success' => true,
            'active_members' => [
                'count' => $members->count(),
                'members' => $members
            ],
            'archived_members' => [
                'count' => $archivedMembers->count(),
                'members' => $archivedMembers->map(function ($archived) {
                    $snapshot = $archived->member_snapshot;
                    return [
                        'id' => $archived->id,
                        'member_id' => $archived->member_id,
                        'full_name' => $snapshot['full_name'] ?? 'Unknown',
                        'archived_at' => $archived->deleted_at_actual
                    ];
                })
            ]
        ]);
    });

    // SMS test route (authenticated)
    Route::get('/test-sms', function (Request $request) {
        $to = $request->query('to');
        $text = $request->query('text', 'Karibu Waumini Church!');
        if (empty($to)) {
            return response()->json([
                'success' => false,
                'message' => 'Provide query params: to=+255XXXXXXXXX and optional text=...'
            ], 422);
        }
        try {
            $resp = app(\WauminiChurch\Services\SmsService::class)->sendDebug($to, $text);
            $conf = [
                'enabled' => \WauminiChurch\Services\SettingsService::get('enable_sms_notifications', false),
                'has_api_url' => !empty(\WauminiChurch\Services\SettingsService::get('sms_api_url')),
                'has_username' => !empty(\WauminiChurch\Services\SettingsService::get('sms_username')),
                'has_password' => !empty(\WauminiChurch\Services\SettingsService::get('sms_password')),
                'has_sender_id' => !empty(\WauminiChurch\Services\SettingsService::get('sms_sender_id')),
                'has_api_key' => !empty(\WauminiChurch\Services\SettingsService::get('sms_api_key')),
            ];
            return response()->json([
                'success' => $resp['ok'] ?? false,
                'to' => $to,
                'text' => $text,
                'provider_status' => $resp['status'] ?? null,
                'provider_body' => $resp['body'] ?? null,
                'reason' => $resp['reason'] ?? null,
                'request' => $resp['request'] ?? null,
                'error' => $resp['error'] ?? null,
                'config' => $conf,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    })->name('test.sms');

    // One-time setup route to configure SMS settings (authenticated)
    Route::match(['get', 'post'], '/setup-sms', function (Request $request) {
        try {
            \WauminiChurch\Models\SystemSetting::setValue('enable_sms_notifications', '1', 'boolean');
            \WauminiChurch\Models\SystemSetting::setValue('sms_api_url', $request->input('sms_api_url', 'https://messaging-service.co.tz/link/sms/v1/text/single'), 'string');
            \WauminiChurch\Models\SystemSetting::setValue('sms_username', $request->input('sms_username', 'emcatechn'), 'string');
            \WauminiChurch\Models\SystemSetting::setValue('sms_password', $request->input('sms_password', 'Emca@#12'), 'string');
            \WauminiChurch\Models\SystemSetting::setValue('sms_sender_id', $request->input('sms_sender_id', 'WauminiLnk'), 'string');

            return response()->json([
                'success' => true,
                'message' => 'SMS settings saved',
                'applied' => [
                    'enable_sms_notifications' => true,
                    'sms_api_url' => $request->input('sms_api_url', 'https://messaging-service.co.tz/link/sms/v1/text/single'),
                    'sms_username' => $request->input('sms_username', 'emcatechn'),
                    'sms_password_set' => true,
                    'sms_sender_id' => $request->input('sms_sender_id', 'MkulimaLink'),
                ]
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    })->name('setup.sms');

    // Test payment approval SMS
    Route::get('/test-payment-sms', function (Request $request) {
        $to = $request->query('to');
        $memberName = $request->query('name', 'John Doe');
        $paymentType = $request->query('type', 'Tithe');
        $amount = $request->query('amount', 50000);
        $date = $request->query('date', date('Y-m-d'));

        if (empty($to)) {
            return response()->json([
                'success' => false,
                'message' => 'Provide query params: to=+255XXXXXXXXX and optional name, type, amount, date'
            ], 422);
        }

        try {
            $smsService = app(\WauminiChurch\Services\SmsService::class);
            $resp = $smsService->sendPaymentApprovalNotificationDebug($to, $memberName, $paymentType, $amount, $date);

            $conf = [
                'enabled' => \WauminiChurch\Services\SettingsService::get('enable_sms_notifications', false),
                'has_api_url' => !empty(\WauminiChurch\Services\SettingsService::get('sms_api_url')),
                'has_username' => !empty(\WauminiChurch\Services\SettingsService::get('sms_username')),
                'has_password' => !empty(\WauminiChurch\Services\SettingsService::get('sms_password')),
                'has_sender_id' => !empty(\WauminiChurch\Services\SettingsService::get('sms_sender_id')),
                'has_api_key' => !empty(\WauminiChurch\Services\SettingsService::get('sms_api_key')),
            ];

            return response()->json([
                'success' => $resp['ok'] ?? false,
                'to' => $to,
                'member_name' => $memberName,
                'payment_type' => $paymentType,
                'amount' => $amount,
                'date' => $date,
                'provider_status' => $resp['status'] ?? null,
                'provider_body' => $resp['body'] ?? null,
                'reason' => $resp['reason'] ?? null,
                'request' => $resp['request'] ?? null,
                'error' => $resp['error'] ?? null,
                'config' => $conf,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    })->name('test.payment.sms');


    // Sunday services routes
    Route::post('/services/sunday', [SundayServiceController::class, 'store'])->name('services.sunday.store');
    // Specific routes must come before parameterized routes
    Route::get('/services/sunday/coordinators', [SundayServiceController::class, 'getCoordinators'])->name('services.sunday.coordinators');
    Route::get('/services/sunday/church-elders', [SundayServiceController::class, 'getChurchElders'])->name('services.sunday.church.elders');
    Route::get('/services/sunday/preachers', [SundayServiceController::class, 'getPreachers'])->name('services.sunday.preachers');
    Route::get('/services/sunday/weekly-assignment', [SundayServiceController::class, 'getWeeklyAssignmentForDate'])->name('services.sunday.weekly.assignment');
    Route::get('/services/sunday-export/csv', [SundayServiceController::class, 'exportCsv'])->name('services.sunday.export.csv');
    // Parameterized routes come last
    Route::get('/services/sunday/{sundayService}', [SundayServiceController::class, 'show'])->name('services.sunday.show');
    Route::put('/services/sunday/{sundayService}', [SundayServiceController::class, 'update'])->name('services.sunday.update');
    Route::delete('/services/sunday/{sundayService}', [SundayServiceController::class, 'destroy'])->name('services.sunday.destroy');

    // Special events routes
    Route::post('/special-events', [SpecialEventController::class, 'store'])->name('special.events.store');
    Route::get('/special-events/{specialEvent}', [SpecialEventController::class, 'show'])->name('special.events.show');
    Route::put('/special-events/{specialEvent}', [SpecialEventController::class, 'update'])->name('special.events.update');
    Route::delete('/special-events/{specialEvent}', [SpecialEventController::class, 'destroy'])->name('special.events.destroy');
    Route::get('/special-events-members/notification', [SpecialEventController::class, 'getMembersForNotification'])->name('special.events.members.notification');

    // Department routes
    Route::resource('departments', \WauminiChurch\Http\Controllers\DepartmentController::class);
    Route::post('/departments/{department}/assign-members', [\WauminiChurch\Http\Controllers\DepartmentController::class, 'assignMembers'])->name('departments.assign-members');
    Route::delete('/departments/{department}/members/{member}', [\WauminiChurch\Http\Controllers\DepartmentController::class, 'removeMember'])->name('departments.remove-member');
});

// Attendance clear-all route - OUTSIDE treasurer group for admin/pastor/secretary access
// MUST be registered BEFORE the treasurer group's /attendance routes to avoid conflicts
Route::middleware(['auth:church_system', PreventBackHistory::class])->group(function () {
    Route::post('/attendance/clear-all', [AttendanceController::class, 'clearAll'])->name('attendance.clear-all');
});

// Treasurer middleware is applied to restrict treasurer access to finance-only routes
Route::middleware(['auth:church_system', PreventBackHistory::class, 'treasurer'])->group(function () {
    // Attendance routes
    // IMPORTANT: More specific routes must come BEFORE less specific routes
    // Biometric sync route - must be first to avoid conflicts
    Route::post('/attendance/biometric-sync', [AttendanceController::class, 'syncBiometricAttendance'])
        ->name('attendance.biometric.sync')
        ->middleware('auth:church_system'); // Explicitly add auth middleware

    Route::post('/stats/trigger-notifications', [AttendanceController::class, 'triggerNotifications'])->name('attendance.trigger.notifications');
    Route::get('/attendance/member/{memberId}/history', [AttendanceController::class, 'memberHistory'])->name('attendance.member.history');
    Route::get('/attendance/service/{serviceType}/{serviceId}/report', [AttendanceController::class, 'serviceReport'])->name('attendance.service.report');
    // Primary route that works with php artisan serve (avoids conflict with public/attendance directory)
    Route::get('/stats/attendance', [AttendanceController::class, 'statistics'])->name('attendance.statistics');
    // Alternative routes for compatibility
    Route::get('/attendance-stats', [AttendanceController::class, 'statistics'])->name('attendance.statistics.alt');
    Route::get('/attendance/statistics', [AttendanceController::class, 'statistics'])->name('attendance.statistics.legacy');
    // Primary route that works with php artisan serve (avoids conflict with public/attendance directory)
    Route::get('/stats/missed-members', [AttendanceController::class, 'getMembersWithMissedAttendance'])->name('attendance.missed.members');
    // Alternative route for compatibility
    Route::get('/attendance/missed-members', [AttendanceController::class, 'getMembersWithMissedAttendance'])->name('attendance.missed.members.legacy');
    // Generic routes come LAST
    Route::get('/service-attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::post('/service-attendance', [AttendanceController::class, 'store'])->name('attendance.store');

    // Biometric Device Testing Routes
    Route::get('/biometric/test', [ZKTecoController::class, 'index'])->name('biometric.test');
    Route::post('/biometric/test-connection', [ZKTecoController::class, 'testConnection'])->name('biometric.test-connection');
    Route::post('/biometric/device-info', [ZKTecoController::class, 'getDeviceInfo'])->name('biometric.device-info');
    Route::post('/biometric/attendance', [ZKTecoController::class, 'getAttendance'])->name('biometric.attendance');
    Route::post('/biometric/users', [ZKTecoController::class, 'getUsers'])->name('biometric.users');

    // Biometric Member Registration Routes
    Route::post('/biometric/register-member', [ZKTecoController::class, 'registerMember'])->name('biometric.register-member');
    Route::post('/biometric/register-members-bulk', [ZKTecoController::class, 'registerMembersBulk'])->name('biometric.register-members-bulk');
    Route::get('/biometric/search-members', [ZKTecoController::class, 'searchMembers'])->name('biometric.search-members');

    // Promise Guests routes
    Route::resource('promise-guests', WauminiChurch\Http\Controllers\PromiseGuestController::class);
    Route::post('/promise-guests/{promiseGuest}/send-notification', [WauminiChurch\Http\Controllers\PromiseGuestController::class, 'sendNotification'])->name('promise-guests.send-notification');
    Route::post('/promise-guests/{promiseGuest}/mark-attended', [WauminiChurch\Http\Controllers\PromiseGuestController::class, 'markAttended'])->name('promise-guests.mark-attended');

    // Celebrations routes
    Route::post('/celebrations', [CelebrationController::class, 'store'])->name('celebrations.store');
    Route::get('/celebrations/{celebration}', [CelebrationController::class, 'show'])->name('celebrations.show');
    Route::put('/celebrations/{celebration}', [CelebrationController::class, 'update'])->name('celebrations.update');
    Route::delete('/celebrations/{celebration}', [CelebrationController::class, 'destroy'])->name('celebrations.destroy');
    Route::get('/celebrations-export/csv', [CelebrationController::class, 'exportCsv'])->name('celebrations.export.csv');

    // Bereavement routes
    Route::get('/bereavement', [WauminiChurch\Http\Controllers\BereavementController::class, 'index'])->name('bereavement.index');
    Route::get('/bereavement/create', [WauminiChurch\Http\Controllers\BereavementController::class, 'create'])->name('bereavement.create');
    Route::post('/bereavement', [WauminiChurch\Http\Controllers\BereavementController::class, 'store'])->name('bereavement.store');
    Route::get('/bereavement/{bereavement}', [WauminiChurch\Http\Controllers\BereavementController::class, 'show'])->name('bereavement.show');
    Route::put('/bereavement/{bereavement}', [WauminiChurch\Http\Controllers\BereavementController::class, 'update'])->name('bereavement.update');
    Route::delete('/bereavement/{bereavement}', [WauminiChurch\Http\Controllers\BereavementController::class, 'destroy'])->name('bereavement.destroy');
    Route::post('/bereavement/{bereavement}/contribution', [WauminiChurch\Http\Controllers\BereavementController::class, 'recordContribution'])->name('bereavement.record-contribution');
    Route::post('/bereavement/{bereavement}/non-contributor', [WauminiChurch\Http\Controllers\BereavementController::class, 'markNonContributor'])->name('bereavement.mark-non-contributor');
    Route::post('/bereavement/{bereavement}/close', [WauminiChurch\Http\Controllers\BereavementController::class, 'close'])->name('bereavement.close');
    Route::get('/bereavement/{bereavement}/summary', [WauminiChurch\Http\Controllers\BereavementController::class, 'summaryReport'])->name('bereavement.summary');
    Route::get('/bereavement/{bereavement}/export/{format}', [WauminiChurch\Http\Controllers\BereavementController::class, 'exportReport'])->name('bereavement.export');
    Route::get('/bereavement-members/notification', [WauminiChurch\Http\Controllers\BereavementController::class, 'getMembersForNotification'])->name('bereavement.members.notification');
});

// Settings routes - Permission-based access control
Route::middleware(['auth:church_system', PreventBackHistory::class, 'treasurer'])->group(function () {
    // View settings - requires settings.view permission
    Route::get('/settings', [SettingsController::class, 'index'])
        ->middleware('permission:settings.view')
        ->name('settings.index');

    // Update settings - requires settings.edit permission
    Route::post('/settings', [SettingsController::class, 'update'])
        ->middleware('permission:settings.edit')
        ->name('settings.update');
    Route::post('/settings/{category}', [SettingsController::class, 'updateCategory'])
        ->middleware('permission:settings.edit')
        ->name('settings.update.category');
    Route::post('/settings/reset', [SettingsController::class, 'reset'])
        ->middleware('permission:settings.edit')
        ->name('settings.reset');
    Route::post('/settings/import', [SettingsController::class, 'import'])
        ->middleware('permission:settings.edit')
        ->name('settings.import');
    Route::post('/settings/set/{key}', [SettingsController::class, 'setValue'])
        ->middleware('permission:settings.edit')
        ->name('settings.set');

    // View-only routes - require settings.view permission
    Route::get('/settings/export', [SettingsController::class, 'export'])
        ->middleware('permission:settings.view')
        ->name('settings.export');
    Route::get('/settings/get/{key}', [SettingsController::class, 'getValue'])
        ->middleware('permission:settings.view')
        ->name('settings.get');
    Route::get('/settings/audit-logs', [SettingsController::class, 'auditLogs'])
        ->middleware('permission:settings.view')
        ->name('settings.audit-logs');
    Route::get('/settings/backup', [SettingsController::class, 'backup'])
        ->middleware('permission:settings.view')
        ->name('settings.backup');
    Route::post('/settings/restore', [SettingsController::class, 'restore'])
        ->middleware('permission:settings.edit')
        ->name('settings.restore');
    Route::get('/settings/help', function () {
        return view('settings.help');
    })
        ->middleware('permission:settings.view')
        ->name('settings.help');
    Route::get('/settings/analytics', [SettingsController::class, 'analytics'])
        ->middleware('permission:settings.view')
        ->name('settings.analytics');
});



// Test route for biometric sync (temporary - for debugging)
Route::post('/test-biometric-sync', [AttendanceController::class, 'syncBiometricAttendance'])
    ->middleware('auth:church_system')
    ->name('test.biometric.sync');

Route::get('/', function () {
    return view('landing');
})->name('landing_page');


// Redirect /dashboard based on user role
Route::get('/dashboard', function () {
    if (!auth()->check()) {
        return redirect()->route('login');
    }

    $user = auth()->user();
    if ($user->isAdmin()) {
        return redirect()->route('admin.dashboard');
    } elseif ($user->isPastor()) {
        return redirect()->route('dashboard.pastor');
    } elseif ($user->isTreasurer() || $user->isAccountant()) {
        return redirect()->route('finance.dashboard');
    } elseif ($user->isMember()) {
        return redirect()->route('member.dashboard');
    } else {
        return redirect()->route('dashboard.secretary');
    }
})->middleware('auth:church_system')->name('dashboard');



// Test route for debugging member creation
Route::get('/test-member-creation', function () {
    try {
        $member = \WauminiChurch\Models\Member::create([
            'member_id' => \WauminiChurch\Models\Member::generateMemberId(),
            'member_type' => 'independent',
            'membership_type' => 'permanent',
            'full_name' => 'Test User',
            'phone_number' => '+255712345678',
            'date_of_birth' => '1990-01-01',
            'gender' => 'male',
            'profession' => 'Developer',
            'region' => 'Dar es Salaam',
            'district' => 'Kinondoni',
            'ward' => 'Test Ward',
            'street' => 'Test Street',
            'address' => 'Test Address',
            'tribe' => 'Test Tribe',
        ]);

        return response()->json(['success' => true, 'member' => $member]);
    } catch (Exception $e) {
        return response()->json(['success' => false, 'error' => $e->getMessage()]);
    }
});

// Test route for debugging special event creation
Route::get('/test-special-event-creation', function () {
    try {
        $event = \WauminiChurch\Models\SpecialEvent::create([
            'event_date' => '2024-01-01',
            'title' => 'Test Event',
            'speaker' => 'Test Speaker',
            'start_time' => '10:00:00',
            'end_time' => '12:00:00',
            'venue' => 'Test Venue',
            'attendance_count' => 50,
            'budget_amount' => 1000.00,
            'category' => 'Test Category',
            'description' => 'Test Description',
            'notes' => 'Test Notes',
        ]);

        return response()->json(['success' => true, 'event' => $event]);
    } catch (Exception $e) {
        return response()->json(['success' => false, 'error' => $e->getMessage()]);
    }
});

// Test route to get CSRF token
Route::get('/test-csrf', function () {
    return response()->json([
        'csrf_token' => csrf_token(),
        'message' => 'CSRF token generated'
    ]);
});

// Public SMS diagnostic route (temporary): returns provider response without auth
Route::get('/public-test-sms', function (Request $request) {
    $to = $request->query('to');
    $text = $request->query('text', 'Karibu Waumini Church!');
    if (empty($to)) {
        return response()->json([
            'success' => false,
            'message' => 'Provide query params: to=+255XXXXXXXXX and optional text=...'
        ], 422);
    }
    try {
        $resp = app(\WauminiChurch\Services\SmsService::class)->sendDebug($to, $text);
        $conf = [
            'enabled' => \WauminiChurch\Services\SettingsService::get('enable_sms_notifications', false),
            'api_url' => \WauminiChurch\Services\SettingsService::get('sms_api_url'),
            'username' => \WauminiChurch\Services\SettingsService::get('sms_username'),
            'sender_id' => \WauminiChurch\Services\SettingsService::get('sms_sender_id'),
        ];
        return response()->json([
            'success' => $resp['ok'] ?? false,
            'to' => $to,
            'text' => $text,
            'provider_status' => $resp['status'] ?? null,
            'provider_body' => $resp['body'] ?? null,
            'reason' => $resp['reason'] ?? null,
            'request' => $resp['request'] ?? null,
            'error' => $resp['error'] ?? null,
            'config' => $conf,
        ]);
    } catch (\Throwable $e) {
        return response()->json([
            'success' => false,
            'message' => $e->getMessage()
        ], 500);
    }
});

// Debug route for testing authentication
Route::get('/debug-auth', function () {
    if (!auth()->check()) {
        return response()->json(['error' => 'Not authenticated']);
    }

    $user = auth()->user();
    return response()->json([
        'user' => $user->name,
        'email' => $user->email,
        'role' => $user->role,
        'can_approve_finances' => $user->can_approve_finances,
        'isPastor' => $user->isPastor(),
        'isAdmin' => $user->isAdmin(),
        'isTreasurer' => $user->isTreasurer(),
        'canApproveFinances' => $user->canApproveFinances(),
        'dashboard_redirect' => $user->isPastor() ? 'pastor' : ($user->isTreasurer() ? 'treasurer' : 'secretary')
    ]);
})->middleware('auth:church_system');

// Simple test route for approval dashboard
Route::get('/test-approval', function () {
    if (!auth()->check()) {
        return 'Not logged in';
    }

    $user = auth()->user();
    if (!$user->canApproveFinances()) {
        return 'Unauthorized - cannot approve finances';
    }

    return 'Authorized - can approve finances';
})->middleware('auth:church_system');

// Very simple test route
Route::get('/simple-test', function () {
    if (!auth()->check()) {
        return 'Not logged in';
    }

    $user = auth()->user();
    return 'Logged in as: ' . $user->name . ' (Role: ' . $user->role . ')';
})->middleware('auth:church_system');

// Debug route to test special event creation
Route::post('/debug-special-events', function (Request $request) {
    \Log::info('Debug special event route called', ['request_data' => $request->all()]);

    try {
        $event = \WauminiChurch\Models\SpecialEvent::create([
            'event_date' => $request->event_date,
            'title' => $request->title,
            'speaker' => $request->speaker,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'venue' => $request->venue,
            'attendance_count' => $request->attendance_count,
            'budget_amount' => $request->budget_amount,
            'category' => $request->category,
            'description' => $request->description,
            'notes' => $request->notes,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Event created successfully',
            'event' => $event
        ], 200);
    } catch (Exception $e) {
        \Log::error('Debug special event error', ['error' => $e->getMessage()]);
        return response()->json([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ], 500);
    }
});

// Test email configuration
Route::get('/test-email', function () {
    try {
        \Mail::raw('Test email from Waumini Link notification system!', function ($message) {
            $message->to('oftenfred.ict@gmail.com')
                ->subject('Waumini Link - Email Test');
        });

        return response()->json([
            'success' => true,
            'message' => 'Test email sent successfully to oftenfred.ict@gmail.com'
        ]);
    } catch (Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Email error: ' . $e->getMessage()
        ]);
    }
});

// Notification data route
Route::get('/notifications/data', [NotificationController::class, 'getNotificationData'])->name('notifications.data');

// Test route to check database records
Route::get('/test-notifications', function () {
    try {
        $events = \WauminiChurch\Models\SpecialEvent::count();
        $celebrations = \WauminiChurch\Models\Celebration::count();
        $services = \WauminiChurch\Models\SundayService::count();

        return response()->json([
            'success' => true,
            'events' => $events,
            'celebrations' => $celebrations,
            'services' => $services,
            'total' => $events + $celebrations + $services
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
});

// Debug route to test notification controller
Route::get('/debug-notifications', function () {
    try {
        $controller = new \WauminiChurch\Http\Controllers\NotificationController();
        $response = $controller->getNotificationData();
        return $response;
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
});

// Test route to check approval system
Route::get('/test-approval-system', function () {
    try {
        // Check if we have any pending records
        $pendingTithes = \WauminiChurch\Models\Tithe::where('approval_status', 'pending')->count();
        $pendingOfferings = \WauminiChurch\Models\Offering::where('approval_status', 'pending')->count();
        $pendingDonations = \WauminiChurch\Models\Donation::where('approval_status', 'pending')->count();
        $pendingExpenses = \WauminiChurch\Models\Expense::where('approval_status', 'pending')->count();
        $pendingBudgets = \WauminiChurch\Models\Budget::where('approval_status', 'pending')->count();

        $totalPending = $pendingTithes + $pendingOfferings + $pendingDonations + $pendingExpenses + $pendingBudgets;

        return response()->json([
            'success' => true,
            'message' => 'Approval system check',
            'pending_records' => [
                'tithes' => $pendingTithes,
                'offerings' => $pendingOfferings,
                'donations' => $pendingDonations,
                'expenses' => $pendingExpenses,
                'budgets' => $pendingBudgets,
                'total' => $totalPending
            ],
            'approval_url' => '/finance/approval/dashboard'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
});

// Simple test route to create one pending record
Route::get('/create-simple-test-data', function () {
    try {
        // Create a simple test member
        $member = \WauminiChurch\Models\Member::firstOrCreate(
            ['member_id' => 'TEST001'],
            [
                'member_type' => 'independent',
                'membership_type' => 'permanent',
                'full_name' => 'Test Member for Approval',
                'phone_number' => '+255712345678',
                'date_of_birth' => '1990-01-01',
                'gender' => 'male',
                'profession' => 'Developer',
                'region' => 'Dar es Salaam',
                'district' => 'Kinondoni',
                'ward' => 'Test Ward',
                'street' => 'Test Street',
                'address' => 'Test Address',
                'tribe' => 'Test Tribe',
            ]
        );

        $today = \Carbon\Carbon::today();

        // Create one pending tithe
        $tithe = \WauminiChurch\Models\Tithe::create([
            'member_id' => $member->id,
            'amount' => 50.00,
            'tithe_date' => $today,
            'payment_method' => 'cash',
            'reference_number' => 'TEST' . time(),
            'notes' => 'Test tithe for approval testing',
            'recorded_by' => 'Test User',
            'approval_status' => 'pending'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Test data created successfully!',
            'tithe_id' => $tithe->id,
            'approval_url' => '/finance/approval/dashboard'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
});

// Test route to create data for all tabs
Route::get('/create-all-tab-test-data', function () {
    try {
        // Create a simple test member
        $member = \WauminiChurch\Models\Member::firstOrCreate(
            ['member_id' => 'TABTEST001'],
            [
                'member_type' => 'independent',
                'membership_type' => 'permanent',
                'full_name' => 'Tab Test Member',
                'phone_number' => '+255712345679',
                'date_of_birth' => '1990-01-01',
                'gender' => 'male',
                'profession' => 'Developer',
                'region' => 'Dar es Salaam',
                'district' => 'Kinondoni',
                'ward' => 'Test Ward',
                'street' => 'Test Street',
                'address' => 'Test Address',
                'tribe' => 'Test Tribe',
            ]
        );

        $today = \Carbon\Carbon::today();
        $time = time();

        // Create pending tithe
        $tithe = \WauminiChurch\Models\Tithe::create([
            'member_id' => $member->id,
            'amount' => 100.00,
            'tithe_date' => $today,
            'payment_method' => 'cash',
            'reference_number' => 'TITHE' . $time,
            'notes' => 'Test tithe for tab testing',
            'recorded_by' => 'Test User',
            'approval_status' => 'pending'
        ]);

        // Create pending offering
        $offering = \WauminiChurch\Models\Offering::create([
            'member_id' => $member->id,
            'amount' => 50.00,
            'offering_date' => $today,
            'payment_method' => 'cash',
            'reference_number' => 'OFFER' . $time,
            'notes' => 'Test offering for tab testing',
            'recorded_by' => 'Test User',
            'approval_status' => 'pending'
        ]);

        // Create pending donation
        $donation = \WauminiChurch\Models\Donation::create([
            'donor_name' => 'Tab Test Donor',
            'amount' => 200.00,
            'donation_date' => $today,
            'payment_method' => 'bank_transfer',
            'reference_number' => 'DONATE' . $time,
            'notes' => 'Test donation for tab testing',
            'recorded_by' => 'Test User',
            'approval_status' => 'pending'
        ]);

        // Create pending expense
        $expense = \WauminiChurch\Models\Expense::create([
            'description' => 'Test expense for tab testing',
            'amount' => 75.00,
            'expense_date' => $today,
            'category' => 'office_supplies',
            'payment_method' => 'cash',
            'reference_number' => 'EXPENSE' . $time,
            'notes' => 'Test expense for tab testing',
            'recorded_by' => 'Test User',
            'status' => 'pending',
            'approval_status' => 'pending'
        ]);

        // Create pending budget
        $budget = \WauminiChurch\Models\Budget::create([
            'name' => 'Test Budget for Tab Testing',
            'amount' => 1000.00,
            'start_date' => $today,
            'end_date' => $today->copy()->addMonth(),
            'category' => 'general',
            'description' => 'Test budget for tab testing',
            'recorded_by' => 'Test User',
            'approval_status' => 'pending'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'All tab test data created successfully!',
            'records' => [
                'tithe_id' => $tithe->id,
                'offering_id' => $offering->id,
                'donation_id' => $donation->id,
                'expense_id' => $expense->id,
                'budget_id' => $budget->id
            ],
            'approval_url' => '/finance/approval/dashboard'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
});

// Test route to create a test offering for approval
Route::get('/test-offering-approval', function () {
    try {
        // Create a test member if none exists
        $member = \WauminiChurch\Models\Member::first();
        if (!$member) {
            $member = \WauminiChurch\Models\Member::create([
                'member_id' => 'TEST001',
                'member_type' => 'independent',
                'membership_type' => 'permanent',
                'full_name' => 'Test Member for Offering',
                'phone_number' => '+255712345678',
                'date_of_birth' => '1990-01-01',
                'gender' => 'male',
                'profession' => 'Developer',
                'region' => 'Dar es Salaam',
                'district' => 'Kinondoni',
                'ward' => 'Test Ward',
                'street' => 'Test Street',
                'address' => 'Test Address',
                'tribe' => 'Test Tribe',
            ]);
        }

        $today = \Carbon\Carbon::today();

        // Create a test offering
        $offering = \WauminiChurch\Models\Offering::create([
            'member_id' => $member->id,
            'amount' => 100.00,
            'offering_date' => $today,
            'offering_type' => 'general',
            'service_type' => 'sunday_service',
            'payment_method' => 'cash',
            'reference_number' => 'TEST' . time(),
            'notes' => 'Test offering for approval system',
            'recorded_by' => 'Test User',
            'approval_status' => 'pending'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Test offering created successfully!',
            'offering' => [
                'id' => $offering->id,
                'amount' => $offering->amount,
                'approval_status' => $offering->approval_status,
                'member_name' => $offering->member->full_name
            ],
            'pastor_dashboard_url' => '/pastor/dashboard',
            'approval_dashboard_url' => '/finance/approval/dashboard'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
});

// Test route to create approval test data
Route::get('/create-approval-test-data', function () {
    try {
        $member = \WauminiChurch\Models\Member::first();
        if (!$member) {
            $member = \WauminiChurch\Models\Member::create([
                'member_id' => 'M001',
                'member_type' => 'independent',
                'membership_type' => 'permanent',
                'full_name' => 'Test Member',
                'phone_number' => '+255712345678',
                'date_of_birth' => '1990-01-01',
                'gender' => 'male',
                'profession' => 'Developer',
                'region' => 'Dar es Salaam',
                'district' => 'Kinondoni',
                'ward' => 'Test Ward',
                'street' => 'Test Street',
                'address' => 'Test Address',
                'tribe' => 'Test Tribe',
            ]);
        }

        $today = \Carbon\Carbon::today();

        // Create pending tithe
        \WauminiChurch\Models\Tithe::create([
            'member_id' => $member->id,
            'amount' => 100.00,
            'tithe_date' => $today,
            'payment_method' => 'cash',
            'reference_number' => 'T' . time(),
            'notes' => 'Test tithe for approval',
            'recorded_by' => 'Test User',
            'approval_status' => 'pending'
        ]);

        // Create pending offering
        \WauminiChurch\Models\Offering::create([
            'member_id' => $member->id,
            'amount' => 50.00,
            'offering_date' => $today,
            'payment_method' => 'cash',
            'reference_number' => 'O' . time(),
            'notes' => 'Test offering for approval',
            'recorded_by' => 'Test User',
            'approval_status' => 'pending'
        ]);

        // Create pending donation
        \WauminiChurch\Models\Donation::create([
            'donor_name' => 'General Member',
            'amount' => 200.00,
            'donation_date' => $today,
            'payment_method' => 'bank_transfer',
            'reference_number' => 'D' . time(),
            'notes' => 'Test donation for approval',
            'recorded_by' => 'Test User',
            'approval_status' => 'pending'
        ]);

        // Create pending expense
        \WauminiChurch\Models\Expense::create([
            'description' => 'Test expense for approval',
            'amount' => 75.00,
            'expense_date' => $today,
            'category' => 'office_supplies',
            'payment_method' => 'cash',
            'reference_number' => 'E' . time(),
            'notes' => 'Test expense for approval',
            'recorded_by' => 'Test User',
            'status' => 'pending',
            'approval_status' => 'pending'
        ]);

        // Create pending budget
        \WauminiChurch\Models\Budget::create([
            'name' => 'Test Budget for Approval',
            'amount' => 1000.00,
            'start_date' => $today,
            'end_date' => $today->copy()->addMonth(),
            'category' => 'general',
            'description' => 'Test budget for approval',
            'recorded_by' => 'Test User',
            'approval_status' => 'pending'
        ]);

        $totalPending = \WauminiChurch\Models\Tithe::where('approval_status', 'pending')->count() +
            \WauminiChurch\Models\Offering::where('approval_status', 'pending')->count() +
            \WauminiChurch\Models\Donation::where('approval_status', 'pending')->count() +
            \WauminiChurch\Models\Expense::where('approval_status', 'pending')->count() +
            \WauminiChurch\Models\Budget::where('approval_status', 'pending')->count();

        return response()->json([
            'success' => true,
            'message' => 'Test approval data created successfully!',
            'total_pending' => $totalPending,
            'redirect_url' => '/finance/approval/dashboard'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
});

// Admin routes - Only accessible by administrators
Route::middleware(['auth:church_system', PreventBackHistory::class])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/logs', [AdminController::class, 'logs'])->name('logs');
    Route::get('/activity-logs', [AdminController::class, 'activityLogs'])->name('activity-logs');
    Route::post('/logs/block-ip', [AdminController::class, 'blockIp'])->name('logs.block-ip');
    Route::post('/logs/unblock-ip', [AdminController::class, 'unblockIp'])->name('logs.unblock-ip');
    Route::get('/system-logs/{logId}/device-details', [AdminController::class, 'getDeviceDetails'])->name('logs.device-details');
    Route::get('/sessions', [AdminController::class, 'sessions'])->name('sessions');
    Route::post('/sessions/{sessionId}/revoke', [AdminController::class, 'revokeSession'])->name('sessions.revoke');
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::get('/users/create', [AdminController::class, 'create'])->name('users.create');
    Route::post('/users', [AdminController::class, 'store'])->name('users.store');
    Route::post('/users/{userId}/unblock', [AdminController::class, 'unblockUser'])->name('users.unblock');
    Route::get('/users/{userId}/activity', [AdminController::class, 'userActivity'])->name('user-activity');
    Route::get('/users/{userId}/edit', [AdminController::class, 'edit'])->name('users.edit');
    Route::put('/users/{userId}', [AdminController::class, 'update'])->name('users.update');
    Route::delete('/users/{userId}', [AdminController::class, 'destroy'])->name('users.destroy');
    Route::post('/users/{userId}/reset-password', [AdminController::class, 'resetPassword'])->name('users.reset-password');
    Route::get('/roles-permissions', [AdminController::class, 'rolesPermissions'])->name('roles-permissions');
    Route::post('/roles-permissions/update', [AdminController::class, 'updateRolePermissions'])->name('roles-permissions.update');
    Route::get('/otps', [AdminController::class, 'otps'])->name('otps');
    Route::get('/system-monitor', [AdminController::class, 'systemMonitor'])->name('system-monitor');
    Route::get('/system-info', [AdminController::class, 'getSystemInfo'])->name('system-info');
    Route::post('/clear-cache', [AdminController::class, 'clearCache'])->name('clear-cache');
});

// Debug route to test attendance clear-all (temporary - remove after testing)
Route::middleware(['auth:church_system'])->group(function () {
    Route::get('/test-attendance-clear-route', function () {
        $route = \Illuminate\Support\Facades\Route::getRoutes()->getByName('attendance.clear-all');
        $allRoutes = \Illuminate\Support\Facades\Route::getRoutes();
        $matchingRoutes = [];

        foreach ($allRoutes as $r) {
            if (str_contains($r->uri(), 'attendance/clear-all') || str_contains($r->uri(), 'attendance')) {
                $matchingRoutes[] = [
                    'uri' => $r->uri(),
                    'methods' => $r->methods(),
                    'name' => $r->getName(),
                    'middleware' => $r->gatherMiddleware(),
                ];
            }
        }

        // Try to match the route manually
        $request = request();
        $request->setMethod('POST');
        $request->server->set('REQUEST_URI', '/attendance/clear-all');
        $matchedRoute = \Illuminate\Support\Facades\Route::getRoutes()->match($request);

        if ($route) {
            return response()->json([
                'success' => true,
                'route_exists' => true,
                'uri' => $route->uri(),
                'methods' => $route->methods(),
                'middleware' => $route->gatherMiddleware(),
                'action' => $route->getActionName(),
                'matched_route' => $matchedRoute ? [
                    'uri' => $matchedRoute->uri(),
                    'name' => $matchedRoute->getName(),
                ] : null,
                'all_attendance_routes' => $matchingRoutes,
                'user' => [
                    'id' => auth()->user()?->id,
                    'role' => auth()->user()?->role,
                    'is_admin' => auth()->user()?->isAdmin(),
                    'is_treasurer' => auth()->user()?->isTreasurer(),
                ]
            ]);
        }
        return response()->json([
            'success' => false,
            'route_exists' => false,
            'all_attendance_routes' => $matchingRoutes
        ]);
    });

    // Test POST route - direct proxy to clear-all
    Route::post('/test-attendance-clear-post', function (\Illuminate\Http\Request $request) {
        try {
            $controller = new \WauminiChurch\Http\Controllers\AttendanceController();
            return $controller->clearAll($request);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    });
});

// Announcements routes (for secretary/admin)
Route::middleware(['auth:church_system', PreventBackHistory::class, 'treasurer'])->group(function () {
    Route::resource('announcements', AnnouncementController::class);
    Route::post('announcements/{announcement}/send-sms', [AnnouncementController::class, 'sendSms'])->name('announcements.send-sms');
});

// Member routes
Route::middleware(['auth:church_system', PreventBackHistory::class])->prefix('member')->name('member.')->group(function () {
    Route::get('/dashboard', [MemberDashboardController::class, 'index'])->name('dashboard');
    Route::get('/information', [MemberDashboardController::class, 'information'])->name('information');
    Route::get('/finance', [MemberDashboardController::class, 'finance'])->name('finance');
    Route::get('/announcements', [MemberDashboardController::class, 'announcements'])->name('announcements');
    Route::get('/leaders', [MemberDashboardController::class, 'leaders'])->name('leaders');
    Route::get('/settings', [MemberDashboardController::class, 'settings'])->name('settings');
    Route::post('/profile/update', [MemberDashboardController::class, 'updateProfile'])->name('profile.update');
    Route::get('/change-password', [MemberDashboardController::class, 'showChangePassword'])->name('change-password');
    Route::post('/change-password', [MemberDashboardController::class, 'updatePassword'])->name('password.update');
    Route::post('/notifications/{notification}/read', [MemberDashboardController::class, 'markNotificationAsRead'])->name('notifications.read');
});

// Serve storage files directly (bypasses symlink issues)
Route::get('/storage/{path}', function ($path) {
    $filePath = storage_path('app/public/' . $path);

    // Security: Only allow files in public storage
    $realPath = realpath($filePath);
    $storagePath = realpath(storage_path('app/public'));

    // Check if file exists and is within storage/app/public directory
    if (!$realPath || strpos($realPath, $storagePath) !== 0) {
        abort(404);
    }

    // Check if file exists
    if (!file_exists($realPath) || !is_file($realPath)) {
        abort(404);
    }

    // Get MIME type
    $mimeType = mime_content_type($realPath);
    if (!$mimeType) {
        // Fallback MIME types for common image formats
        $extension = strtolower(pathinfo($realPath, PATHINFO_EXTENSION));
        $mimeTypes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
        ];
        $mimeType = $mimeTypes[$extension] ?? 'application/octet-stream';
    }

    // Set headers and return file
    return response()->file($realPath, [
        'Content-Type' => $mimeType,
        'Cache-Control' => 'public, max-age=31536000', // Cache for 1 year
    ]);
})->where('path', '.*')->name('storage.serve');


