<?php

namespace Ave40\Base\Model\ResourceModel\Demiware;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Contact Resource Model Collection
 *
 * @author      Pierre FAY
 */
class Collection extends AbstractCollection
{
    /**
     * Store Id
     *
     * @var int
     */
    protected $_storeId = 0;

    const MODEL = \Ave40\Base\Model\Demiware::Class;

    const RESOURCE_MODEL = \Ave40\Base\Model\ResourceModel\Demiware::Class;

    /**
     * Initialize resource collection
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init(self::MODEL, self::RESOURCE_MODEL);
    }

    /**
     * Setter
     *
     * @param integer $storeId
     * @return $this
     */
    public function setStoreId($storeId)
    {
        $this->_storeId = $storeId;
        return $this;
    }

    /**
     * Getter
     *
     * @return integer
     */
    public function getStoreId()
    {
        return $this->_storeId;
    }

    /**
     * Set collection page start and records to show
     *
     * @param integer $pageNum
     * @param integer $pageSize
     * @return $this
     * @codeCoverageIgnore
     */
    public function setPage($pageNum, $pageSize)
    {
        $this->setCurPage($pageNum)->setPageSize($pageSize);
        return $this;
    }
}