<?php

namespace App\Modules\Group\Providers;

use Illuminate\Support\ServiceProvider;

class GroupServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__ . '/../Routes/api.php');
    }

    public function register()
    {
        //
    }
}
