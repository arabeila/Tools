<?php
/**
 * Created by PhpStorm.
 * User: TELstatic
 * Date: 2019-05-17
 * Time: 14:35
 */

namespace Arabeila\Tools\Supports;

/**
 * 辅助函数
 * @desc
 */
class Help
{
    /**
     * 通用返回函数
     * @desc
     */
    public static function reply($bool, $msg = '')
    {
        return response()->json([
            'code' => $bool ? 200 : 500,
            'msg'  => $msg,
        ]);
    }

}
