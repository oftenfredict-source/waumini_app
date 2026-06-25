<?php

use App\Http\Controllers\Church\AttendanceController;
use App\Http\Controllers\Church\AnnouncementController;
use App\Http\Controllers\Church\BereavementController;
use App\Http\Controllers\Church\ChurchServiceController;
use App\Http\Controllers\Church\DashboardController;
use App\Http\Controllers\Church\DepartmentController;
use App\Http\Controllers\Church\BranchController;
use App\Http\Controllers\Church\FinanceApprovalController;
use App\Http\Controllers\Church\FinanceDashboardController;
use App\Http\Controllers\Church\LeaderController;
use App\Http\Controllers\Church\ForgotPasswordController;
use App\Http\Controllers\Church\LoginController;
use App\Http\Controllers\Church\MemberChildController;
use App\Http\Controllers\Church\MemberController;
use App\Http\Controllers\Church\OfferingController;
use App\Http\Controllers\Church\PledgeController;
use App\Http\Controllers\Church\CelebrationController;
use App\Http\Controllers\Church\PromiseGuestController;
use App\Http\Controllers\Church\BudgetController;
use App\Http\Controllers\Church\ChurchAssetController;
use App\Http\Controllers\Church\ExpenseController;
use App\Http\Controllers\Church\SpecialEventController;
use App\Http\Controllers\Church\TitheController;
use App\Http\Controllers\Church\System\LogController as SystemLogController;
use App\Http\Controllers\Church\System\SessionController as SystemSessionController;
use App\Http\Controllers\Church\System\UserController as SystemUserController;
use App\Http\Controllers\Church\System\RolePermissionController;
use App\Http\Controllers\Church\System\MonitorController;
use App\Http\Controllers\Church\System\SettingsController;
use App\Http\Controllers\Church\System\SubscriptionController;
use App\Http\Controllers\Church\System\OtpController;
use App\Http\Controllers\Church\System\SmsStoreController;
use App\Http\Controllers\Church\MemberPortal\DashboardController as MemberPortalDashboardController;
use App\Http\Controllers\Church\MemberPortal\ProfileController as MemberPortalProfileController;
use App\Http\Controllers\Church\MemberPortal\AnnouncementController as MemberPortalAnnouncementController;
use App\Http\Controllers\Church\MemberPortal\LeaderController as MemberPortalLeaderController;
use App\Http\Controllers\Church\MemberPortal\ServiceController as MemberPortalServiceController;
use App\Http\Controllers\Church\MemberPortal\RequestController as MemberPortalRequestController;
use App\Http\Controllers\Church\MemberRequestController;
use App\Http\Controllers\Church\AnalyticsController;
use App\Http\Controllers\Church\ReportController;
use Illuminate\Support\Facades\Route;

Route::middleware('church.maintenance')->group(function () {
    Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [LoginController::class, 'login'])->name('login.submit');
    Route::get('login/otp', [LoginController::class, 'showOtpForm'])->name('login.otp');
    Route::post('login/otp', [LoginController::class, 'verifyOtp'])->name('login.otp.verify');
    Route::post('login/otp/resend', [LoginController::class, 'resendOtp'])->name('login.otp.resend');

    Route::get('forgot-password', [ForgotPasswordController::class, 'showRequestForm'])->name('password.forgot');
    Route::post('forgot-password', [ForgotPasswordController::class, 'sendOtp'])->name('password.forgot.send');
    Route::get('forgot-password/verify', [ForgotPasswordController::class, 'showVerifyForm'])->name('password.forgot.verify');
    Route::post('forgot-password/verify', [ForgotPasswordController::class, 'verifyOtp'])->name('password.forgot.verify.submit');
    Route::post('forgot-password/resend', [ForgotPasswordController::class, 'resendOtp'])->name('password.forgot.resend');
    Route::get('forgot-password/reset', [ForgotPasswordController::class, 'showResetForm'])->name('password.forgot.reset');
    Route::post('forgot-password/reset', [ForgotPasswordController::class, 'resetPassword'])->name('password.forgot.reset.submit');

    Route::get('register', [\App\Http\Controllers\Church\MemberSelfRegistrationController::class, 'create'])->name('register');
    Route::post('register', [\App\Http\Controllers\Church\MemberSelfRegistrationController::class, 'store'])->name('register.submit');
    Route::get('register/success/{reference}', [\App\Http\Controllers\Church\MemberSelfRegistrationController::class, 'success'])->name('register.success');

    Route::middleware(['auth', 'church'])->group(function () {
    Route::post('logout', [LoginController::class, 'logout'])->name('logout');
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::middleware('church.member')->prefix('my')->name('member.')->group(function () {
        Route::get('/', [MemberPortalDashboardController::class, 'index'])->name('dashboard');
        Route::get('profile', [MemberPortalProfileController::class, 'edit'])->name('profile.edit');
        Route::put('profile', [MemberPortalProfileController::class, 'update'])->name('profile.update');
        Route::put('profile/password', [MemberPortalProfileController::class, 'updatePassword'])->name('profile.password');
        Route::get('announcements', [MemberPortalAnnouncementController::class, 'index'])->name('announcements.index');
        Route::get('announcements/{announcement}', [MemberPortalAnnouncementController::class, 'show'])->name('announcements.show');
        Route::get('leaders', [MemberPortalLeaderController::class, 'index'])->name('leaders.index');
        Route::get('services', [MemberPortalServiceController::class, 'index'])->name('services.index');
        Route::get('requests', [MemberPortalRequestController::class, 'index'])->name('requests.index');
        Route::get('requests/create', [MemberPortalRequestController::class, 'create'])->name('requests.create');
        Route::post('requests', [MemberPortalRequestController::class, 'store'])->name('requests.store');
        Route::get('requests/{member_request}', [MemberPortalRequestController::class, 'show'])->name('requests.show');
        Route::get('requests/{member_request}/certificate', [MemberPortalRequestController::class, 'downloadCertificate'])->name('requests.certificate');
    });

    Route::get('member-registrations', [\App\Http\Controllers\Church\MemberRegistrationApplicationController::class, 'index'])->name('member-registrations.index');
    Route::get('member-registrations/check-envelope', [\App\Http\Controllers\Church\MemberRegistrationApplicationController::class, 'checkEnvelope'])->name('member-registrations.check-envelope');
    Route::get('member-registrations/{registration}', [\App\Http\Controllers\Church\MemberRegistrationApplicationController::class, 'show'])->name('member-registrations.show');
    Route::post('member-registrations/{registration}/approve', [\App\Http\Controllers\Church\MemberRegistrationApplicationController::class, 'approve'])->name('member-registrations.approve');
    Route::post('member-registrations/{registration}/reject', [\App\Http\Controllers\Church\MemberRegistrationApplicationController::class, 'reject'])->name('member-registrations.reject');

    Route::get('member-requests', [MemberRequestController::class, 'index'])->name('member-requests.index');
    Route::get('member-requests/{member_request}', [MemberRequestController::class, 'show'])->name('member-requests.show');
    Route::get('member-requests/{member_request}/certificate', [MemberRequestController::class, 'downloadCertificate'])->name('member-requests.certificate');
    Route::post('member-requests/{member_request}/respond', [MemberRequestController::class, 'respond'])->name('member-requests.respond');

    Route::get('members/archived', [MemberController::class, 'archived'])->name('members.archived');
    Route::get('members/check-envelope', [MemberController::class, 'checkEnvelope'])->name('members.check-envelope');
    Route::get('members/children', [MemberChildController::class, 'index'])->name('members.children.index');
    Route::get('members/children/create', [MemberChildController::class, 'create'])->name('members.children.create');
    Route::post('members/children', [MemberChildController::class, 'store'])->name('members.children.store');
    Route::post('members/children/process-aged-out', [MemberChildController::class, 'processAgedOut'])->name('members.children.process-aged-out');
    Route::post('members/children/{dependant}/convert', [MemberChildController::class, 'convert'])->name('members.children.convert');
    Route::post('members/{member}/reset-password', [MemberController::class, 'resetPassword'])->name('members.reset-password');
    Route::post('members/{member}/archive', [MemberController::class, 'archive'])->name('members.archive');
    Route::post('members/{member}/restore', [MemberController::class, 'restore'])->name('members.restore');
    Route::post('members/{member}/convert-to-permanent', [MemberController::class, 'convertToPermanent'])->name('members.convert-to-permanent');
    Route::post('members/{member}/extend-membership', [MemberController::class, 'extendMembership'])->name('members.extend-membership');
    Route::resource('members', MemberController::class)->only(['index', 'create', 'store', 'show', 'edit', 'update', 'destroy']);

    Route::resource('leadership', LeaderController::class)
        ->parameters(['leadership' => 'leader'])
        ->only(['index', 'create', 'store', 'show']);
    Route::post('leadership/{leader}/deactivate', [LeaderController::class, 'deactivate'])->name('leadership.deactivate');

    Route::resource('departments', DepartmentController::class)->only(['index', 'create', 'store', 'show', 'edit', 'update', 'destroy']);
    Route::post('departments/{department}/assign-head', [DepartmentController::class, 'assignHead'])->name('departments.assign-head');
    Route::post('departments/{department}/members', [DepartmentController::class, 'attachMembers'])->name('departments.members.attach');
    Route::delete('departments/{department}/members/{member}', [DepartmentController::class, 'removeMember'])->name('departments.members.remove');
    Route::resource('branches', BranchController::class)->except(['destroy']);

    Route::resource('announcements', AnnouncementController::class)->only(['index', 'create', 'store', 'show']);

    Route::resource('services', ChurchServiceController::class)->only(['index', 'create', 'store', 'show', 'edit', 'update', 'destroy']);
    Route::resource('special-events', SpecialEventController::class)
        ->parameters(['special-events' => 'special_event'])
        ->only(['index', 'create', 'store', 'show', 'edit', 'update', 'destroy']);
    Route::get('attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::get('attendance/record', [AttendanceController::class, 'create'])->name('attendance.create');
    Route::post('attendance', [AttendanceController::class, 'store'])->name('attendance.store');
    Route::get('attendance/view', [AttendanceController::class, 'show'])->name('attendance.show');
    Route::resource('bereavements', BereavementController::class)
        ->parameters(['bereavements' => 'bereavement'])
        ->only(['index', 'create', 'store', 'show', 'edit', 'update', 'destroy']);
    Route::resource('promise-guests', PromiseGuestController::class)
        ->parameters(['promise-guests' => 'promise_guest'])
        ->only(['index', 'create', 'store', 'show', 'edit', 'update', 'destroy']);
    Route::post('promise-guests/{promise_guest}/send-sms', [PromiseGuestController::class, 'sendNotification'])
        ->name('promise-guests.send-sms');
    Route::post('promise-guests/{promise_guest}/mark-attended', [PromiseGuestController::class, 'markAttended'])
        ->name('promise-guests.mark-attended');

    Route::resource('celebrations', CelebrationController::class)
        ->parameters(['celebrations' => 'celebration']);
    Route::post('celebrations/sync', [CelebrationController::class, 'sync'])
        ->name('celebrations.sync');

    Route::post('bereavements/{bereavement}/contribution', [BereavementController::class, 'recordContribution'])
        ->name('bereavements.contribution');
    Route::post('bereavements/{bereavement}/non-contributor', [BereavementController::class, 'markNonContributor'])
        ->name('bereavements.non-contributor');
    Route::post('bereavements/{bereavement}/close', [BereavementController::class, 'close'])
        ->name('bereavements.close');

    Route::prefix('finance')->name('finance.')->group(function () {
        Route::get('dashboard', [FinanceDashboardController::class, 'index'])->name('dashboard');
        Route::get('approvals', [FinanceApprovalController::class, 'index'])->name('approvals');
        Route::post('approvals/approve', [FinanceApprovalController::class, 'approve'])->name('approvals.approve');
        Route::post('approvals/reject', [FinanceApprovalController::class, 'reject'])->name('approvals.reject');
        Route::post('approvals/bulk-approve', [FinanceApprovalController::class, 'bulkApprove'])->name('approvals.bulk-approve');
        Route::get('approvals/details/{type}/{id}', [FinanceApprovalController::class, 'details'])->name('approvals.details');
    });

    Route::resource('tithes', TitheController::class)->only(['index', 'create', 'store', 'show', 'edit', 'update', 'destroy']);
    Route::resource('offerings', OfferingController::class)->only(['index', 'create', 'store', 'show', 'edit', 'update', 'destroy']);
    Route::resource('pledges', PledgeController::class)->only(['index', 'create', 'store', 'show', 'edit', 'update', 'destroy']);
    Route::post('pledges/{pledge}/payment', [PledgeController::class, 'recordPayment'])->name('pledges.payment');

    Route::resource('budget/expenses', ExpenseController::class)->only(['index', 'create', 'store', 'show', 'edit', 'update', 'destroy']);
    Route::post('budget/expenses/{expense}/paid', [ExpenseController::class, 'markPaid'])
        ->name('budget.expenses.mark-paid');
    Route::resource('budget', BudgetController::class)->only(['index', 'create', 'store', 'show', 'edit', 'update', 'destroy']);
    Route::post('budget/{budget}/allocate-funds', [BudgetController::class, 'allocateFunds'])
        ->name('budget.allocate-funds');

    Route::resource('church-assets', ChurchAssetController::class)
        ->names('assets')
        ->parameters(['church-assets' => 'asset'])
        ->only(['index', 'create', 'store', 'show', 'edit', 'update', 'destroy']);

    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/overview', [ReportController::class, 'overview'])->name('overview');
        Route::get('/member-summary', [ReportController::class, 'memberSummary'])->name('member-summary');
        Route::get('/member-giving', [ReportController::class, 'memberGiving'])->name('member-giving');
        Route::get('/income-vs-expenditure', [ReportController::class, 'incomeVsExpenditure'])->name('income-vs-expenditure');
        Route::get('/offering-breakdown', [ReportController::class, 'offeringBreakdown'])->name('offering-breakdown');
        Route::get('/budget-performance', [ReportController::class, 'budgetPerformance'])->name('budget-performance');
        Route::get('/attendance-summary', [ReportController::class, 'attendanceSummary'])->name('attendance-summary');
        Route::get('/leadership', [ReportController::class, 'leadership'])->name('leadership');
        Route::get('/monthly-financial', [ReportController::class, 'monthlyFinancial'])->name('monthly-financial');
    });
    Route::get('analytics', [AnalyticsController::class, 'index'])->name('analytics.index');

    Route::prefix('system')->name('system.')->group(function () {
        Route::get('logs', [SystemLogController::class, 'index'])->name('logs.index');
        Route::get('sessions', [SystemSessionController::class, 'index'])->name('sessions.index');
        Route::post('sessions/{sessionId}/revoke', [SystemSessionController::class, 'revoke'])->name('sessions.revoke');
        Route::get('users', [SystemUserController::class, 'index'])->name('users.index');
        Route::get('users/create', [SystemUserController::class, 'create'])->name('users.create');
        Route::post('users', [SystemUserController::class, 'store'])->name('users.store');
        Route::get('users/{user}/edit', [SystemUserController::class, 'edit'])->name('users.edit');
        Route::put('users/{user}', [SystemUserController::class, 'update'])->name('users.update');
        Route::post('users/{user}/reset-password', [SystemUserController::class, 'resetPassword'])->name('users.reset-password');
        Route::get('roles', [RolePermissionController::class, 'index'])->name('roles.index');
        Route::post('roles', [RolePermissionController::class, 'update'])->name('roles.update');
        Route::get('monitor', [MonitorController::class, 'index'])->name('monitor.index');
        Route::get('otps', [OtpController::class, 'index'])->name('otps.index');
        Route::get('sms', [SmsStoreController::class, 'index'])->name('sms.index');
        Route::get('sms/templates/{key}/edit', [SmsStoreController::class, 'editTemplate'])->name('sms.templates.edit');
        Route::put('sms/templates/{key}', [SmsStoreController::class, 'updateTemplate'])->name('sms.templates.update');
        Route::get('sms/messages/{smsLog}', [SmsStoreController::class, 'showMessage'])->name('sms.messages.show');
        Route::put('sms/messages/{smsLog}', [SmsStoreController::class, 'updateMessage'])->name('sms.messages.update');
        Route::post('sms/messages/{smsLog}/resend', [SmsStoreController::class, 'resendMessage'])->name('sms.messages.resend');
        Route::post('sms/send', [SmsStoreController::class, 'sendManual'])->name('sms.send');
        Route::get('settings', [SettingsController::class, 'index'])->name('settings.index');
        Route::put('settings/{tab}', [SettingsController::class, 'update'])->name('settings.update');
        Route::get('subscription', [SubscriptionController::class, 'index'])->name('subscription.index');
        Route::post('subscription/upgrade', [SubscriptionController::class, 'upgrade'])->name('subscription.upgrade');
        Route::get('terms', [SubscriptionController::class, 'terms'])->name('subscription.terms');
    });
    });
});
