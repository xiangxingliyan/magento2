<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018-6-12
 * Time: 下午 06:33
 */
namespace Ave40\Base\Api;

/**
 * Interface OrderInterface
 * @package Ave40\Base\Api
 * @api
 */
interface OrderInterface {
    /**
     * @api
     * @param mixed $orderInfo
     * @return mixed
     */
    public function updateOrderState($orderInfo);
}