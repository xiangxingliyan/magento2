<?php

namespace Ave40\Base\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Contact Resource Model
 */
class Shippingmapping extends AbstractDb
{
    const TABLE = 'ave40_shippingmapping';
    const PRIMARY = 'id';
    
    /**
     * Initialize resource
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init(self::TABLE, self::PRIMARY);
    }
}
