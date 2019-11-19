<?php

namespace Arabeila\Tools\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Category extends Model
{
    protected $guarded = [];

    const IS_SHOW_ACTIVATE = 1;
    const IS_SHOW_DEACTIVATE = 2;

    public static $isShowMap = [
        self::IS_SHOW_ACTIVATE   => '显示',
        self::IS_SHOW_DEACTIVATE => '隐藏',
    ];

    protected $casts = [
        'is_directory' => 'boolean',
    ];

    protected $appends = [
        'disabled',
        'expand',
        'label',
        'value',
        'key',
    ];

    protected static function boot()
    {
        parent::boot();

        // 监听 Category 的创建事件,用于初始化 path 和 level 字段值
        static::creating(function ($category) {
            $category->clear();

            if (!$category->parent_id) {
                $category->level = 0;
                $category->path = '-';
            } else {
                $category->level = $category->parent->level + 1;
                $category->path = $category->parent->path.$category->parent_id.'-';
            }
        });

        static::updating(function ($category) {
            $category->clear();
        });

        static::deleting(function ($category) {
            $category->clear();
            $category->allChildren()->delete();
        });
    }

    public function scopeRoot($builder, $parentId = null)
    {
        return $builder->where('parent_id', $parentId);
    }

    // 获取分类名称
    public function getLabelAttribute()
    {
        return $this->name;
    }

    // 获取分类 id
    public function getValueAttribute()
    {
        return $this->id;
    }

    // 获取分类 id
    public function getKeyAttribute()
    {
        $this->getValueAttribute();
    }

    // 获取分类显示状态
    public function getDisabledAttribute()
    {
        return !$this->is_show === 1;
    }

    // 获取分类层级
    public function getExpandAttribute()
    {
        return $this->level === 1;
    }

    // 获取父级分类
    public function parent()
    {
        return $this->belongsTo(get_class($this));
    }

    // 获取子分类
    public function child()
    {
        return $this->hasMany(get_class($this), 'parent_id')->orderBy('sort', 'desc');
    }

    // 获取所有子分类
    public function allChildren()
    {
        return $this->child()->with(['allChildren', 'parent']);
    }

    // 获取显示中的子分类
    public function childShow()
    {
        return $this->hasMany(get_class($this), 'parent_id')->orderBy('sort', 'desc')->where('is_show', 1);
    }

    // 获取所有子分类
    public function children()
    {
        return $this->childShow()->with('children');
    }

    // 获取所有祖先分类的 ID 值
    public function getPathIdsAttribute()
    {
        return array_filter(explode('-', trim($this->path, '-')));
    }

    // 获取所有祖先分类并按层级排序
    public function getAncestorsAttribute()
    {
        return Category::query()
            ->whereIn('id', $this->path_ids)
            ->orderBy('level')
            ->get();
    }

    // 获取以 - 为分隔的所有祖先分类名称以及当前分类的名称
    public function getFullNameAttribute()
    {
        return $this->ancestors
            ->pluck('name')
            ->push($this->name)
            ->implode(' - ');
    }

    // 获取所有祖先分类及自身的 ID 值
    public function getFullPathIdsAttribute()
    {
        return array_filter(explode('-', trim('-'.$this->id.'-'.$this->path, '-')));
    }

    /**
     * 缓存数据
     * Date: 2019/11/19
     * @return mixed
     */
    public function cache()
    {
        return Cache::tags($this->getTable())->rememberForever($this->getTable(), function () {
            return self::root(0)->with('children')->orderBy('sort', 'desc')->get();
        });
    }

    /**
     * 清除缓存
     * Date: 2019/11/19
     */
    public function clear()
    {
        Cache::tags($this->getTable())->flush();
    }
}
