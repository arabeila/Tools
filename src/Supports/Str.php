<?php
/**
 * Created by PhpStorm.
 * User: TELstatic
 * Date: 2019-05-21
 * Time: 9:13
 */

namespace Arabeila\Tools\Supports;
use Illuminate\Support\Str as Sstr;

/**
 * 字符串类辅助函数
 * @desc
 */
class Str
{
    public static function start_with($haystack, $needles)
    {
        return Sstr::startsWith($haystack, $needles);
    }
}