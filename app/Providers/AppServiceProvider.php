<?php

namespace App\Providers;

use App\Models\Classroom;
use App\Models\Media;
use App\Models\Student;
use App\Observers\MediaObserver;
use App\Policies\ClassroomPolicy;
use App\Policies\StudentPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::policy(Student::class, StudentPolicy::class);
        Gate::policy(Classroom::class, ClassroomPolicy::class);

        Media::observe(MediaObserver::class);
    }
}
