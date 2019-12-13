<?php

namespace Arabeila\Tools\Commands;

use Arabeila\Tools\Models\Menu;
use Illuminate\Console\Command;

class CreateMenus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tool:create-menus {--g|guard=admin}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '生成菜单';

    protected $model;

    public function __construct(Menu $model)
    {
        parent::__construct();

        $this->model = $model;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->model::guardName($this->option('guard'))->delete();

        $menus = $this->model::getData($this->option('guard'));

        foreach ($menus as $item) {
            $this->generate($item, 0, $this->option('guard'));
        }
    }

    public function generate($data, $parent_id, $guard)
    {
        $data['parent_id'] = $parent_id;
        $data['guard_name'] = $guard;

        $menu = $this->model::create($data);

        if (isset($data['children'])) {
            foreach ($data['children'] as $child) {
                $this->generate($child, $menu->id, $guard);
            }
        }
    }
}