<?php

/**
 * Created by PhpStorm.
 * User: TELstatic
 * Date: 2019/11/19
 * Time: 9:51
 */

namespace Arabeila\Tools\Traits;

trait LimitTrait
{
    /**
     * 限定用户
     * @param $builder
     * @param $userId
     * @return mixed
     * @deprecated
     * Date: 2019/11/19
     */
    public function scopeUser($builder, $userId)
    {
        return $builder->where('user_id', $userId);
    }

    /**
     * 限定店铺
     * @param $builder
     * @param $storeId
     * @return mixed
     * @deprecated
     * Date: 2019/11/19
     */
    public function scopeStore($builder, $storeId)
    {
        return $builder->where('store_id', $storeId);
    }

    /**
     * 限定平台
     * @param $builder
     * Date: 2019/11/19
     * @return mixed
     */
    public function scopeAdmin($builder)
    {
        return $builder->where('store_id', 0);
    }
}