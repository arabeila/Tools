<?php

namespace Arabeila\Tools;

use Illuminate\Console\Command;

class AppRelease extends Command
{
    protected $signature = 'tool:release {--l|level=patch} {--g|guard=admin}';

    protected $description = 'release app version 
        major 
        minor
        patch                                                        
    ';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {

        xenv($data);

        $this->info(env('APP_NAME').' 版本发布助手 '.$guard);
        $this->info('当前发布版本为 '.$version);

        if (env('APP_ENV') !== 'local') {
            exit('env环境修改成功!'.PHP_EOL);
        }

        $this->info('文件编译中!');

        exec('npm run prod-'.strtolower($guard));

        $directory = 'js/'.strtolower($guard).'/'.$version;

        $file_online = $directory.'/app.js';

        $file_local = 'public/js/'.strtolower($guard).'.js';

        $this->info('文件上传中!');

        Storage::disk('oss')->put($file_online, file_get_contents($file_local));

        if (Storage::disk('oss')->exists($file_online)) {
            $this->info('文件上传成功!');
        } else {
            $this->error('文件上传失败!');
        }
    }

    /**
     * 更新 发布版本
     */
    public function changeVersion()
    {
        $guard = strtoupper($this->option('guard'));

        $js_ver = str_replace('v', '', env($guard.'_JS', 'v1.0.0'));

        list($major, $minor, $patch) = explode('.', $js_ver);

        switch (strtoupper($this->option('level'))) {
            default:
            case 'C':
            case 'PATCH':
                $patch += 1;
                break;
            case 'B':
            case 'MINOR':
                $patch = 0;
                $minor += 1;
                break;
            case 'A':
            case 'MAJOR':
                $patch = 0;
                $minor = 0;
                $major += 1;
                break;
        }

        $version = 'v'.$major.'.'.$minor.'.'.$patch;

        $data = [
            $guard.'_JS' => $version,
        ];



    }

    /**
     * 编译文件
     */
    public function build()
    {

    }

    /**
     * 上传文件
     */
    public function upload()
    {

    }

    /**
     * 获取线上文件路径
     */
    public function getOnlineFile()
    {

    }

    /**
     * 获取本地文件路径
     */
    public function getLocalFile()
    {

    }


}