<?php
/**
 * Created by PhpStorm.
 * User: TELstatic
 * Date: 2019-05-21
 * Time: 9:13
 */

namespace Arabeila\Tools\Supports;

/**
 * 链接类辅助函数
 * @desc
 */
class Url
{
    /**
     * 去除分隔符
     * @param $url
     * Date: 2020/3/20
     * @return string
     */
    public static function removeDirectorySeparator($url)
    {
        return rtrim($url, '/');
    }

    /**
     * 去除 http 协议
     * @param $url
     * Date: 2020/3/20
     * @return string
     */
    public static function removeProtocol($url)
    {
        return str_replace(['http:', 'https:'], '', $url);
    }

    /**
     * 生成 https 链接
     * @param $url
     * Date: 2020/3/20
     * @return string
     */
    public static function parseHttps($url)
    {
        return 'https:'.self::removeProtocol($url);
    }

    /**
     * 生成 http 链接
     * @param $url
     * Date: 2020/3/20
     * @return string
     */
    public static function parseHttp($url)
    {
        return 'http:'.self::removeProtocol($url);
    }
}