<?php
namespace Ave40\Erpapi\Api;

interface ProductInfoInterface
{
    /**
     * Returns greeting message to user
     *
     * @api
     * @param string $name Users name.
     * @return string Greeting message with users name.
     */
    public function ProductInfo($itemnum);
}