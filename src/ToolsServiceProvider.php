<?php
/**
 * Created by PhpStorm.
 * User: TELstatic
 * Date: 2019-05-17
 * Time: 8:51
 */

namespace Arabeila\Tools;

use Arabeila\Tools\Commands\AppRelease;
use Arabeila\Tools\Commands\CodeDetector;
use Arabeila\Tools\Commands\CodeStyleCheck;
use Arabeila\Tools\Commands\CodeStyleRepair;
use Arabeila\Tools\Commands\CreateController;
use Arabeila\Tools\Commands\CreateModel;
use Arabeila\Tools\Commands\CreatePermissions;
use Arabeila\Tools\Commands\CreateRequest;
use Arabeila\Tools\Commands\CreateTemplate;
use Arabeila\Tools\Commands\RefreshNacosConfig;
use Arabeila\Tools\Commands\ResetPassword;
use Illuminate\Support\ServiceProvider;

class ToolsServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__.'/config/tools.php' => config_path('tools.php'),
        ]);

        $this->mergeConfigFrom(__DIR__.'/config/tools.php', 'tools');

        $this->loadViewsFrom(__DIR__.'/stubs', 'tools');

        $this->publishes([
            __DIR__.'/Commands/stubs' => resource_path('stubs'),
        ]);

        if ($this->app->runningInConsole()) {
            $this->commands([
                CreateController::class,
                CreateRequest::class,
                CreateTemplate::class,
                AppRelease::class,
                CodeDetector::class,
                CodeStyleCheck::class,
                CodeStyleRepair::class,
                CreatePermissions::class,
                RefreshNacosConfig::class,
                ResetPassword::class,
            ]);
        }else{
            $this->commands([
                CreatePermissions::class,
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