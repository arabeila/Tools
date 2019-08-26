<?php
/**
 * Created by PhpStorm.
 * User: TELstatic
 * Date: 2019/8/23
 * Time: 14:23
 */

namespace Arabeila\Tools\Services;

use Illuminate\Support\Facades\Route;

class PostmanService
{
    public $list = [];

    public $permissions;

    public $blackList;

    public function __construct()
    {
        $this->blackList = config('tools.blackList');

        $this->getControllers();
    }

    /**
     * 获取所有路由中使用的控制器
     */
    public function getControllers()
    {
        $routes = Route::getRoutes();

        foreach ($routes as $route) {
            $arr = array();
            $actionName = $route->getActionName();

            preg_match('/\@/', $actionName, $end, PREG_OFFSET_CAPTURE);
            if (!isset($end[0])) {
                continue;
            }

            $controller = substr($actionName, 0, $end[0][1]);

            if (in_array($controller, $this->blackList)) {
                continue;
            }

            $arr['controller'] = $controller;

            $action = substr($actionName, $end[0][1] + 1);
            $arr['action'] = $action;

            $arr['method'] = method_exists($route, 'getMethods') ? $route->getMethods()[0] : $route->methods[0];
            $arr['uri'] = method_exists($route, 'getPath') ? $route->getPath() : $route->uri;
            array_push($this->list, $arr);
        }
    }

    /**
     * 返回控制器注释
     */
    public function index()
    {
        $data['info'] = [
            '_postman_id' => '1111',
            'name'        => 'Permission',
            'schema'      => 'https://schema.getpostman.com/json/collection/v2.1.0/collection.json',
        ];
        $data['item'] = [];

        $res = [];

        $num = 0;

        for ($i = 0; $i < count($this->list); $i++) {
            $class = $this->list[$i]['controller'];

            if (in_array($class, $res)) {
                continue;
            }

            array_push($res, $class);

            $className = explode('\\', str_replace('App\Http\Controllers\\', '', $class));

            $reflection = new \ReflectionClass($class);

            $actions = $this->getActions($class);


            switch (count($className)) {
                case 1:
                    //$data['/'][$className[0]] = $this->getActionDoc($actions, $reflection);
                    break;
                case 2:
                    $data['item'][$num++] = [
                        'name' => $this->getClassDoc($reflection),
                        'item' => $this->getActionDoc($actions, $reflection, $className[0]),
                    ];
                    break;
                case 3:
                    $data['item'][$num++] = [
                        'name' => $this->getClassDoc($reflection),
                        'item' => $this->getActionDoc($actions, $reflection, $className[0]),
                    ];
                    break;
            }
        }
        return $data;
    }

    /**
     * 获取所有权限
     */
    public function getPermissions($guard = "Admin")
    {
        if (!isset($this->permissions[$guard])) {
            $this->permissions[$guard] = [];
        }

        $permissions = array_unique($this->permissions[$guard]);
        sort($permissions);

        return $permissions;
    }

    /**
     * 解析类注释
     */
    public function getClassDoc($reflection)
    {
        $doc = $reflection->getDocComment();

        $arr = $this->formatClassDoc($doc);

        return $arr;
    }

    /**
     * 获取控制器中方法
     */
    public function getActions($controller)
    {
        $obj = [];
        foreach ($this->list as $value) {
            if ($value['controller'] == $controller) {
                $obj[] = $value['action'];
            }
        }
        return $obj;
    }

    /**
     * 获取方法Url
     */
    public function getRequestUrl($controller, $action)
    {
        foreach ($this->list as $one) {
            if ($one['controller'] == $controller && $one['action'] == $action) {
                return $one['uri'];
            }
        }
        return null;
    }

    /**
     * 获取请求类型
     */
    public function getRequestMethod($controller, $action)
    {
        foreach ($this->list as $one) {

            if ($one['controller'] == $controller && $one['action'] == $action) {
                return $one['method'];
            }
        }
        return null;
    }

    /**
     * 获取方法注释
     */
    public function getActionDoc($methodArray, \ReflectionClass $reflection, $nameSpace)
    {
        $arr = [];
        $methods = $reflection->getMethods();
        $i = 0;
        foreach ($methods as $key => $property) {
            if (!in_array($property->getName(), $methodArray))
                continue;
            $doc = $property->getDocComment();
            $arr[$i]['name'] = $this->formatClassDoc($doc);
            $arr[$i]['request'] = $this->formatDoc($doc, $reflection, $property, $nameSpace);
            $arr[$i]['response'] = [];

            $i++;
        }
        return $arr;
    }

    /**
     * 格式化注释代码
     */
    public function formatClassDoc($doc)
    {
        if (!$doc) {
            return "";
        }

        if (preg_match('#^/\*\*(.*)\*/#s', $doc, $comment) === false) {
            return [];
        }

        if (preg_match_all('#^\s*\*(.*)#m', trim($comment[1]), $lines) === false) {
            return [];
        }

        $title = $this->formatTitle($lines[1]);

        return $title;
    }

    /**
     * 格式化注释代码
     */
    public function formatDoc($doc, $reflection, $property, $nameSpace)
    {
        if (!$doc) {
            return [
                'method'      => null,
                'header'      => [],
                'body'        => null,
                'url'         => null,
                'description' => " ",
            ];
        }

        if (preg_match('#^/\*\*(.*)\*/#s', $doc, $comment) === false) {
            return [];
        }

        if (preg_match_all('#^\s*\*(.*)#m', trim($comment[1]), $lines) === false) {
            return [];
        }
        $controller = $reflection->getName();
        $method = $this->getRequestMethod($controller, $property->getName());
        $uri = $this->getRequestUrl($controller, $property->getName());

        $var = $this->formatVar($lines[1]);

        $body = [
            "mode"     => "formdata",
            "formdata" => $var,
        ];

        $url = [
            "raw"  => "{{url}}/".$uri,
            "host" => [
                "{{url}}"
            ],
            "path" => explode('/', $uri),
        ];

        if ($method == 'GET') {
            if($var){
                $url['query'] = $var;
            }

            return [
                'method'      => $method,
                'header'      => [],
                'url'         => $url,
                'description' => " ",
            ];
        } else {

            return [
                'method'      => $method,
                'header'      => [],
                'body'        => $body,
                'url'         => $url,
                'description' => " ",
            ];
        }
    }

    /**
     * 格式化标题
     */
    public function formatTitle($line)
    {
        if (count($line) > 0) {
            return trim($line[0]);
        } else {
            return null;
        }
    }

    /**
     * 格式化描述
     */
    public function formatDesc($lines)
    {
        $reg = '/@desc.*/i';
        $desc = [];

        foreach ($lines as $k => $line) {
            if (preg_match($reg, trim($line), $tmp) !== false)
                if (!empty($tmp)) {
                    $desc[] = trim(str_replace('@desc', "", $tmp[0]));
                }
        }

        return $desc;
    }


    /**
     * 格式化参数
     * @desc 类型 名称 是否必须 默认值 描述
     */
    public function formatVar($lines)
    {
        $reg = '/@var.*/i';
        $var = [];

        foreach ($lines as $k => $line) {
            if (preg_match($reg, trim($line), $tmp) !== false)
                if (!empty($tmp)) {
                    $temp = explode(' ', trim(str_replace('@var', "", $tmp[0])));

                    if (count($temp) == 5) {
                        $var[$k]['type'] = $temp[0];
                        $var[$k]['key'] = $temp[1];
                        $var[$k]['require'] = $temp[2];
                        $var[$k]['value'] = $temp[3];
                        $var[$k]['description'] = $temp[4];
                    }
                }
        }

        sort($var);
        return $var;
    }

    /**
     * 格式化返回值
     */
    public function formatReturn($lines)
    {
        $reg = '/@return.*/i';
        $return = [];

        foreach ($lines as $k => $line) {
            if (preg_match($reg, trim($line), $tmp) !== false) {
                if (!empty($tmp)) {
                    $temp = explode(' ', trim(str_replace('@return', "", $tmp[0])));
                    if (count($temp) == 2) {
                        $return[$k]['self_type'] = $temp[0];
                        $return[$k]['data_type'] = $temp[1];
                    }
                }
            }
        }

        sort($return);
        return $return;
    }
}