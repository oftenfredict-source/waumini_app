<?php

use App\Http\Controllers\Owner\ChurchController;
use App\Http\Controllers\Owner\ChurchSubscriptionController;
use App\Http\Controllers\Owner\DashboardController;
use App\Http\Controllers\Owner\LoginController;
use App\Http\Controllers\Owner\PaymentController;
use App\Http\Controllers\Owner\RevenueController;
use App\Http\Controllers\Owner\SettingController;
use App\Http\Controllers\Owner\SmsUsageController;
use App\Http\Controllers\Owner\SubscriptionController;
use App\Http\Controllers\Owner\SupportTicketController;
use App\Http\Controllers\Owner\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [LoginController::class, 'login'])->name('login.submit');
});

Route::middleware(['auth', 'owner'])->group(function () {
    Route::post('logout', [LoginController::class, 'logout'])->name('logout');
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('churches', ChurchController::class);
    Route::post('churches/{church}/impersonate', [ChurchController::class, 'impersonate'])->name('churches.impersonate');
    Route::post('churches/{church}/subscription', [ChurchSubscriptionController::class, 'store'])->name('churches.subscription.store');
    Route::post('churches/{church}/suspend', [ChurchController::class, 'suspend'])->name('churches.suspend');
    Route::post('churches/{church}/activate', [ChurchController::class, 'activate'])->name('churches.activate');
    Route::post('churches/{church}/regenerate-admin-password', [ChurchController::class, 'regenerateAdminPassword'])->name('churches.regenerate-admin-password');
    Route::post('churches/{church}/create-admin', [ChurchController::class, 'createAdmin'])->name('churches.create-admin');

    Route::get('subscriptions', [SubscriptionController::class, 'index'])->name('subscriptions.index');
    Route::get('subscriptions/packages', [SubscriptionController::class, 'packages'])->name('subscriptions.packages');

    Route::get('payments', [PaymentController::class, 'index'])->name('payments.index');

    Route::get('revenue', [RevenueController::class, 'index'])->name('revenue.index');

    Route::get('sms-usage', [SmsUsageController::class, 'index'])->name('sms-usage.index');
    Route::get('sms-usage/{church}', [SmsUsageController::class, 'show'])->name('sms-usage.show');

    Route::get('users', [UserController::class, 'index'])->name('users.index');

    Route::get('support', [SupportTicketController::class, 'index'])->name('support.index');

    Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
    Route::put('settings/general', [SettingController::class, 'updateGeneral'])->name('settings.general');
    Route::put('settings/billing', [SettingController::class, 'updateBilling'])->name('settings.billing');
    Route::put('settings/churches', [SettingController::class, 'updateChurches'])->name('settings.churches');
    Route::put('settings/notifications', [SettingController::class, 'updateNotifications'])->name('settings.notifications');
    Route::post('settings/sms-test', [SettingController::class, 'testSms'])->name('settings.sms-test');
    Route::put('settings/legal', [SettingController::class, 'updateLegal'])->name('settings.legal');
    Route::put('settings/system', [SettingController::class, 'updateSystem'])->name('settings.system');
    Route::post('settings/packages', [SettingController::class, 'storePackage'])->name('settings.packages.store');
    Route::put('settings/packages/{package}', [SettingController::class, 'updatePackage'])->name('settings.packages.update');
    Route::delete('settings/packages/{package}', [SettingController::class, 'destroyPackage'])->name('settings.packages.destroy');
});
