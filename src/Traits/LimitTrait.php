<?php
/**
 * Created by PhpStorm.
 * User: TELstatic
 * Date: 2019/11/19
 * Time: 9:51
 */

trait LimitTrait
{
    /**
     * 限定用户
     * @param $builder
     * @param $userId
     * Date: 2019/11/19
     * @return mixed
     */
    public function scopeUser($builder, $userId)
    {
        return $builder->where('user_id', $userId);
    }

    /**
     * 限定店铺
     * @param $builder
     * @param $storeId
     * Date: 2019/11/19
     * @return mixed
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