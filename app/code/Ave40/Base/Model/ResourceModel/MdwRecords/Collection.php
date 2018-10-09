<?php

namespace Ave40\Base\Model\ResourceModel\MdwRecords;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends  AbstractCollection
{
    const MODEL = \Ave40\Base\Model\Mdw\Records::Class;
    const RESOURCE_MODEL = \Ave40\Base\Model\ResourceModel\MdwRecords::Class;
    
    public function _construct()
    {
        $this->_init(self::MODEL, self::RESOURCE_MODEL);
    }
}