<?php

namespace Arabeila\Tools\Commands;

use Illuminate\Console\Command;

class RefreshNacosConfig extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nacos:refresh';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh config from Nacos';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if (!file_exists('.env')) {
            throw new \Exception('.env 文件未找到');
        }

        $message = '环境变量 var 未找到，尝试清除配置缓存？';

        if (!env('NACOS_HOST')) {
            throw new \Exception(str_replace('var', 'NACOS_HOST', $message));
        }

        if (env('APP_ENV')) {
            throw new \Exception(str_replace('var', 'APP_ENV', $message));
        }

        if (env('NACOS_DATAID')) {
            throw new \Exception(str_replace('var', 'NACOS_DATAID', $message));
        }

        if (env('NACOS_GROUPID')) {
            throw new \Exception(str_replace('var', 'NACOS_GROUPID', $message));
        }

        if (env('NACOS_NAMESPACEID')) {
            throw new \Exception(str_replace('var', 'NACOS_NAMESPACEID', $message));
        }

        //获取 快照

        \alibaba\nacos\Nacos::init(env('NACOS_HOST'),
            env('APP_ENV'),
            env('NACOS_DATAID'),
            env('NACOS_GROUPID'),
            env('NACOS_NAMESPACEID'))->runOnce();

        //发布 配置
        file_put_contents('.env', \alibaba\nacos\failover\LocalConfigInfoProcessor::getSnapshot(env('APP_ENV'),
            env('NACOS_DATAID'), env('NACOS_GROUPID'), env('NACOS_NAMESPACEID')));
    }
}