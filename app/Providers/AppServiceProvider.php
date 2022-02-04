<?php

namespace App\Providers;

use App\Models\LkImportFile;
use App\Observers\LkImportFileObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        LkImportFile::observe(LkImportFileObserver::class);
    }
}
