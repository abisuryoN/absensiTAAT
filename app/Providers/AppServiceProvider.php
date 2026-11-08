<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use App\Models\Student;
use App\Models\AttendanceGate;
use App\Policies\StudentParentPolicy;
use App\Observers\AttendanceGateObserver;

class AppServiceProvider extends ServiceProvider
{
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
        Paginator::useBootstrapFive();

        // Parent → Student authorization
        Gate::policy(Student::class, StudentParentPolicy::class);

        // Register Attendance Observer
        AttendanceGate::observe(AttendanceGateObserver::class);

        // Listen to login event to show welcome SweetAlert
        \Illuminate\Support\Facades\Event::listen(
            \Illuminate\Auth\Events\Login::class,
            function ($event) {
                session(['show_welcome_notification' => true]);
            }
        );
    }
}
