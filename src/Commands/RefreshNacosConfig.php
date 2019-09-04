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
            throw new \Exception('file .env not found');
        }

        //获取 快照
        (new \Dotenv\Loader([], new \Dotenv\Environment\DotenvFactory(), true))->loadDirect(
            \alibaba\nacos\Nacos::init(
                getenv("NACOS_HOST"),
                getenv("APP_ENV"),
                getenv("NACOS_DATAID"),
                getenv("NACOS_GROUPID"),
                getenv("NACOS_NAMESPACEID")
            )->runOnce()
        );

        //发布 配置
        file_put_contents('.env', \alibaba\nacos\failover\LocalConfigInfoProcessor::getSnapshot(getenv("APP_ENV"),
            getenv("NACOS_DATAID"), getenv("NACOS_GROUPID"), getenv("NACOS_NAMESPACEID")));
    }
}