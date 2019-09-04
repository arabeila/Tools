<?php
/**
 * Created by PhpStorm.
 * User: satoshi
 * Date: 2019/8/27
 * Time: 11:47
 */
if (!function_exists('xenv')) {
    function xenv($data)
    {
        $envPath = base_path().DIRECTORY_SEPARATOR.'.env';
        $contentArray = collect(file($envPath, FILE_IGNORE_NEW_LINES));

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

        \File::put($envPath, $content);
    }
}