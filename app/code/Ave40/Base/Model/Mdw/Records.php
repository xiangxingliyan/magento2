<?php

namespace Ave40\Base\Model\Mdw;

use Magento\Framework\Model\AbstractModel;

class Records extends AbstractModel
{
    const RESOURCE = \Ave40\Base\Model\ResourceModel\MdwRecords::class;
    
    const TYPE_ORDER_UPLOAD = 'order_upload';
    const TYPE_CUSTOMER_UPLOAD = 'customer_upload';
    
    const STATUS_COMPLETED = 'completed';
    const STATUS_ARRIVED_DMW = 'arrived_mdw';
    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_FAILURE = 'failure';
    
    protected function _construct()
    {
        $this->_init(self::RESOURCE);
    }
    
    public function getLastUpdatedAt($timezone=null)
    {
        return $this->_timezoneConverter('last_updated_at', $timezone);
    }
    
    protected function _timezoneConverter($key, $toTimeone)
    {
        $date = $this->getData($key);
        
        if(is_null($toTimeone)) {
            return $date;
        }
        
        $dateTime = new \DateTime($date, new \DateTimeZone(date_default_timezone_get()));
        $dateTime->setTimezone(new \DateTimeZone($toTimeone));
        return $dateTime->format('Y-m-d H:i:s');
    }
}