<?php

namespace Arabeila\Tools\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

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

        xenv($data);

        $this->info(config('app.name').' 版本发布助手 '.$guard);
        $this->info('当前发布版本为 '.$version);

        if (config('app.env') !== 'local') {
            exit('env环境修改成功!'.PHP_EOL);
        }

        $this->info('文件编译中!');

        exec('npm run prod-'.strtolower($guard));

        $directory = 'js/'.strtolower($guard).'/'.$version;

        $fileOnline = $directory.'/app.js';

        $fileLocal = 'public/js/'.strtolower($guard).'.js';

        $this->info('文件上传中!');

        Storage::disk('oss')->put($fileOnline, file_get_contents($fileLocal));

        if (Storage::disk('oss')->exists($fileOnline)) {
            $this->info('文件上传成功!');
            Storage::disk('oss')->put('js/'.strtolower($guard).'/current/version', $version);

            if (Storage::disk('oss')->symlink('js/'.strtolower($guard).'/current/app.js', $fileOnline)) {
                $this->info('软链接设置成功!');
            } else {
                $this->info('软链接设置失败!');
            }
        } else {
            $this->error('文件上传失败!');
        }
    }
}
