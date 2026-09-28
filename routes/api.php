<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MemberController;
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
});
