<?php
/**
 * Created by PhpStorm.
 * User: satoshi
 * Date: 2019/10/17
 * Time: 14:30
 */

namespace Arabeila\Tools\Services;

use Arabeila\Tools\Supports\Help;
use Arabeila\Tools\Models\Menu as AdminMenu;
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

    public function build($guard)
    {
        $key = Help::key('menus',Auth::guard($guard)->id());

        if (app()->environment() == 'local') {
            Cache::forget($key);
            self::flush($guard);
        }

        echo Cache::tags(self::getTags())->rememberForever($key, function () use ($guard) {
            $menu = Menu::new()->add(Html::raw('菜单')->addParentClass('header'))->addClass('sidebar-menu')->setAttributes(['data-widget' => 'tree']);

            $menus = AdminMenu::guardName($guard)->with('child')->where('parent_id',0)->get()->toArray();

            foreach ($menus as $key => $value) {
                if ($value['child'] != null) {
                    $child = Menu::new()->addClass('treeview-menu');

                    for ($i = 0; $i < count($value['child']); $i++) {

                        if ($value['child'][$i]['icon'] != null) {
                            $icon = '<i class="'.$value['child'][$i]['icon'].'"></i>';
                        } else {
                            $icon = '';
                        }

                        $text = '<span>'.$value['child'][$i]['name'].'</span>';

                        $link = Link::to($value['child'][$i]['url'], $icon.$text)
                            ->setAttribute('target', $value['type']);

                        $child->addIf($this->checkPermission($guard, $value['child'][$i]['url']), $link);
                    }

                    $child->addParentClass('treeview');

                    if ($value['icon'] != null) {
                        $icon = '<i class="'.$value['icon'].'"></i>';
                    } else {
                        $icon = '';
                    }

                    $text = '<span>'.$value['name'].'</span>';

                    $btn = '<span class="pull-right-container">
						<i class="fa fa-angle-left pull-right"></i>
					</span>';

                    if ($child->count()) {
                        $menu->submenu(Link::to('#', $icon.$text.$btn), $child);
                    }
                } else {
                    if ($value['icon'] != null) {
                        $icon = '<i class="'.$value['icon'].'"></i>';
                    } else {
                        $icon = '';
                    }
                    $text = '<span>'.$value['name'].'</span>';

                    $link = Link::to($value['url'], $icon.$text)
                        ->setAttribute('target', $value['type']);

                    if (!$value['parent_id']) {

                        $menu->addIf($this->checkPermission($guard, $value['url'], '/'), $link);
                    }
                }
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