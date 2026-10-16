<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MemberController;
use App\Http\Controllers\Api\Pastor\AnnouncementController as PastorAnnouncementController;
use App\Http\Controllers\Api\Pastor\AttendanceController as PastorAttendanceController;
use App\Http\Controllers\Api\Pastor\BereavementController as PastorBereavementController;
use App\Http\Controllers\Api\Pastor\BranchController as PastorBranchController;
use App\Http\Controllers\Api\Pastor\CelebrationController as PastorCelebrationController;
use App\Http\Controllers\Api\Pastor\DashboardController as PastorDashboardController;
use App\Http\Controllers\Api\Pastor\DepartmentController as PastorDepartmentController;
use App\Http\Controllers\Api\Pastor\EventController as PastorEventController;
use App\Http\Controllers\Api\Pastor\FinanceController as PastorFinanceController;
use App\Http\Controllers\Api\Pastor\FormOptionsController as PastorFormOptionsController;
use App\Http\Controllers\Api\Pastor\LeaderController as PastorLeaderController;
use App\Http\Controllers\Api\Pastor\MemberController as PastorMemberController;
use App\Http\Controllers\Api\Pastor\MemberRequestController as PastorMemberRequestController;
use App\Http\Controllers\Api\Pastor\RegistrationController as PastorRegistrationController;
use App\Http\Controllers\Api\Pastor\ServiceController as PastorServiceController;
use App\Http\Controllers\Api\MemberPortal\AnnouncementController as MemberPortalAnnouncementController;
use App\Http\Controllers\Api\MemberPortal\AttendanceController as MemberPortalAttendanceController;
use App\Http\Controllers\Api\MemberPortal\DashboardController as MemberPortalDashboardController;
use App\Http\Controllers\Api\MemberPortal\DepartmentController as MemberPortalDepartmentController;
use App\Http\Controllers\Api\MemberPortal\DiaryController as MemberPortalDiaryController;
use App\Http\Controllers\Api\MemberPortal\EventController as MemberPortalEventController;
use App\Http\Controllers\Api\MemberPortal\GivingController as MemberPortalGivingController;
use App\Http\Controllers\Api\MemberPortal\HelpController as MemberPortalHelpController;
use App\Http\Controllers\Api\MemberPortal\LeaderController as MemberPortalLeaderController;
use App\Http\Controllers\Api\MemberPortal\ProfileController as MemberPortalProfileController;
use App\Http\Controllers\Api\MemberPortal\RequestController as MemberPortalRequestController;
use App\Http\Controllers\Api\MemberPortal\ServiceController as MemberPortalServiceController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login'])
    ->middleware('throttle:api-login')
    ->name('api.login');

Route::middleware(['auth:sanctum', 'church.api'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('api.logout');
    Route::get('/user', [AuthController::class, 'user'])->name('api.user');

    Route::get('/members', [MemberController::class, 'index'])->name('api.members.index');
    Route::get('/members/{id}', [MemberController::class, 'show'])
        ->whereNumber('id')
        ->name('api.members.show');

    Route::middleware('church.member')->prefix('member')->name('api.member.')->group(function () {
        Route::get('/dashboard', [MemberPortalDashboardController::class, 'index'])->name('dashboard');

        Route::get('/profile', [MemberPortalProfileController::class, 'show'])->name('profile.show');
        Route::put('/profile', [MemberPortalProfileController::class, 'update'])->name('profile.update');
        Route::post('/profile', [MemberPortalProfileController::class, 'update'])->name('profile.update.post');
        Route::put('/profile/password', [MemberPortalProfileController::class, 'updatePassword'])->name('profile.password');

        Route::get('/announcements', [MemberPortalAnnouncementController::class, 'index'])->name('announcements.index');
        Route::get('/announcements/{id}', [MemberPortalAnnouncementController::class, 'show'])
            ->whereNumber('id')
            ->name('announcements.show');

        Route::get('/leaders', [MemberPortalLeaderController::class, 'index'])->name('leaders.index');
        Route::get('/departments', [MemberPortalDepartmentController::class, 'index'])->name('departments.index');
        Route::get('/events', [MemberPortalEventController::class, 'index'])->name('events.index');
        Route::get('/attendance', [MemberPortalAttendanceController::class, 'index'])->name('attendance.index');
        Route::post('/attendance/scan', [MemberPortalAttendanceController::class, 'scan'])->name('attendance.scan');
        Route::get('/giving', [MemberPortalGivingController::class, 'index'])->name('giving.index');
        Route::get('/help/contact', [MemberPortalHelpController::class, 'contact'])->name('help.contact');
        Route::post('/help', [MemberPortalHelpController::class, 'store'])->name('help.store');
        Route::get('/diary', [MemberPortalDiaryController::class, 'index'])->name('diary.index');
        Route::post('/diary', [MemberPortalDiaryController::class, 'store'])->name('diary.store');
        Route::put('/diary/{id}', [MemberPortalDiaryController::class, 'update'])
            ->whereNumber('id')
            ->name('diary.update');
        Route::delete('/diary/{id}', [MemberPortalDiaryController::class, 'destroy'])
            ->whereNumber('id')
            ->name('diary.destroy');
        Route::get('/services', [MemberPortalServiceController::class, 'index'])->name('services.index');
        Route::get('/services/{id}', [MemberPortalServiceController::class, 'show'])
            ->whereNumber('id')
            ->name('services.show');

        Route::get('/requests', [MemberPortalRequestController::class, 'index'])->name('requests.index');
        Route::get('/requests/meta', [MemberPortalRequestController::class, 'meta'])->name('requests.meta');
        Route::post('/requests', [MemberPortalRequestController::class, 'store'])->name('requests.store');
        Route::get('/requests/{id}', [MemberPortalRequestController::class, 'show'])
            ->whereNumber('id')
            ->name('requests.show');
        Route::get('/requests/{id}/certificate', [MemberPortalRequestController::class, 'certificate'])
            ->whereNumber('id')
            ->name('requests.certificate');
    });

    Route::middleware('church.pastor')->prefix('pastor')->name('api.pastor.')->group(function () {
        Route::get('/dashboard', [PastorDashboardController::class, 'index'])->name('dashboard');

        Route::get('/members/form', [PastorFormOptionsController::class, 'member'])->name('members.form');
        Route::get('/members/check-envelope', [PastorMemberController::class, 'checkEnvelope'])->name('members.check-envelope');
        Route::get('/members', [PastorMemberController::class, 'index'])->name('members.index');
        Route::post('/members', [PastorMemberController::class, 'store'])->name('members.store');
        Route::get('/members/{id}', [PastorMemberController::class, 'show'])->whereNumber('id')->name('members.show');
        Route::post('/members/{id}', [PastorMemberController::class, 'update'])->whereNumber('id')->name('members.update');
        Route::delete('/members/{id}', [PastorMemberController::class, 'destroy'])->whereNumber('id')->name('members.destroy');
        Route::get('/children', [PastorMemberController::class, 'children'])->name('children.index');
        Route::post('/children', [PastorMemberController::class, 'storeChild'])->name('children.store');
        Route::get('/children/{id}', [PastorMemberController::class, 'showChild'])->whereNumber('id')->name('children.show');

        Route::get('/leaders/form', [PastorFormOptionsController::class, 'leader'])->name('leaders.form');
        Route::get('/leaders', [PastorLeaderController::class, 'index'])->name('leaders.index');
        Route::post('/leaders', [PastorLeaderController::class, 'store'])->name('leaders.store');
        Route::get('/leaders/{id}', [PastorLeaderController::class, 'show'])->whereNumber('id')->name('leaders.show');
        Route::post('/leaders/{id}/deactivate', [PastorLeaderController::class, 'deactivate'])->whereNumber('id')->name('leaders.deactivate');

        Route::get('/departments/form', [PastorFormOptionsController::class, 'department'])->name('departments.form');
        Route::get('/departments', [PastorDepartmentController::class, 'index'])->name('departments.index');
        Route::post('/departments', [PastorDepartmentController::class, 'store'])->name('departments.store');
        Route::get('/departments/{id}', [PastorDepartmentController::class, 'show'])->whereNumber('id')->name('departments.show');
        Route::get('/departments/{id}/available-members', [PastorDepartmentController::class, 'availableMembers'])->whereNumber('id')->name('departments.available-members');
        Route::post('/departments/{id}/members', [PastorDepartmentController::class, 'attachMembers'])->whereNumber('id')->name('departments.members.attach');
        Route::post('/departments/{id}/assign-leader', [PastorDepartmentController::class, 'assignLeader'])->whereNumber('id')->name('departments.assign-leader');
        Route::delete('/departments/{id}/members/{memberId}', [PastorDepartmentController::class, 'removeMember'])->whereNumber('id')->whereNumber('memberId')->name('departments.members.remove');

        Route::get('/announcements', [PastorAnnouncementController::class, 'index'])->name('announcements.index');
        Route::post('/announcements', [PastorAnnouncementController::class, 'store'])->name('announcements.store');
        Route::get('/announcements/{id}', [PastorAnnouncementController::class, 'show'])->whereNumber('id')->name('announcements.show');

        Route::get('/services/form', [PastorFormOptionsController::class, 'service'])->name('services.form');
        Route::get('/services/people', [PastorServiceController::class, 'people'])->name('services.people');
        Route::get('/services', [PastorServiceController::class, 'index'])->name('services.index');
        Route::post('/services', [PastorServiceController::class, 'store'])->name('services.store');
        Route::get('/services/{id}', [PastorServiceController::class, 'show'])->whereNumber('id')->name('services.show');
        Route::post('/services/{id}', [PastorServiceController::class, 'update'])->whereNumber('id')->name('services.update');

        Route::get('/events/form', [PastorFormOptionsController::class, 'event'])->name('events.form');
        Route::get('/events', [PastorEventController::class, 'index'])->name('events.index');
        Route::post('/events', [PastorEventController::class, 'store'])->name('events.store');
        Route::get('/events/{id}', [PastorEventController::class, 'show'])->whereNumber('id')->name('events.show');
        Route::post('/events/{id}', [PastorEventController::class, 'update'])->whereNumber('id')->name('events.update');
        Route::delete('/events/{id}', [PastorEventController::class, 'destroy'])->whereNumber('id')->name('events.destroy');

        Route::get('/celebrations/form', [PastorFormOptionsController::class, 'celebration'])->name('celebrations.form');
        Route::get('/celebrations', [PastorCelebrationController::class, 'index'])->name('celebrations.index');
        Route::post('/celebrations', [PastorCelebrationController::class, 'store'])->name('celebrations.store');
        Route::get('/celebrations/{id}', [PastorCelebrationController::class, 'show'])->whereNumber('id')->name('celebrations.show');

        Route::get('/attendance/overview', [PastorAttendanceController::class, 'overview'])->name('attendance.overview');
        Route::get('/attendance/sessions', [PastorAttendanceController::class, 'sessions'])->name('attendance.sessions');
        Route::get('/attendance/roster', [PastorAttendanceController::class, 'roster'])->name('attendance.roster');
        Route::get('/attendance', [PastorAttendanceController::class, 'index'])->name('attendance.index');
        Route::post('/attendance', [PastorAttendanceController::class, 'store'])->name('attendance.store');

        Route::get('/bereavements', [PastorBereavementController::class, 'index'])->name('bereavements.index');
        Route::post('/bereavements', [PastorBereavementController::class, 'store'])->name('bereavements.store');
        Route::get('/bereavements/{id}', [PastorBereavementController::class, 'show'])->whereNumber('id')->name('bereavements.show');
        Route::get('/bereavements/{id}/contributions', [PastorBereavementController::class, 'contributions'])->whereNumber('id')->name('bereavements.contributions');
        Route::post('/bereavements/{id}/contributions', [PastorBereavementController::class, 'recordContribution'])->whereNumber('id')->name('bereavements.contributions.store');
        Route::post('/bereavements/{id}/non-contributor', [PastorBereavementController::class, 'markNonContributor'])->whereNumber('id')->name('bereavements.non-contributor');

        Route::get('/member-requests', [PastorMemberRequestController::class, 'index'])->name('member-requests.index');
        Route::get('/member-requests/{id}', [PastorMemberRequestController::class, 'show'])->whereNumber('id')->name('member-requests.show');
        Route::post('/member-requests/{id}/respond', [PastorMemberRequestController::class, 'respond'])->whereNumber('id')->name('member-requests.respond');

        Route::get('/registrations', [PastorRegistrationController::class, 'index'])->name('registrations.index');
        Route::get('/registrations/{id}', [PastorRegistrationController::class, 'show'])->whereNumber('id')->name('registrations.show');
        Route::post('/registrations/{id}/approve', [PastorRegistrationController::class, 'approve'])->whereNumber('id')->name('registrations.approve');
        Route::post('/registrations/{id}/reject', [PastorRegistrationController::class, 'reject'])->whereNumber('id')->name('registrations.reject');

        Route::get('/branches', [PastorBranchController::class, 'index'])->name('branches.index');
        Route::post('/branches', [PastorBranchController::class, 'store'])->name('branches.store');
        Route::get('/branches/{id}', [PastorBranchController::class, 'show'])->whereNumber('id')->name('branches.show');
        Route::post('/branches/{id}', [PastorBranchController::class, 'update'])->whereNumber('id')->name('branches.update');

        Route::get('/finance', [PastorFinanceController::class, 'summary'])->name('finance.summary');
        Route::post('/finance/tithes', [PastorFinanceController::class, 'storeTithe'])->name('finance.tithes.store');
        Route::post('/finance/offerings', [PastorFinanceController::class, 'storeOffering'])->name('finance.offerings.store');
        Route::post('/finance/expenses', [PastorFinanceController::class, 'storeExpense'])->name('finance.expenses.store');
        Route::post('/finance/pledges', [PastorFinanceController::class, 'storePledge'])->name('finance.pledges.store');
        Route::post('/finance/budgets', [PastorFinanceController::class, 'storeBudget'])->name('finance.budgets.store');
        Route::get('/finance/approvals', [PastorFinanceController::class, 'approvals'])->name('finance.approvals');
        Route::post('/finance/approvals/approve', [PastorFinanceController::class, 'approve'])->name('finance.approvals.approve');
        Route::post('/finance/approvals/reject', [PastorFinanceController::class, 'reject'])->name('finance.approvals.reject');
    });
});
