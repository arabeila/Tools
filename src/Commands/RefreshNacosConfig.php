<?php

namespace Arabeila\Tools\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

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

        //清空 配置缓存
        Artisan::call('config:clear');

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