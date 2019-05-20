<?php
/**
 * Created by PhpStorm.
 * User: TELstatic
 * Date: 2019-05-17
 * Time: 8:36
 */

namespace Arabeila\Tools\Commands;

use Illuminate\Console\GeneratorCommand;

class CreateRequest extends GeneratorCommand
{
    protected $signature = 'create:request {name}';

    protected $description = 'Create request';

    protected $type = 'Request';

    protected function getStub()
    {
        return __DIR__.'/stubs/request.stub';
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\Http\Requests';
    }
}
