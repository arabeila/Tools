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
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

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

        if (!getenv('NACOS_HOST')) {
            throw new \Exception(str_replace('var', 'NACOS_HOST', $message));
        }

        if (getenv('APP_ENV')) {
            throw new \Exception(str_replace('var', 'APP_ENV', $message));
        }

        if (getenv('NACOS_DATAID')) {
            throw new \Exception(str_replace('var', 'NACOS_DATAID', $message));
        }

        if (getenv('NACOS_GROUPID')) {
            throw new \Exception(str_replace('var', 'NACOS_GROUPID', $message));
        }

        if (getenv('NACOS_NAMESPACEID')) {
            throw new \Exception(str_replace('var', 'NACOS_NAMESPACEID', $message));
        }

        //获取 快照
        (new \Dotenv\Loader([], new \Dotenv\Environment\DotenvFactory(), true))->loadDirect(
            \alibaba\nacos\Nacos::init(getenv('NACOS_HOST'),
                getenv('APP_ENV'),
                getenv('NACOS_DATAID'),
                getenv('NACOS_GROUPID'),
                getenv('NACOS_NAMESPACEID'))->runOnce()
        );

        //发布 配置
        file_put_contents('.env', \alibaba\nacos\failover\LocalConfigInfoProcessor::getSnapshot(getenv('APP_ENV'),
            getenv('NACOS_DATAID'), getenv('NACOS_GROUPID'), getenv('NACOS_NAMESPACEID')));
    }
}