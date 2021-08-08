<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
// use LaravelLocalization;

use Illuminate\Support\Facades\Blade;
// use App\Models\Settings\Translation;
// use App\Models\Settings\Setting;
use Illuminate\Support\Facades\App;
use App\Libraries\Modules\ModulesLoadingHandler;
use App\Libraries\ServiceContainer\SingletoneStorage;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $app = $this->app;
        ModulesLoadingHandler::loadModulesProviders($app);

        $app->singleton(SingletoneStorage::class, function () {
            return new SingletoneStorage();
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
    }
}
