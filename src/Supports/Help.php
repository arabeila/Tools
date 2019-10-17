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

    /**
     * 生成随机编号
     * @desc
     * @param $flag string 标志位 默认 null 订单号 S 父订单 P 支付单 R 请求日志 F 反馈日志
     * @return string
     * @throws
     */
    public static function no($flag = null)
    {
        $prefix = config('app.prefix').strtoupper($flag).date('ymdHis');

        return $prefix.str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }


}
