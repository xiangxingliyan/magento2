<?php

namespace Ave40\Inquire\Model;

use Magento\Framework\Model\AbstractModel;

/**
 * getSenderEamil
 * @method getSenderEamil
 * @method $this setSenderEamil($value)
 * @method getSenderName
 * @method $this setSenderName($value)
 * @method getSenderPhone
 * @method $this setSenderPhone($value)
 * @method getSenderUseragent
 * @method $this setSenderUseragent($value)
 * @method getSenderIp
 * @method $this setSenderIp($value)
 * @method getRecipientMails
 * @method $this setRecipientMails($value)
 * @method getReferer
 * @method $this setReferer($value)
 * @method getContent
 * @method $this setContent($value)
 * @method getUserType
 * @method $this setUserType($value)
 * @method getCreatedAt
 * @method $this setCreatedAt($value)
 * @method getReadingTime
 * @method $this setReadingTime($value)
 * @method getSite
 * @method $this setSite($value)
 */
class Records extends AbstractModel
{
    const RESOURCE = ResourceModel\Records::class;
    
    /**
     * Initialize resource
     *
     * @return void
     */
    public function _construct() {
        $this->_init(self::RESOURCE);
    }
}
