<?php
/**
 * Created by PhpStorm.
 * User: TELstatic
 * Date: 2019-05-17
 * Time: 8:53
 */

return [
    'path'      => [
        'view' => 'resources\\views\\',
        'vue'  => 'resources\\assets\\js\\',
    ],
//    PermissionService 黑名单
    'blackList' => [
        'Barryvdh\Debugbar\Controllers\CacheController',
        'Barryvdh\Debugbar\Controllers\OpenHandlerController',
        'Barryvdh\Debugbar\Controllers\AssetController',
        'App\Http\Controllers\Auth\LoginController',
        'App\Http\Controllers\Admin\LoginController',
        'App\Http\Controllers\User\LoginController',
        'App\Http\Controllers\User\IndexController',
        'App\Http\Controllers\Store\LoginController',
        'App\Http\Controllers\Admin\DemoController',
        'App\Http\Controllers\Api\LoginController',
        'App\Http\Controllers\Auth\RegisterController',
        'App\Http\Controllers\Auth\ForgotPasswordController',
        'App\Http\Controllers\Auth\ResetPasswordController',
        'App\Http\Controllers\User\ProductController',
        'App\Http\Controllers\HomeController',
    ],
];