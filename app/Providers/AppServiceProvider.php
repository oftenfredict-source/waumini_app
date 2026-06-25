<?php

namespace App\Providers;

use App\Models\AttendanceRecord;
use App\Models\Announcement;
use App\Models\BereavementEvent;
use App\Models\Church;
use App\Models\ChurchAsset;
use App\Models\ChurchBranch;
use App\Models\Celebration;
use App\Models\ChurchService;
use App\Models\Department;
use App\Models\Leader;
use App\Models\Member;
use App\Models\MemberDependant;
use App\Models\MemberRegistrationApplication;
use App\Models\MemberRequest;
use App\Models\Payment;
use App\Models\Offering;
use App\Models\Pledge;
use App\Models\PromiseGuest;
use App\Models\Budget;
use App\Models\Expense;
use App\Models\SpecialEvent;
use App\Models\Tithe;
use App\Models\SubscriptionPackage;
use App\Models\SupportTicket;
use App\Models\SystemSetting;
use App\Models\User;
use App\Policies\BereavementEventPolicy;
use App\Policies\ChurchAssetPolicy;
use App\Policies\ChurchBranchPolicy;
use App\Policies\ChurchPolicy;
use App\Policies\AttendancePolicy;
use App\Policies\AnnouncementPolicy;
use App\Policies\CelebrationPolicy;
use App\Policies\ChurchServicePolicy;
use App\Policies\DepartmentPolicy;
use App\Policies\LeaderPolicy;
use App\Policies\MemberPolicy;
use App\Policies\MemberRegistrationApplicationPolicy;
use App\Policies\MemberRequestPolicy;
use App\Policies\MemberDependantPolicy;
use App\Policies\PaymentPolicy;
use App\Policies\OfferingPolicy;
use App\Policies\BudgetPolicy;
use App\Policies\ExpensePolicy;
use App\Policies\PledgePolicy;
use App\Policies\PromiseGuestPolicy;
use App\Policies\SpecialEventPolicy;
use App\Policies\TithePolicy;
use App\Policies\SubscriptionPackagePolicy;
use App\Policies\SupportTicketPolicy;
use App\Policies\SystemSettingPolicy;
use App\Policies\UserPolicy;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use App\Services\Church\HeaderNotificationService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    protected $policies = [
        Church::class => ChurchPolicy::class,
    ];
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (! $this->app->runningInConsole() && $this->app->bound('request')) {
            $request = $this->app->make('request');
            URL::forceRootUrl($request->getSchemeAndHttpHost().$request->getBaseUrl());
        }

        Gate::policy(Church::class, ChurchPolicy::class);
        Gate::policy(Member::class, MemberPolicy::class);
        Gate::policy(MemberRegistrationApplication::class, MemberRegistrationApplicationPolicy::class);
        Gate::policy(MemberRequest::class, MemberRequestPolicy::class);
        Gate::policy(MemberDependant::class, MemberDependantPolicy::class);
        Gate::policy(AttendanceRecord::class, AttendancePolicy::class);
        Gate::policy(Leader::class, LeaderPolicy::class);
        Gate::policy(Department::class, DepartmentPolicy::class);
        Gate::policy(ChurchAsset::class, ChurchAssetPolicy::class);
        Gate::policy(ChurchBranch::class, ChurchBranchPolicy::class);
        Gate::policy(ChurchService::class, ChurchServicePolicy::class);
        Gate::policy(Announcement::class, AnnouncementPolicy::class);
        Gate::policy(SpecialEvent::class, SpecialEventPolicy::class);
        Gate::policy(PromiseGuest::class, PromiseGuestPolicy::class);
        Gate::policy(Celebration::class, CelebrationPolicy::class);
        Gate::policy(Tithe::class, TithePolicy::class);
        Gate::policy(Offering::class, OfferingPolicy::class);
        Gate::policy(Pledge::class, PledgePolicy::class);
        Gate::policy(Budget::class, BudgetPolicy::class);
        Gate::policy(Expense::class, ExpensePolicy::class);
        Gate::policy(BereavementEvent::class, BereavementEventPolicy::class);
        Gate::policy(SubscriptionPackage::class, SubscriptionPackagePolicy::class);
        Gate::policy(Payment::class, PaymentPolicy::class);
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(SupportTicket::class, SupportTicketPolicy::class);
        Gate::policy(SystemSetting::class, SystemSettingPolicy::class);
        Paginator::useBootstrap();

        View::composer('layouts.church', function ($view) {
            $user = auth()->user();

            if (! $user) {
                return;
            }

            $view->with('headerNotifications', app(HeaderNotificationService::class)->forUser($user));
        });

        Route::bind('member', function (string $value) {
            $churchId = auth()->user()?->church_id;

            abort_unless($churchId, 404);

            return Member::forChurch($churchId)
                ->whereKey($value)
                ->firstOrFail();
        });

        Route::bind('leader', function (string $value) {
            $churchId = auth()->user()?->church_id;

            abort_unless($churchId, 404);

            return Leader::forChurch($churchId)
                ->whereKey($value)
                ->firstOrFail();
        });

        Route::bind('department', function (string $value) {
            $churchId = auth()->user()?->church_id;

            abort_unless($churchId, 404);

            return Department::forChurch($churchId)
                ->whereKey($value)
                ->firstOrFail();
        });

        Route::bind('branch', function (string $value) {
            $churchId = auth()->user()?->church_id;

            abort_unless($churchId, 404);

            return ChurchBranch::forChurch($churchId)
                ->whereKey($value)
                ->firstOrFail();
        });

        Route::bind('announcement', function (string $value) {
            $churchId = auth()->user()?->church_id;

            abort_unless($churchId, 404);

            return Announcement::forChurch($churchId)
                ->whereKey($value)
                ->firstOrFail();
        });

        Route::bind('registration', function (string $value) {
            $churchId = auth()->user()?->church_id;

            abort_unless($churchId, 404);

            return MemberRegistrationApplication::forChurch($churchId)
                ->whereKey($value)
                ->firstOrFail();
        });

        Route::bind('member_request', function (string $value) {
            $churchId = auth()->user()?->church_id;

            abort_unless($churchId, 404);

            return MemberRequest::forChurch($churchId)
                ->whereKey($value)
                ->firstOrFail();
        });

        Route::bind('dependant', function (string $value) {
            $churchId = auth()->user()?->church_id;

            abort_unless($churchId, 404);

            return MemberDependant::forChurch($churchId)
                ->whereKey($value)
                ->firstOrFail();
        });

        Route::bind('service', function (string $value) {
            $churchId = auth()->user()?->church_id;

            abort_unless($churchId, 404);

            return ChurchService::forChurch($churchId)
                ->whereKey($value)
                ->firstOrFail();
        });

        Route::bind('special_event', function (string $value) {
            $churchId = auth()->user()?->church_id;

            abort_unless($churchId, 404);

            return SpecialEvent::forChurch($churchId)
                ->whereKey($value)
                ->firstOrFail();
        });

        Route::bind('bereavement', function (string $value) {
            $churchId = auth()->user()?->church_id;

            abort_unless($churchId, 404);

            return BereavementEvent::forChurch($churchId)
                ->whereKey($value)
                ->firstOrFail();
        });

        Route::bind('tithe', function (string $value) {
            $churchId = auth()->user()?->church_id;

            abort_unless($churchId, 404);

            return Tithe::forChurch($churchId)
                ->whereKey($value)
                ->firstOrFail();
        });

        Route::bind('offering', function (string $value) {
            $churchId = auth()->user()?->church_id;

            abort_unless($churchId, 404);

            return Offering::forChurch($churchId)
                ->whereKey($value)
                ->firstOrFail();
        });

        Route::bind('pledge', function (string $value) {
            $churchId = auth()->user()?->church_id;

            abort_unless($churchId, 404);

            return Pledge::forChurch($churchId)
                ->whereKey($value)
                ->firstOrFail();
        });

        Route::bind('budget', function (string $value) {
            $churchId = auth()->user()?->church_id;

            abort_unless($churchId, 404);

            return Budget::forChurch($churchId)
                ->whereKey($value)
                ->firstOrFail();
        });

        Route::bind('expense', function (string $value) {
            $churchId = auth()->user()?->church_id;

            abort_unless($churchId, 404);

            return Expense::forChurch($churchId)
                ->whereKey($value)
                ->firstOrFail();
        });

        Route::bind('asset', function (string $value) {
            $churchId = auth()->user()?->church_id;

            abort_unless($churchId, 404);

            return ChurchAsset::forChurch($churchId)
                ->whereKey($value)
                ->firstOrFail();
        });
    }
}
