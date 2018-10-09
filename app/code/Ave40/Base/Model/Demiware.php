<?php

namespace Ave40\Base\Model;

use Magento\Framework\Model\AbstractModel;

/**
 * Contact Resource Model
 * @method $this setCustomerId($value)
 * @method $this setFirstName($value)
 * @method $this setLastName($value)
 * @method $this setCountry($value)
 * @method $this setEmail($value)
 * @method $this setStreet($value)
 * @method $this setCity($value)
 * @method $this setState($value)
 * @method $this setPhone($value)
 * @method $this setSmokeForYears($value)
 * @method $this setReason($value)
 * @method $this setDemiwareCode($value)
 * @method $this setOtherPodSystems($value)
 * @method $this setLastUpdatedAt($value)
 *
 * @method getCustomerId
 * @method getFirstName
 * @method getLastName
 * @method getCountry
 * @method getEmail
 * @method getStreet
 * @method getCity
 * @method getState
 * @method getPhone
 * @method getSmokeForYears
 * @method getLastUpdatedAt
 * @method getReason
 * @method getOtherPodSystems
 * @method getDemiwareCode
 */
class Demiware extends AbstractModel
{
    const RESOURCE = ResourceModel\Demiware::class;


    /**
     * Initialize resource
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init(self::RESOURCE);
    }

    public function loadByCustomer($cid)
    {
        return $this->load($cid, 'customer_id');
    }

    /**
     * Get collection instance
     *
     * @return \Magento\Catalog\Model\ResourceModel\Collection\AbstractCollection
     */
    public function getResourceCollection()
    {
        $collection = parent::getResourceCollection();
        return $collection;
    }

    /**
     * 获取一条数据
     */
    public function loadByField($params)
    {
        if (!empty($params)) {
            $collection = $this->getResourceCollection();
            foreach ($params as $field => $value) {
                $collection->addFieldToFilter($field, $value);
            }
            $collection->setPage(1, 1);

            foreach ($collection as $object) {
                return $object;
            }
        }
        return false;
    }

    public function createNew($customerId, $data)
    {

    }
}
