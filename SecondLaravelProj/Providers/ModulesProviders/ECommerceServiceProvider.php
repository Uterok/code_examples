<?php

namespace Modules\ECommerce;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use App\Libraries\Modules\LoadModuleConfiguration;
use Dotenv\Dotenv;
use App\Libraries\Routes\RoutesHandler;
use App\Libraries\Modules\ModulesLoadingHandler;
use Illuminate\Database\Eloquent\Relations\Relation;
use Modules\ECommerce\Models\ECommerceProduct;
use Modules\ECommerce\Models\ECommerceProductOption;

class ECommerceServiceProvider extends ServiceProvider
{
    public function register()
    {
    }

    public function boot()
    {
        // overload .env files
        $dotenv = Dotenv::createImmutable(
            __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR,
            '.env'
        );
        $dotenv->load();

        // register config files
        (new LoadModuleConfiguration())->loadModuleConfigurationFiles(
            'e-commerce',
            __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'config'
        );

        // register routes
        $this->registerRoutes();

        $this->loadMigrationsFrom([
            __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'database'.DIRECTORY_SEPARATOR.'migrations'
        ]);

        $this->loadViewsFrom(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'views', 'e-commerce');

        ModulesLoadingHandler::loadModulesLivewireComponents('ECommerce');

        // register morphed classes
        Relation::morphMap([
            ECommerceProduct::POLYMORPH_CUSTOM_TYPE => ECommerceProduct::class,
            ECommerceProductOption::POLYMORPH_CUSTOM_TYPE => ECommerceProductOption::class,
        ]);
    }

    protected function registerRoutes()
    {
        Route::group(
            [
            'prefix' => RoutesHandler::getLaravelLocalizationPrefix(),
            'middleware' => config('routes.lang_middlewares')
            ],
            function () {
                Route::group($this->routeConfiguration('web'), function () {
                    $this->loadRoutesFrom(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'routes'.DIRECTORY_SEPARATOR.'web.php');
                });
            }
        );

        Route::group($this->routeConfiguration('api'), function () {
            $this->loadRoutesFrom(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'routes'.DIRECTORY_SEPARATOR.'api.php');
        });
    }

    protected function routeConfiguration($type)
    {
        $middleware_config = ($type = 'web') ? 'web_default_middleware' : 'api_default_middleware';

        return [
            'prefix' => config('e-commerce.module.route_prefix'),
            'middleware' => config('e-commerce.module.' . $middleware_config),
        ];
    }
}
