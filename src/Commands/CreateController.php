<?php
/**
 * Created by PhpStorm.
 * User: TELstatic
 * Date: 2019-05-17
 * Time: 8:36
 */

namespace Arabeila\Tools\Commands;

use Illuminate\Support\Str;
use Illuminate\Console\Command;
use InvalidArgumentException;
use Symfony\Component\Console\Input\InputOption;
use  Illuminate\Routing\Console\ControllerMakeCommand;

class CreateController extends ControllerMakeCommand
{
    protected $signature = 'create:controller {name} {--m|model=} {--api} {--parent}';

    protected $description = 'Create a new customize controller class';

    protected $type = 'Controller';

    protected $suffix;

    protected $dummyModelName;

    protected $time = 1;        //多次 调用call bug 临时补丁

    public function handle()
    {
        $this->dummyModelName = 'Placeholder';

        return parent::handle();
    }

    protected function getStub()
    {
        $stub = null;

        if ($this->option('model')) {
            $stub = '/stubs/controller.model.stub';
        } else {
            $stub = '/stubs/controller.stub';
        }

        if ($this->option('api')) {
            $stub = str_replace('.stub', '.api.stub', $stub);
        }

        if (file_exists(resource_path($stub))) {

            return resource_path($stub);
        }

        return __DIR__.$stub;
    }

    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\Http\Controllers';
    }

    protected function buildModelReplacements(array $replace)
    {
        $modelClass = $this->parseModel($this->option('model'));

        if (!class_exists($modelClass)) {
            if ($this->time++ < 3) {
                if ($this->confirm("A {$modelClass} model does not exist. Do you want to generate it?", true)) {
                    $this->call('make:model', ['name' => $modelClass]);
                }
            }
        }

        $requestClass = $this->parseRequest('App\\Http\Requests\\'.class_basename($modelClass).'Request');

        if (!class_exists($requestClass)) {
            if ($this->time++ < 3) {
                if ($this->confirm("A {$requestClass} request does not exist. Do you want to generate it?", true)) {
                    $this->call('create:request', ['name' => $requestClass]);
                }
            }
        }

        return array_merge($replace, [
            'DummyFullModelClass'       => $modelClass,
            'DummyModelClass'           => class_basename($modelClass),
            'DummyModelVariable'        => lcfirst(class_basename($modelClass)),
            'DummyModelPluralLowerCase' => str_plural(lcfirst(class_basename($modelClass))),
            'DummyModelName'            => $this->dummyModelName,
            'DummyRequest'              => class_basename($modelClass).'Request',
            'DummyFullRequestClass'     => 'App\\Http\Requests\\'.class_basename($modelClass).'Request',
            'DummyBladePath'            => end($this->suffix).'.'.lcfirst(class_basename($modelClass)),
        ]);
    }

    protected function buildClass($name)
    {
        $controllerNamespace = $this->getNamespace($name);

        $this->suffix = explode('\\', $controllerNamespace);

        $replace = [];

        if ($this->option('model')) {
            $replace = $this->buildModelReplacements($replace);
        }

        $replace["use {$controllerNamespace}\Controller;\n"] = '';

        return str_replace(
            array_keys($replace), array_values($replace), parent::buildClass($name)
        );
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

    protected function parseRequest($request)
    {
        if (preg_match('([^A-Za-z0-9_/\\\\])', $request)) {
            throw new InvalidArgumentException('Model name contains invalid characters.');
        }

        $request = trim(str_replace('/', '\\', $request), '\\');

        if (!Str::startsWith($request, $rootNamespace = $this->laravel->getNamespace())) {
            $request = $rootNamespace.$request;
        }

        return $request;
    }
}
