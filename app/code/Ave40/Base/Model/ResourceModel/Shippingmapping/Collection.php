<?php

namespace Ave40\Base\Model\ResourceModel\Shippingmapping;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Contact Resource Model Collection
 *
 * @author      Pierre FAY
 */
class Collection extends AbstractCollection
{
    const MODEL = \Ave40\Base\Model\Shippingmapping::Class;
    const RESOURCE_MODEL = \Ave40\Base\Model\ResourceModel\Shippingmapping::Class;
    /**
     * Initialize resource collection
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init(self::MODEL, self::RESOURCE_MODEL);
    }
}