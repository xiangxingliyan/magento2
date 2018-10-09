<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018-6-12
 * Time: 下午 06:33
 */
namespace Ave40\Base\Api;

/**
 * Interface ProductInterface
 * @package Ave40\Base\Api
 * @api
 */
interface ProductInterface {
    /**
     * @api
     * @param string $productId
     * @param string $productSku
     * @param string $productItemnum
     * @return mixed
     */
    public function idconvert($productId=null, $productSku=null, $productItemnum=null);

    /**
     * @api
     * @param string $data
     * @return mixed
     */
    public function updateProductQtyBatch($data=null);
}