<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018-08-24 0024
 * Time: 下午 06:21
 */

namespace Ave40\Base\Entities\Email;

use Magento\Framework\DataObject;

/**
 * Class SendRequest
 * @package Ave40\Base\Entities\Email
 *
 * @method getRecipients
 * @method getCC
 * @method getBCC
 * @method getSubject
 * @method $this setSubject($subject)
 * @method getContent
 * @method $this setContent($content)
 * @method getFromName
 * @method getFromAddress
 */
class SendRequest extends DataObject  {
    const KEY_RECIPIENTS = 'recipients';
    const KEY_CC = 'c_c';
    const KEY_BCC = 'b_c_c';
    const KEY_REPLYS = 'replys';
    const KEY_SUBJECT = 'subject';
    const KEY_FROM_NAME = 'from_name';
    const KEY_FROM_ADDRESS = 'from_address';
    
    /**
     * 添加收件人
     * @param $emails
     * @param null $name
     * @return $this
     */
    public function addRecipients($emails, $name=null) {
        return $this->_addEmail(self::KEY_RECIPIENTS, $emails, $name);
    }
    
    /**
     * 判断收件人是否存在
     * @param $email
     * @return bool
     */
    public function hasRecipients($email) {
        return $this->_hasEmail(self::KEY_RECIPIENTS, $email);
    }
    
    /**
     * 移除收件人
     * @param $email
     * @return SendRequest
     */
    public function removeRecipients($email) {
        return $this->_removeEmail(self::KEY_RECIPIENTS, $email);
    }
    
    /**
     * 添加抄送人
     * @param $emails
     * @param null $name
     * @return $this
     */
    public function addCC($emails, $name=null) {
        return $this->_addEmail(self::KEY_CC, $emails, $name);
    }
    
    /**
     * 判断抄送人是否存在
     * @param $email
     * @return bool
     */
    public function hasCC($email) {
        return $this->_hasEmail(self::KEY_CC, $email);
    }
    
    /**
     * 移除抄送人
     * @param $email
     * @return SendRequest
     */
    public function removeCC($email) {
        return $this->_removeEmail(self::KEY_CC, $email);
    }
    
    /**
     * 添加秘密抄送人
     * @param $emails
     * @param null $name
     * @return $this
     */
    public function addBCC($emails, $name=null) {
        return $this->_addEmail(self::KEY_BCC, $emails, $name);
    }
    
    /**
     * 判断秘密抄送人是否存在
     * @param $email
     * @return bool
     */
    public function hasBCC($email) {
        return $this->_hasEmail(self::KEY_BCC, $email);
    }
    
    /**
     * 移除秘密抄送人
     * @param $email
     * @return SendRequest
     */
    public function removeBCC($email) {
        return $this->_removeEmail(self::KEY_BCC, $email);
    }
    
    /**
     * 添加回复人
     * @param $emails
     * @param null $name
     * @return $this
     */
    public function addReplys($emails, $name=null) {
        return $this->_addEmail(self::KEY_REPLYS, $emails, $name);
    }
    
    /**
     * 判断秘密抄送人是否存在
     * @param $email
     * @return bool
     */
    public function hasReplys($email) {
        return $this->_hasEmail(self::KEY_REPLYS, $email);
    }
    
    /**
     * 移除秘密抄送人
     * @param $email
     * @return SendRequest
     */
    public function removeReplys($email) {
        return $this->_removeEmail(self::KEY_REPLYS, $email);
    }
    
    public function setFromName($name) {
        $this->setData(self::KEY_FROM_NAME, $name);
    }
    
    public function setFromAddress($email) {
        $this->setData(self::KEY_FROM_ADDRESS, $email);
    }
    
    protected function _addEmail($key, $emails, $name) {
        if(is_array($emails)) {
            foreach ($emails as $email => $name) {
                $email = trim($email);
            
                if(is_numeric($email)) {
                    $email = $name;
                }
            
                $email = trim($email);
            
                if(empty($email)) {
                    continue;
                }
            
                $this->_addEmail($key, $email, $name);
            }
        
            return $this;
        } else {
            $email = trim($emails);
            $name = $name ? $name : $emails;
            $this->_data [$key] [$email]= $name;
            return $this;
        }
    }
    
    protected function _hasEmail($key, $email) {
        return isset($this->_data[$key][$email]);
    }
    
    protected function _removeEmail($key, $email) {
        if($this->_hasEmail($key, $email)) {
            unset($this->_data[$key][$email]);
        }
        
        return $this;
    }
    
    public function getLog() {
        return json_encode([
            'recipients' => $this->getRecipients(),
            'bcc' => $this->getBCC(),
            'cc' => $this->getCC(),
            'subject' => $this->getSubject(),
            'from' => $this->getFromAddress()
        ]);
    }
}