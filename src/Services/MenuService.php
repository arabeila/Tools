<?php
/**
 * Created by PhpStorm.
 * User: satoshi
 * Date: 2019/10/17
 * Time: 14:30
 */

namespace Arabeila\Tools\Services;

use Arabeila\Tools\Supports\Help;
use Arabeila\Tools\Models\Menu as MenuModel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Spatie\Menu\Html;
use Spatie\Menu\Link;
use Spatie\Menu\Menu;

class MenuService
{
    public static function getTags($guard = 'admin')
    {
        return $guard.'-menu';
    }

    public static function flush($guard)
    {
        Cache::tags(self::getTags($guard))->flush();
    }

    public function generate($data,$menu,$guard)
    {
        if (empty($data['children'])) {
            $icon = $data['icon'] ? '<i class="'.$data['icon'].'"></i>' : '';

            $text = '<span>'.$data['name'].'</span>';

            $link = Link::to($data['url'], $icon.$text)
                ->setAttribute('target', $data['type']);

            return $menu->addIf($this->checkPermission($guard,$data['url']),$link);
        }

        $child = Menu::new()->addClass('treeview-menu');

        $icon = $data['icon'] ? '<i class="'.$data['icon'].'"></i>' : '';

        $text = '<span>'.$data['name'].'</span>';

        $btn = '<span class="pull-right-container">
						<i class="fa fa-angle-left pull-right"></i>
					</span>';

        $child->addParentClass('treeview');

        foreach ($data['children'] as $childData) {
            $child = $this->generate($childData, $child,$guard);
        }

        if($child->count()){
            $menu->submenu(Link::to('#', $icon.$text.$btn), $child);
        }

        return $menu;
    }

    public function build($guard)
    {
        $key = Help::key('menus',Auth::guard($guard)->id());

        if (app()->environment() == 'local') {
            Cache::forget($key);
            self::flush($guard);
        }

        echo Cache::tags(self::getTags())->rememberForever($key, function () use ($guard) {
            $menu = Menu::new()->add(Html::raw('菜单')->addParentClass('header'))->addClass('sidebar-menu')->setAttributes(['data-widget' => 'tree']);

            $menus = MenuModel::guardName($guard)->with('children')->where('parent_id',0)->get()->toArray();

            foreach ($menus as $key => $value) {
                $menu = $this->generate($value, $menu,$guard);
            }

            return $menu;
        });
    }

    /**
     * 检查权限
     * @param $permission
     * Date: 2019/10/14
     * @return bool
     */
    protected function checkPermission($guard, $permission)
    {
        if (Auth::guard($guard)->user()->hasRole('超级管理员')) {
            return true;
        }

        return Auth::guard($guard)->user()->can(ltrim($permission, '/'));
    }
}