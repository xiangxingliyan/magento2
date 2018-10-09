<?php

namespace Ave40\Base\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class MdwRecords extends AbstractDb
{
    const TABLE = 'ave40_mdw_sync_records';
    const PRIMARY = 'id';
    
    protected function _construct()
    {
        $this->_init(self::TABLE,self::PRIMARY);
    }
}