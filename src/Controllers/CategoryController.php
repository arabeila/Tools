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
    protected $key;

    public function __construct($key = "category")
    {
        $this->key = strtolower($key);
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
     * 参数
     * @desc 参数
     */
    protected function getData(Request $request)
    {
        $data = [
            'name'         => $request->get('name'),
            'sort'         => $request->get('sort', 0),
            'if_show'      => $request->get('if_show', 1),
            'parent_id'    => $request->get('parent_id',0),
            'is_directory' => false,
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
     * 分类一览
     * @desc 分类一览
     */
    public function index(Request $request)
    {
        if (!$request->ajax()) {
            return view('Admin.category.index');
        }

        $whiteList = $this->getWhiteList();

        if (empty($whiteList)) {
            $categories = Category::where('parent_id', 0)->with('allChildren')->orderBy('sort')->get();
        } else {
            $categories = Category::whereIn('id', $whiteList)->where('parent_id', 0)->with('allChildren')->orderBy('sort')->get();
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

        return Cache::rememberForever($this->getSettingKey(), function () {
            $whiteList = $this->getWhiteList();

            if (empty($whiteList)) {
                $categories = Category::where('parent_id', 0)->with('children')->orderBy('sort')->get();
            } else {
                $categories = Category::whereIn('id', $whiteList)->where('parent_id', 0)->with('children')->orderBy('sort')->get();
            }

            return $categories;
        });
    }

    /**
     * 添加分类
     * @desc 添加分类
     */
    public function store(Request $request)
    {
        $category = Category::create($this->getData($request));

        if ($category) {
            $this->refreshCache();
        }

        $parent = null;

        if ($request->filled('parent_id') && $category->parent_id != 0) {

            $parent = Category::findOrFail($category->parent_id);

            $parent->is_directory = true;

            $parent->save();
        }

        if (isset($parent)) {
            $category->parent()->associate($parent);
        }

        $bool = $category->save();

        return Help::reply($bool, $bool ? '分类添加成功' : '分类添加失败');
    }

    /**
     * 更新分类
     * @desc 更新分类
     */
    public function update($id, Request $request)
    {
        $data = array_only($this->getData($request), ['name', 'sort', 'if_show']);

        $bool = Category::where('id', $id)->update($data);

        if ($bool) {
            $this->refreshCache();
        }

        return Help::reply($bool, $bool ? '分类修改成功' : '分类修改失败');
    }

    /**
     * 删除分类
     * @desc 删除分类
     */
    public function destroy($id)
    {
        $this->refreshCache();

        $bool = Category::destroy($id);

        return Help::reply($bool, $bool ? '分类删除成功' : '分类删除失败');
    }

    /**
     * 刷新缓存
     * @desc 刷新缓存
     */
    protected function refreshCache()
    {
        setting([$this->getSettingKey() => setting($this->getSettingKey(), 1) + 1])->save();
    }
}