<?php
/**
 * Created by PhpStorm.
 * User: TELstatic
 * Date: 2019-05-17
 * Time: 8:53
 */

return [
    'path'       => [
        'view' => 'resources\\views\\',
        'vue'  => 'resources\\assets\\js\\',
    ],
    //    PermissionService 黑名单
    'blackList'  => [
        'Barryvdh\Debugbar\Controllers\CacheController',
        'Barryvdh\Debugbar\Controllers\OpenHandlerController',
        'Barryvdh\Debugbar\Controllers\AssetController',
    ],
    'guards'     => [
        'auth:api',
    ],
    'category'   => [
        'refresh' => 1, // 分类清除缓存
    ],
    'access_key' => env('APP_AK'),
    'secret_key' => env('APP_SK'),
];