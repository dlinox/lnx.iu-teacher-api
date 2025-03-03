<?php

namespace App\Modules\EnrollmentGrade\Providers;

use Illuminate\Support\ServiceProvider;

class EnrollmentGradeServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__ . '/../Routes/api.php');
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
    }

    public function register()
    {
        //
    }
}
