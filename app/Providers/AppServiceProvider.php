<?php

namespace App\Providers;

use App\Models\Classroom;
use App\Models\Media;
use App\Models\ParentStudent;
use App\Models\Student;
use App\Models\School;
use App\Models\User;
use App\Observers\MediaObserver;
use App\Policies\ClassroomPolicy;
use App\Policies\StudentPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
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

        // Explicit route model bindings — needed because the {school} domain parameter
        // interferes with implicit binding resolution on domain-group routes.
        Route::bind('classroom', fn($v) => Classroom::findOrFail($v));
        Route::bind('student',   fn($v) => Student::findOrFail($v));
        Route::bind('media',     fn($v) => Media::findOrFail($v));
        Route::bind('link',      fn($v) => ParentStudent::findOrFail($v));
        Route::bind('teacher',   fn($v) => User::findOrFail($v));
        Route::bind('parent',    fn($v) => User::findOrFail($v));

        // Share `school` with all views — set URL default for subdomain routes
        // so that route('teacher.dashboard') etc. works without needing the `school` param explicitly
        View::composer('*', function ($view) {
            $school = null;
            $request = request();

            if ($request) {
                $school = $request->attributes->get('school');
            }

            // Set URL default so ALL route() calls in views auto-use the current school
            if ($school && $school instanceof School) {
                URL::defaults(['school' => $school->slug]);
            }
        });
    }
}
