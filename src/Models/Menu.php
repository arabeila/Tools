<?php

namespace Arabeila\Tools\Models;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $fillable = [
        'icon',
        'type',
        'parent_id',
        'url',
        'name',
        'sort',
        'guard_name',
        'created_at',
        'updated_at',
    ];

    protected $appends = [
        'expand', 'title', 'label', 'value'
    ];

    public function scopeGuardName($query, $guard = 'admin')
    {
        return $query->where('guard_name', $guard);
    }

    public function getExpandAttribute()
    {
        return true;
    }

    public function getTitleAttribute()
    {
        return $this->name;
    }

    public function getLabelAttribute()
    {
        $this->getTitleAttribute();
    }

    public function getValueAttribute()
    {
        return $this->id;
    }

    public function child()
    {
        return $this->hasMany(get_class($this), 'parent_id', 'id');
    }

    public function children()
    {
        return $this->child()->with(['children']);
    }

    public static function getData($guard)
    {
        $menus = [
            'admin' => [
                [
                    'icon' => 'fa fa-tachometer',
                    'url'  => '/admin',
                    'name' => '控制面板',
                    'sort' => 0,
                    'type' => '_self'
                ],
                [
                    'icon'     => 'fa fa-bell-o',
                    'url'      => '#',
                    'name'     => '公告管理',
                    'sort'     => 0,
                    'type'     => '_self',
                    'children' => [
                        [
                            'icon' => 'fa fa-circle-o',
                            'url'  => '/admin/notice/create',
                            'name' => '添加公告',
                            'sort' => 0,
                            'type' => '_self',
                        ],
                        [
                            'icon'     => 'fa fa-circle-o',
                            'url'      => '/admin/notice/index',
                            'name'     => '公告一览',
                            'sort'     => 0,
                            'type'     => '_self',
                            'children' => [
                                [
                                    'icon' => 'fa fa-circle-o',
                                    'url'  => '/admin/notice/{id}',
                                    'name' => '公告详情',
                                    'sort' => 0,
                                    'type' => '_self',
                                ],
                                [
                                    'icon' => 'fa fa-circle-o',
                                    'url'  => '/admin/notice/batch',
                                    'name' => '公告删除',
                                    'sort' => 0,
                                    'type' => '_self',
                                ]
                            ],
                        ],
                    ],
                ],
            ],
        ];

        return $menus[$guard];
    }
}
