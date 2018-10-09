<?php

namespace Ave40\Base\Model;

use Magento\Framework\Model\AbstractModel;

/**
 * Contact Resource Model
 * @method $this setLastUpdatedAt($value)
 * @method $this setErpCode($value)
 * @method $this setShippingCode($value)
 * @method $this setCountry($value)
 * @method $this setBattery($value)
 *
 * @method getLastUpdatedAt
 * @method getErpCode
 * @method getShippingCode
 * @method getCountry
 * @method getBattery
 */
class Shippingmapping extends AbstractModel
{
    const RESOURCE = ResourceModel\Shippingmapping::class;
    
    
    /**
     * Initialize resource
     *
     * @return void
     */
    public function _construct() {
        $this->_init(self::RESOURCE);
    }
    
    public function loadByName($name) {
        return $this->load($name, 'name');
    }
}
