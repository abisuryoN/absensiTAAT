<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Schedule;
use App\Policies\StudentParentPolicy;
use App\Policies\StudentPolicy;
use App\Policies\TeacherPolicy;
use App\Policies\SchedulePolicy;

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

        // Register authorization policies
        Gate::policy(Student::class, StudentPolicy::class);
        Gate::policy(Teacher::class, TeacherPolicy::class);
        Gate::policy(Schedule::class, SchedulePolicy::class);
        // Parent → Student authorization
        Gate::policy(Student::class, StudentParentPolicy::class);

        // Listen to login event to show welcome SweetAlert
        \Illuminate\Support\Facades\Event::listen(
            \Illuminate\Auth\Events\Login::class,
            function ($event) {
                session(['show_welcome_notification' => true]);
            }
        );
    }
}
