<?php
/**
 * Created by PhpStorm.
 * User: TELstatic
 * Date: 2019/8/23
 * Time: 14:23
 */

namespace Arabeila\Tools\Services;

use Illuminate\Support\Facades\Route;
use Arabeila\Tools\Supports\Str;

class PostmanService
{
    public $list = [];

    public $permissions;

    public $blackList;

    protected $controllerKey = 'controller';

    protected $actionKey = 'action';

    protected $methodKey = 'method';

    protected $description = 'description';

    protected $header = 'header';

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
            $arr = [];
            $actionName = $route->getActionName();

            preg_match('/\@/', $actionName, $end, PREG_OFFSET_CAPTURE);
            if (!isset($end[0])) {
                continue;
            }

            $controller = substr($actionName, 0, $end[0][1]);

            if (in_array($controller, $this->blackList)) {
                continue;
            }

            $arr[$this->controllerKey] = $controller;

            $action = substr($actionName, $end[0][1] + 1);
            $arr[$this->actionKey] = $action;

            $arr[$this->methodKey] = method_exists($route, 'getMethods') ? $route->getMethods()[0] : $route->methods[0];
            $arr['uri'] = method_exists($route, 'getPath') ? $route->getPath() : $route->uri;

            $arr['auth'] = false;

            foreach ($route->gatherMiddleware() as $item) {
                if (in_array($item, config('tools.guards'))) {
                    $arr['auth'] = true;
                }
            }

            if (!isset($this->list[$arr[$this->controllerKey]])) {
                $this->list[$arr[$this->controllerKey]] = [
                    $arr,
                ];
            } else {
                array_push($this->list[$arr[$this->controllerKey]], $arr);
            }
        }
    }

    /**
     * 返回控制器注释
     */
    public function index($namespce = null)
    {
        $data['info'] = [
            '_postman_id' => '',
            'name'        => config('app.name').' 接口文档',
            'schema'      => 'https://schema.getpostman.com/json/collection/v2.1.0/collection.json',
        ];

        $data['item'] = [];

        $res = [];

        $num = 0;

        foreach ($this->list as $key => $item) {
            if (!(!is_null($namespce) && Str::start_with($key, 'App\\Http\\Controllers\\'.$namespce))) {
                continue;
            }
            foreach ($item as $value) {
                $class = $value[$this->controllerKey];
                if (in_array($class, $res)) {
                    continue;
                }

                array_push($res, $class);

                $className = explode('\\', str_replace('App\Http\Controllers\\', '', $class));

                $reflection = new \ReflectionClass($class);

                $actions = $this->getActions($class);


                switch (count($className)) {
                    default:
                    case 1:
                        break;
                    case 2:
                    case 3:
                        $data['item'][$num++] = [
                            'name' => $this->getClassDoc($reflection),
                            'item' => $this->getActionDoc($actions, $reflection, $className[0]),
                        ];
                        break;
                }
            }
        }
        return $data;
    }

    /**
     * 获取所有权限
     */
    public function getPermissions($guard = 'Admin')
    {
        if (!isset($this->permissions[$guard])) {
            $this->permissions[$guard] = [];
        }

        $data = array_unique($this->permissions[$guard]);

        sort($data);

        return $data;
    }

    /**
     * 解析类注释
     */
    public function getClassDoc($reflection)
    {
        $doc = $reflection->getDocComment();

        return $this->formatClassDoc($doc);
    }

    /**
     * 获取控制器中方法
     */
    public function getActions($controller)
    {
        $obj = [];

        foreach ($this->list[$controller] as $value) {
            $obj[] = $value[$this->actionKey];
        }

        return $obj;
    }

    /**
     * 获取方法Url
     */
    public function getRequestUrl($controller, $action)
    {
        foreach ($this->list[$controller] as $item) {
            if ($item[$this->actionKey] == $action) {
                return $item['uri'];
            }
        }

        return null;
    }

    /**
     * 获取请求类型
     * @param $controller
     * @param $action
     * Date: 2019/11/18
     * @return null
     */
    public function getRequestMethod($controller, $action)
    {
        foreach ($this->list[$controller] as $item) {
            if ($item[$this->actionKey] == $action) {
                return $item[$this->methodKey];
            }
        }

        return null;
    }

    /**
     * 获取请求头
     */
    public function getRequestHeader($controller, $action)
    {
        foreach ($this->list[$controller] as $item) {
            if ($item[$this->actionKey] == $action) {
                if ($item['auth']) {
                    return [
                        [
                            'type'             => 'string',
                            'key'              => 'access-token',
                            'require'          => 'yes',
                            'value'            => '{{AccessToken}}',
                            $this->description => ' ',
                        ]
                    ];
                } else {
                    return [];
                }
            }
        }

        return [];
    }

    /**
     * 获取方法注释
     */
    public function getActionDoc($methodArray, \ReflectionClass $reflection, $nameSpace)
    {
        $arr = [];
        $methods = $reflection->getMethods();
        $i = 0;
        foreach ($methods as $property) {
            if (!in_array($property->getName(), $methodArray)) {
                continue;
            }

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
            return '';
        }

        if (preg_match('#^/\*\*(.*)\*/#s', $doc, $comment) === false || preg_match_all('#^\s*\*(.*)#m', trim($comment[1]), $lines) === false) {
            return [];
        }

        return $this->formatTitle($lines[1]);
    }

    /**
     * 格式化注释代码
     */
    public function formatDoc($doc, $reflection, $property)
    {
        if (!$doc) {
            return [
                $this->methodKey   => null,
                $this->header      => [],
                'body'             => null,
                'url'              => null,
                $this->description => ' ',
            ];
        }
        $controller = $reflection->getName();
        $header = $this->getRequestHeader($controller, $property->getName());

        if (preg_match('#^/\*\*(.*)\*/#s', $doc, $comment) === false || preg_match_all('#^\s*\*(.*)#m', trim($comment[1]), $lines) === false) {
            return [];
        }

        $method = $this->getRequestMethod($controller, $property->getName());
        $uri = $this->getRequestUrl($controller, $property->getName());

        $var = $this->formatVar($lines[1]);
        $desc = $this->formatDesc($lines[1]);

        $url = [
            'raw'  => '{{url}}'.DIRECTORY_SEPARATOR.$uri,
            'host' => [
                '{{url}}'
            ],
            'path' => explode(DIRECTORY_SEPARATOR, $uri),
        ];

        if ($method == 'GET') {
            if ($var) {
                $url['query'] = $var;
            }

            return [
                $this->methodKey   => $method,
                $this->header      => $header,
                'url'              => $url,
                $this->description => $desc,
            ];
        } else {
            $body = [
                'mode'     => 'formdata',
                'formdata' => $var,
            ];

            return [
                $this->methodKey   => $method,
                $this->header      => $header,
                'body'             => $body,
                'url'              => $url,
                $this->description => $desc,
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
        $desc = null;
        foreach ($lines as $line) {
            if (preg_match($reg, trim($line), $tmp) !== false && !empty($tmp)) {

                $desc = trim(str_replace('@desc', '', $tmp[0]));
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
            if (preg_match($reg, trim($line), $tmp) !== false && !empty($tmp)) {

                $temp = explode(' ', trim(str_replace('@var', '', $tmp[0])));

                if (count($temp) == 5) {
                    if (Str::start_with($temp[0], '$')) {
                        $var[$k]['type'] = $temp[1];
                        $var[$k]['key'] = $temp[0];
                    } else {
                        $var[$k]['type'] = $temp[0];
                        $var[$k]['key'] = $temp[1];
                    }
                    $var[$k]['require'] = $temp[2];
                    $var[$k]['value'] = $temp[3];
                    $var[$k][$this->description] = $temp[4];
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
            if (preg_match($reg, trim($line), $tmp) !== false && !empty($tmp)) {
                $temp = explode(' ', trim(str_replace('@return', '', $tmp[0])));
                if (count($temp) == 2) {
                    $return[$k]['self_type'] = $temp[0];
                    $return[$k]['data_type'] = $temp[1];
                }
            }
        }

        sort($return);
        return $return;
    }
}
