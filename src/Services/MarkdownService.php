<?php
/**
 * Created by PhpStorm.
 * User: TELstatic
 * Date: 2019/8/23
 * Time: 14:23
 */

namespace Arabeila\Tools\Services;


class MarkdownService
{
    public function index()
    {
        $service = new PostmanService();

        $json = $service->index();

        $fileName = date('Y-m-d H:i:s').'_Api.md';

        echo '# '.$json['info']['name'];

        echo PHP_EOL;
        echo PHP_EOL;

        echo '> ';

        echo PHP_EOL;
        echo PHP_EOL;

        echo "## 目录";
        echo PHP_EOL;
        echo PHP_EOL;

        foreach ($json['item'] as $item) {
            echo '* '.'['.$item['name'].'](/Api#'.$item['name'].')';
            echo PHP_EOL;

            foreach ($item['item'] as $val) {
                echo '  * '.'['.$val['name'].'](/Api#'.$val['name'].')';
                echo PHP_EOL;
            }
        }

        foreach ($json['item'] as $item) {
            echo '### '.$item['name'];

            echo PHP_EOL;
            echo PHP_EOL;

            foreach ($item['item'] as $val) {
                echo '#### '.$val['name'];
                echo PHP_EOL;
                echo PHP_EOL;

                echo '* 地址 '.'`'.$val['request']['url']['raw'].'`';
                echo PHP_EOL;

                echo '* 请求方式 '.'`'.$val['request']['method'].'`';
                echo PHP_EOL;

                echo '* 请求参数';
                echo PHP_EOL;

                if ($val['request']['method'] != 'GET') {
                    if ($val['request']['body']['formdata']) {
                        echo '|   名称    |  类型  | 必填 | 默认值 |  备注  |';
                        echo PHP_EOL;

                        echo '| :-------: | :----: | :--: | :----: | :----: |';
                        echo PHP_EOL;

                        foreach ($val['request']['body']['formdata'] as $param) {
                            echo "|   {$param['key']}    |  {$param['type']}  | {$param['require']} | {$param['value']} |  {$param['description']}  |";
                            echo PHP_EOL;
                        }
                    } else {
                        echo "无";
                        echo PHP_EOL;

                    }
                } else {
                    if (isset($val['request']['url']['query'])) {
                        echo '|   名称    |  类型  | 必填 | 默认值 |  备注  |';
                        echo PHP_EOL;

                        echo '| :-------: | :----: | :--: | :----: | :----: |';
                        echo PHP_EOL;

                        foreach ($val['request']['url']['query'] as $param) {
                            echo "|   {$param['key']}    |  {$param['type']}  | {$param['require']} | {$param['value']} |  {$param['description']}  |";
                            echo PHP_EOL;
                        }
                    } else {
                        echo "无";
                        echo PHP_EOL;
                    }
                }
            }
        }

        header("Content-type:  application/octet-stream ");
        header("Accept-Ranges:  bytes ");
        header("Content-Disposition:  attachment;  filename= {$fileName}");
    }
}