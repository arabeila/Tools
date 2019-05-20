<?php
/**
 * Created by PhpStorm.
 * User: TELstatic
 * Date: 2019-05-17
 * Time: 8:51
 */

namespace Arabeila\Tools;

use Arabeila\Tools\Commands\CreateController;
use Arabeila\Tools\Commands\CreateModel;
use Arabeila\Tools\Commands\CreateRequest;
use Arabeila\Tools\Commands\CreateTemplate;
use Illuminate\Support\ServiceProvider;

class ToolsServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__.'/config/tools.php' => config_path('tools.php'),
        ]);

        $this->mergeConfigFrom(__DIR__.'/config/tools.php', 'tools');

        $this->loadViewsFrom(__DIR__.'/resources/stubs', 'tools');

        $this->publishes([
            __DIR__.'/Commands/stubs' => resource_path('views/stubs'),
        ]);

        if ($this->app->runningInConsole()) {
            $this->commands([
                CreateController::class,
                CreateRequest::class,
                CreateTemplate::class,
            ]);
        }
    }

    public function register()
    {

    }

    public function provides()
    {

    }
}