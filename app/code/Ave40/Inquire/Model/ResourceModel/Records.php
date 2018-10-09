<?php

namespace Ave40\Inquire\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Records extends AbstractDb
{
    const TABLE = 'ave40_inquire_records';
    const PRIMARY = 'id';
    
    protected function _construct()
    {
        $this->_init(self::TABLE,self::PRIMARY);
    }
}