<?php

namespace Arabeila\Tools\Commands;

use Arabeila\Tools\Services\PermissionService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;

class CreatePermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tool:create-permissions {--g|guard=admin}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create permissions';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $permission = new PermissionService();

        switch (strtoupper($this->option('guard'))) {
            default:
            case 'CLEAN':
                return $this->clean();
                break;
            case 'USER':
                $permissions = $permission->getPermissions('User');
                break;
            case 'ADMIN':
                $permissions = $permission->getPermissions('Admin');
                break;
            case 'STORE':
                $permissions = $permission->getPermissions('Store');
                break;
            case 'API':
                $permissions = $permission->getPermissions('Api');
                break;
        }

        $this->save($permissions);
    }

    /**
     *清空权限
     * @desc 清空权限
     */
    public function clean()
    {
        if (!app()->runningInConsole()) {
            return;
        }
        if ($this->confirm('此操作会清空角色权限表和权限表,确定继续?')) {

            DB::table('role_has_permissions')->truncate();
            DB::table('permissions')->truncate();

            //清空权限缓存
            app()['cache']->forget('spatie.permission.cache');

            $this->info('权限清空完成!');
        } else {
            $this->info('操作取消!');
        }
    }

    /**
     * 更新权限
     * @desc 更新权限
     */
    public function save($permissions)
    {
        $bar = $this->output->createProgressBar(count($permissions));

        $guardName = $this->option('guard');

        foreach ($permissions as $item) {
            Permission::findOrCreate($item, $guardName);

            $bar->advance();
        }

        $bar->finish();

        $this->info('');
        $this->info($guardName.' 权限更新完成');
    }
}
