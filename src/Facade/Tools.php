<?php
/**
 * Created by PhpStorm.
 * User: TELstatic
 * Date: 2019-05-17
 * Time: 8:49
 */

namespace Arabeila\Tools\Facade;

use Illuminate\Support\Facades\Facade;

class Tools extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'Tools';
    }

}
