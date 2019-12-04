<?php

namespace Arabeila\Tools\Controllers;

use Arabeila\Tools\Models\Category;
use Arabeila\Tools\Supports\Help;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Http\Controllers\Controller;

/**
 * 分类管理
 * @desc 分类管理
 */
class CategoryController extends Controller
{
    protected $model;
    protected $key;
    protected $title;
    protected $path;
    protected $tag;

    protected $is_show = 'is_show';

    protected $parent_id = 'parent_id';

    public function __construct($model, $key, $title, $path = '', $tag = '')
    {
        $this->model = $model;
        $this->key = strtolower($key);
        $this->title = $title;
        $this->path = $path;
        $this->tag = $tag;

        if (!$this->tag) {
            throw new \Exception('param tag is not defined!');
        }
    }

    /**
     * 白名单
     * @desc 白名单
     * @return array
     */
    protected function getWhiteList()
    {
        return [];
    }

    /**
     * 分类参数
     * @desc 分类参数
     * @param $request
     * @var string $name yes null 分类名
     * @var int $sort yes 0 排序
     * @var int $is_show yes 1 是否显示
     * @var int $parent_id yes 0 父级ID
     * @return array
     */
    protected function getData(Request $request)
    {
        $data = [
            'name'           => $request->get('name'),
            'sort'           => $request->get('sort', 0),
            $this->is_show   => $request->get('is_show', 1),
            $this->parent_id => $request->get('parent_id', 0),
            'is_directory'   => false,
        ];

        return $data;
    }

    /**
     * setting键名
     * @desc setting键名
     */
    protected function getSettingKey()
    {
        return $this->key.'_version';
    }

    /**
     * 获取分类一览
     * @desc 获取分类一览
     * @param $request
     * @return mixed
     */
    public function index(Request $request)
    {
        if (!$request->ajax()) {
            return view($this->path);
        }

        $whiteList = $this->getWhiteList();

        if (empty($whiteList)) {
            $categories = $this->model::where($this->parent_id,
                0)->with('allChildren')->orderBy('sort', 'desc')->get();
        } else {
            $categories = $this->model::whereIn('id', $whiteList)->where($this->parent_id,
                0)->with('allChildren')->orderBy('sort', 'desc')->get();
        }

        return $categories;
    }

    /**
     * 分类级联
     * @desc 分类级联
     */
    public function cascade()
    {
        if (config('tools.category.refresh')) {
            Cache::forget($this->getSettingKey());
        }

        return Cache::tags($this->tag)->rememberForever($this->getSettingKey(), function () {
            $whiteList = $this->getWhiteList();

            if (empty($whiteList)) {
                $categories = $this->model::where($this->parent_id,
                    0)->with('children')->orderBy('sort')->get();
            } else {
                $categories = $this->model::where($this->parent_id,
                    0)->whereIn('id', $whiteList)->with([
                    'children'                   => function ($query) use ($whiteList) {
                        $query->whereIn('id', $whiteList);
                    },
                    'children.children'          => function ($query) use ($whiteList) {
                        $query->whereIn('id', $whiteList);
                    },
                    'children.children.children' => function ($query) use ($whiteList) {
                        $query->whereIn('id', $whiteList);
                    },
                ])->orderBy('sort', 'desc')->get();
            }

            return $categories;
        });
    }

    /**
     * 添加分类
     * @desc 添加分类
     * @param $request
     * @var string $name yes null 分类名
     * @var int $sort yes 0 排序
     * @var int $is_show yes 1 是否显示
     * @var int $parent_id yes 0 父级ID
     * @return mixed
     */
    public function store(Request $request)
    {
        $category = $this->model::create($this->getData($request));

        if ($category) {
            $this->refreshCache();
        }

        $parent = null;

        if ($request->filled($this->parent_id) && $category->parent_id != 0) {

            $parent = $this->model::findOrFail($category->parent_id);

            $parent->is_directory = true;

            $parent->save();
        }

        if (isset($parent)) {
            $category->parent()->associate($parent);
        }

        $bool = $category->save();

        return Help::reply($bool, $bool ? $this->title.'分类添加成功' : $this->title.'分类添加失败');
    }

    /**
     * 更新分类
     * @desc 更新分类
     * @param $id
     * @param $request
     * @var string $name yes null 分类名
     * @var int $sort yes 0 排序
     * @var int $is_show yes 1 是否显示
     * @return mixed
     */
    public function update($id, Request $request)
    {
        $data = array_only($this->getData($request), ['name', 'sort', $this->is_show]);

        $bool = $this->model::where('id', $id)->update($data);

        if ($bool) {
            $this->refreshCache();
        }

        return Help::reply($bool, $bool ? $this->title.'分类修改成功' : $this->title.'分类修改失败');
    }

    /**
     * 删除分类
     * @desc 删除分类
     * @param $id
     * @var int $id yes null 分类ID
     * @return mixed
     */
    public function destroy($id)
    {
        $bool = $this->model::destroy($id);

        if ($bool) {
            $this->refreshCache();
        }

        return Help::reply($bool, $bool ? $this->title.'分类删除成功' : $this->title.'分类删除失败');
    }

    /**
     * 刷新缓存
     * @desc 刷新缓存
     */
    protected function refreshCache()
    {
        Cache::forget($this->getSettingKey());
        Cache::flush($this->tag);
    }
}
