<?php
/**
 * Created by PhpStorm.
 * User: TELstatic
 * Date: 2019/8/23
 * Time: 14:22
 */

namespace Arabeila\Tools\Services;

use Illuminate\Support\Facades\Route;

class PermissionService
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

            $arr['controller'] = $controller;

            $action = substr($actionName, $end[0][1] + 1);
            $arr['action'] = $action;

            $arr['method'] = method_exists($route, 'getMethods') ? $route->getMethods()[0] : $route->methods[0];
            $arr['uri'] = method_exists($route, 'getPath') ? $route->getPath() : $route->uri;

            if (!isset($this->list[$arr['controller']])) {
                $this->list[$arr['controller']] = [
                    $arr,
                ];
            } else {
                array_push($this->list[$arr['controller']], $arr);
            }
        }
    }

    /**
     * 返回控制器注释
     */
    public function index()
    {
        $data = [];

        foreach ($this->list as $item) {
            foreach ($item as $value) {
                $class = $value['controller'];

                $className = explode('\\', str_replace('App\Http\Controllers\\', '', $class));

                $reflection = new \ReflectionClass($class);

                $actions = $this->getActions($class);

                switch (count($className)) {
                    case 1:
                        //$data['/'][$className[0]] = $this->getActionDoc($actions, $reflection);
                        break;
                    case 2:
                        $data[$className[0]][$className[0].'/'.$className[1]]['class'] = $this->getClassDoc($reflection);
                        $data[$className[0]][$className[0].'/'.$className[1]]['actions'] = $this->getActionDoc($actions, $reflection, $className[0]);
                        $data[$className[0]][$className[0].'/'.$className[1]]['uri'] = $value['uri'];
                        break;
                    case 3:
                        $data[$className[0]][$className[0].'/'.$className[1].'/'.$className[2]]['class'] = $this->getClassDoc($reflection);
                        $data[$className[0]][$className[0].'/'.$className[1].'/'.$className[2]]['actions'] = $this->getActionDoc($actions, $reflection, $className[0]);
                        $data[$className[0]][$className[0].'/'.$className[1].'/'.$className[2]]['uri'] = $value['uri'];
                        break;
                }
            }
        }
        return $data;
    }

    /**
     * 获取所有权限
     */
    public function getPermissions($guard = "Admin")
    {
        $this->index();

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

        foreach ($this->list[$controller] as $value) {
            $obj[] = $value['action'];
        }
        return $obj;
    }

    /**
     * 获取方法Url
     */
    public function getRequestUrl($controller, $action)
    {
        foreach ($this->list[$controller] as $item) {
            if ($item['action'] == $action) {
                return config('app.url').$item['uri'];
            }
        }
        return null;
    }

    /**
     * 获取请求类型
     */
    public function getRequestMethod($controller, $action)
    {
        foreach ($this->list[$controller] as $item) {
            if ($item['action'] == $action) {
                return $item['method'];
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

            $arr[$i]['name'] = $property->getName();
            $controller = $reflection->getName();
            $arr[$i]['url'] = $this->getRequestUrl($controller, $arr[$i]['name']);
            $arr[$i]['auth'] = str_replace(config('app.url'), '', $arr[$i]['url']);

            if (isset($this->permissions[$nameSpace])) {
                array_push($this->permissions[$nameSpace], $arr[$i]['auth']);
            } else {
                $this->permissions[$nameSpace] = [];
            }

            array_push($this->permissions[$nameSpace], $arr[$i]['auth']);
            $arr[$i]['method'] = $this->getRequestMethod($controller, $arr[$i]['name']);
            $arr[$i]['_expanded'] = true;

            $arr[$i]['doc'] = $this->formatDoc($doc, $nameSpace);

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
            return [
                'title' => null,
                'check' => false,
                'desc'  => [],
            ];
        }

        if (preg_match('#^/\*\*(.*)\*/#s', $doc, $comment) === false) {
            return [];
        }

        list($doc) = preg_match_all('#^\s*\*(.*)#m', trim($comment[1]), $lines);

        if ($doc === false) {
            return [];
        }

        $title = $this->formatTitle($lines[1]);
        $desc = $this->formatDesc($lines[1]);

        return [
            'title' => $title,
            'check' => false,
            'desc'  => $desc,
        ];
    }

    /**
     * 格式化注释代码
     */
    public function formatDoc($doc, $nameSpace)
    {
        if (!$doc) {
            return [
                'title'   => null,
                'desc'    => [],
                'params'  => null,
                'returns' => null
            ];
        }

        if (preg_match('#^/\*\*(.*)\*/#s', $doc, $comment) === false) {
            return [];
        }

        list($doc) = preg_match_all('#^\s*\*(.*)#m', trim($comment[1]), $lines);

        if ($doc === false) {
            return [];
        }

        $title = $this->formatTitle($lines[1]);
        $desc = $this->formatDesc($lines[1]);
        $params = $this->formatParams($lines[1]);
        $return = $this->formatReturn($lines[1]);

        return [
            'title'   => $title,
            'desc'    => $desc,
            'params'  => $params,
            'returns' => $return
        ];
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
     * @desc 名称 类型 是否必须 默认值 最大值 最小值 描述
     */
    public function formatParams($lines)
    {
        $reg = '/@var.*/i';
        $params = [];

        foreach ($lines as $k => $line) {
            if (preg_match($reg, trim($line), $tmp) !== false) {
                if (!empty($tmp)) {
                    list($type, $name, $require, $default, $comment) = explode(' ', trim(str_replace('@var', "", $tmp[0])));

                    $params[$k]['type'] = $type;
                    $params[$k]['name'] = $name;
                    $params[$k]['require'] = $require;
                    $params[$k]['default'] = $default;
                    $params[$k]['comment'] = $comment;
                }
            }
        }

        sort($params);
        return $params;
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
                    list($self_type) = explode(' ',trim(str_replace('@return',"",$tmp[0])));

                    $return[$k]['self_type']  = $self_type;
//                    $return[$k]['data_type']  = ;
                }
            }
        }

        sort($return);
        return $return;
    }
}
