<?php

namespace App\Modules\Masters\DocumentType\Providers;

use Illuminate\Support\ServiceProvider;

class DocumentTypeServiceProvider extends ServiceProvider
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
