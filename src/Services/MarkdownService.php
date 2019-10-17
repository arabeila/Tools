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
    protected $request = 'request';

    protected $header = 'header';

    public function index($namespace = null)
    {
        $service = new PostmanService();

        $json = $service->index($namespace);

        $fileName = date('Y-m-d H:i:s').'_Api.md';

        ob_start();

        $this->echoDirectory($json);

        foreach ($json['item'] as $item) {
            echo '### '.$item['name'];

            echo PHP_EOL;
            echo PHP_EOL;

            foreach ($item['item'] as $val) {
                echo '#### '.$val['name'];
                echo PHP_EOL;
                echo PHP_EOL;

                echo '* 地址 '.'`'.$val[$this->request]['url']['raw'].'`';
                echo PHP_EOL;

                echo '* 请求方式 '.'`'.$val[$this->request]['method'].'`';
                echo PHP_EOL;

                $this->echoHeader($val);

                echo '* 请求参数';
                echo PHP_EOL;

                if ($val[$this->request]['method'] != 'GET') {

                    $this->echoFormData($val);
                } else {

                    $this->echoQuery($val);
                }
            }
        }

        header("Content-type:  application/octet-stream ");
        header("Accept-Ranges:  bytes ");
        header("Content-Disposition:  attachment;  filename= {$fileName}");
    }

    /**
     * 输出目录
     * @param $json
     */
    public function echoDirectory($json)
    {
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
    }

    /**
     * 输出请求头
     * @param $val
     */
    public function echoHeader($val)
    {
        if (!empty($val[$this->request][$this->header])) {
            echo '* 请求头';
            echo PHP_EOL;

            if ($val[$this->request][$this->header]) {
                $this->echoTableHead();

                foreach ($val[$this->request][$this->header] as $param) {
                    $this->echoTableContents($param);
                }
            }
        }
    }

    /**
     * 输出 formdata 内容
     * @param $val
     */
    public function echoFormData($val)
    {
        if ($val[$this->request]['body']['formdata']) {
            $this->echoTableHead();

            foreach ($val[$this->request]['body']['formdata'] as $param) {
                $this->echoTableContents($param);
            }
        } else {
            echo "无";
            echo PHP_EOL;
        }
    }

    /**
     * 输出 query 里的内容
     * @param $val
     */
    public function echoQuery($val)
    {
        if (isset($val[$this->request]['url']['query'])) {
            $this->echoTableHead();

            foreach ($val[$this->request]['url']['query'] as $param) {
                $this->echoTableContents($param);
            }
        } else {
            echo "无";
            echo PHP_EOL;
        }
    }

    /**
     * 输出表头
     */
    public function echoTableHead()
    {
        echo '|   名称    |  类型  | 必填 | 默认值 |  备注  |';
        echo PHP_EOL;

        echo '| :-------: | :----: | :--: | :----: | :----: |';
        echo PHP_EOL;
    }

    /**
     * 输出表格内容
     * @param $param
     */
    public function echoTableContents($param)
    {
        echo "|   {$param['key']}    |  {$param['type']}  | {$param['require']} | {$param['value']} |  {$param['description']}  |";
        echo PHP_EOL;
    }
}
