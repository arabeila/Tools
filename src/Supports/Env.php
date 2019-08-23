<?php
/**
 * Created by PhpStorm.
 * User: TELstatic
 * Date: 2019-05-22
 * Time: 17:37
 */

namespace Arabeila\Tools\Supports;

class Env
{
    /**
     * 修改 env 环境变量
     */
    public function change($data)
    {
        $envFile = base_path().DIRECTORY_SEPARATOR.'.env';
        $contentArray = collect(file($envFile, FILE_IGNORE_NEW_LINES));

        $contentArray->transform(function ($item) use ($data) {
            foreach ($data as $key => $value) {
                if (str_contains($item, $key)) {
                    return $key.'='.$value;
                }
            }

            return $item;
        });

        foreach ($data as $key => $value) {
            if (env($key) == null) {
                $contentArray[] = $key.'='.$value;
            }
        }

        $content = implode($contentArray->toArray(), PHP_EOL);

        \File::put($envFile, $content);
    }

    /**
     * 切换为 test 环境
     */
    public static function test()
    {
        //todo 交换 .env .env.test 文件内容 修改 .env APP_ENV = test
    }

    /**
     * 切换为 prod 环境
     */
    public static function prod()
    {
        //todo 交换 .env .env.prod 文件内容 修改 .env APP_ENV = production
    }

    /**
     * 切换为 dev 环境
     */
    public static function dev()
    {
        //todo 交换 .env .env.dev 文件内容 修改 .env APP_ENV = local

        //todo 增加命令 切换环境 切换之前 更新 APP_ENV 对应 配置文件
        // todo 示例: 当前 dev 环境 切换 test 环境 则 更新 .env.local 文件内容为当前 .env 内容
        // todo 示例: 当前 test 环境 切换 prod 环境 则 更新 .env.test 文件内容为当前 .env 内容
    }

}