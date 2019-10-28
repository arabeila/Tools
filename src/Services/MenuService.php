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
        return $guard . '-menu';
    }

    public static function flush($guard)
    {
        Cache::tags(self::getTags($guard))->flush();
    }

    public function generate($data, $menu, $guard, $theme)
    {
        if (empty($data['children'])) {
            $icon = $data['icon'] ? '<i class="' . $data['icon'] . '"></i>' : '';

            $text = '<span>' . $data['name'] . '</span>';

            $link = Link::to($data['url'], $icon . $text)
                ->setAttribute('target', $data['type']);

            return $menu->addIf($this->checkPermission($guard, $data['url']), $link);
        }

        return $this->getChildMenu($data, $menu, $guard, $theme);
    }

    public function build($guard = 'admin', $theme = 'adminlte')
    {
        $key = Help::key('menus', Auth::guard($guard)->id());

        if (app()->environment() == 'local') {
            Cache::forget($key);
            self::flush($guard);
        }

        return Cache::tags(self::getTags())->rememberForever($key, function () use ($guard, $theme) {

            return $this->getMenu($guard, $theme);
        });
    }

    public function getMenu($guard, $theme)
    {
        switch (strtolower($theme)) {
            default:
            case 'adminlte':
                $menu = Menu::new()->add(Html::raw('菜单')->addParentClass('header'))->addClass('sidebar-menu')->setAttributes(['data-widget' => 'tree']);
                break;
            case 'metis':
                $menu = Menu::new()->add(Html::raw('菜单')->addParentClass('nav-header'))->addClass('bg-blue dker')->setAttribute('id', 'menu');
                break;
        }

        $menus = MenuModel::guardName($guard)->with('children')->where('parent_id', 0)->get()->toArray();

        foreach ($menus as $key => $value) {
            $menu = $this->generate($value, $menu, $guard, $theme);
        }

        return $menu;
    }

    public function getChildMenu($data, $menu, $guard, $theme)
    {
        switch (strtolower($theme)) {
            default:
            case 'adminlte':
                $child = Menu::new()->addClass('treeview-menu');

                $icon = $data['icon'] ? '<i class="' . $data['icon'] . '"></i>' : '';

                $text = '<span>' . $data['name'] . '</span>';

                $btn = '<span class="pull-right-container">
						<i class="fa fa-angle-left pull-right"></i>
					</span>';

                $child->addParentClass('treeview');
                break;
            case 'metis':
                $child = Menu::new()->addClass('collapse');

                $icon = $data['icon'] ? '<i class="' . $data['icon'] . '"></i>' : '';

                $text = '<span class="link-title">  ' . $data['name'] . "</span>";

                $btn = '<span class="fa arrow"></span>';
                break;
        }

        foreach ($data['children'] as $childData) {
            $child = $this->generate($childData, $child, $guard, $theme);
        }

        if ($child->count()) {
            $menu->submenu(Link::to('#', $icon . $text . $btn), $child);
        }

        return $menu;
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