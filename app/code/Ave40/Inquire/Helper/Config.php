<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018-08-27 0027
 * Time: 下午 06:18
 */
namespace Ave40\Inquire\Helper;
use Ave40\Base\Entities\Email\SendRequest;
use Ave40\Base\Lib\PHPMailer\Exception;
use Magento\Framework\App\Helper\Context;

class Config extends \Magento\Framework\App\Helper\AbstractHelper
{
    public function __construct(
        Context $context
    )
    {
        parent::__construct($context);
    }
    
    public function getRecipients() {
        return $this->_getFromConfig('ave40_inquire_setting/email/recipients');
    }
    
    public function getCC() {
        return $this->_getFromConfig('ave40_inquire_setting/email/cc');
    }
    
    public function getBcc() {
        return $this->_getFromConfig('ave40_inquire_setting/email/bcc');
    }
    
    protected function _getFromConfig($key) {
        $value = $this->scopeConfig->getValue($key);
        $value = array_filter(array_map('trim', explode("\n", $value)));
        $emails = [];
        
        foreach ($value as $row) {
           list($email, $name) = explode(',', $row, 2);
           $email = trim($email);
           $name = trim($name);
           
           if(empty($name)) {
               $name = $email;
           }
           
           $emails [$email] = $name;
        }
        
        return $emails;
    }
}