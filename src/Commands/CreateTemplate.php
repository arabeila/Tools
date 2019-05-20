<?php
/**
 * Created by PhpStorm.
 * User: TELstatic
 * Date: 2019-05-18
 * Time: 15:18
 */

namespace Arabeila\Tools\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Prettus\Repository\Generators\FileAlreadyExistsException;

class CreateTemplate extends Command
{
    protected $signature = 'create:template {path} {--m|model=} {--api} {--vue}';

    protected $description = 'Create template';

    protected $type = 'Template';

    protected $files;

    public function __construct(Filesystem $files)
    {
        parent::__construct();

        $this->files = $files;
    }

    public function handle()
    {
        $name = $this->qualifyClass($this->getPathInput());

        $path = $this->getPath($name);

        // First we will check to see if the class already exists. If it does, we don't want
        // to create the class and overwrite the user's code. So, we will bail out so the
        // code is untouched. Otherwise, we will continue generating this class' files.
        if ((!$this->hasOption('force') ||
                !$this->option('force')) &&
            $this->alreadyExists($this->getPathInput())) {
            $this->error($this->type.' already exists!');

            return false;
        }

//        $this->makeDirectory($path);

        $this->buildClass($path, $name);

        $this->info($this->type.' created successfully.');
    }

    protected function replaceClass($stub, $name)
    {
        $class = str_replace($this->getNamespace($name).'\\', '', $name);

        return str_replace('DummyModelClass', $class, $stub);
    }

    protected function buildClass($path, $name)
    {
        //API 仅有 index 方法
        if ($this->option('api')) {
            $stubFiles = [
                'index',
            ];
        } else {
            $stubFiles = [
                'index', 'create', 'edit',
            ];
        }

        foreach ($stubFiles as $stubFile) {
            $stub = File::get(str_replace('{method}', $stubFile, $this->getStub()));

            $replace = [];

            if ($this->option('model')) {
                $replace = $this->buildModelReplacements($replace);
            }

            $stub = str_replace(
                array_keys($replace), array_values($replace), $stub
            );

            if (!File::exists(dirname($path))) {
                mkdir(dirname($path));
            }

            file_put_contents(str_replace('{method}', $stubFile, $path), $stub);
        }
    }

    protected function alreadyExists($rawName)
    {
        if ($this->option('api')) {
            $stubFiles = [
                'index',
            ];
        } else {
            $stubFiles = [
                'index', 'create', 'edit',
            ];
        }

        foreach ($stubFiles as $stubFile) {
            if ($this->option('vue')) {
                if (!File::exists($rawName.'\\'.lcfirst(class_basename($this->option('model'))).'\\'.$stubFile.'.vue')) {
                    return false;
                }
            } else {
                if (!File::exists($rawName.'\\'.lcfirst(class_basename($this->option('model'))).'\\'.$stubFile.'.blade.php')) {
                    return false;
                }
            }
        }

        return true;
    }

    protected function getPath($name)
    {
        return $name;
    }

    public function getStub()
    {
        $stub = '\stubs\{method}.stub';

        if ($this->option('vue')) {
            $stub = str_replace('.stub', '.vue.stub', $stub);

            if ($this->option('api')) {
                //todo 不加载 create edit
                $stub = str_replace('.stub', '.api.stub', $stub);
            }

        }

        if (file_exists(resource_path('views'.$stub))) {
            return resource_path('views'.$stub);
        }

        return __DIR__.$stub;
    }

    protected function buildModelReplacements(array $replace)
    {
        $modelClass = $this->parseModel($this->option('model'));

        return array_merge($replace, [
            'DummyModelVariable' => lcfirst(class_basename($modelClass)),
        ]);
    }

    protected function parseModel($model)
    {
        if (preg_match('([^A-Za-z0-9_/\\\\])', $model)) {
            throw new InvalidArgumentException('Model name contains invalid characters.');
        }

        $model = trim(str_replace('/', '\\', $model), '\\');

        if (!Str::startsWith($model, $rootNamespace = $this->laravel->getNamespace())) {
            $model = $rootNamespace.$model;
        }

        return $model;
    }

    protected function makeDirectory($path)
    {
        if (!$this->files->isDirectory(dirname($path))) {
            $this->files->makeDirectory(dirname($path), 0777, true, true);
        }

        return $path;
    }

    protected function qualifyClass($path)
    {
        $path = ltrim($path, '\\/');
        $path = str_replace('/', '\\', $path);

        if ($this->option('vue')) {
            return $path.'\\'.lcfirst(class_basename($this->option('model'))).'\\'.'{method}.vue';
        }

        return $path.'\\'.lcfirst(class_basename($this->option('model'))).'\\'.'{method}.blade.php';
    }

    protected function getPathInput()
    {
        if ($this->option('vue')) {
            $rootPath = config('tools.path.vue');
        } else {
            $rootPath = config('tools.path.view');
        }

        return $rootPath.trim($this->argument('path'));
    }

    protected function rootNamespace()
    {
        return $this->laravel->getNamespace();
    }
}