<?php
namespace Ave40\Base\Helper;
use Ave40\Base\Entities\Email\SendRequest;
use Ave40\Base\Lib\PHPMailer\Exception;
use Magento\Framework\App\Helper\Context;

class Email extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $log;
    
    public function __construct(
        Context $context
    ) {
        $this->log = new \Ave40\Base\Model\Log('ave40_base_email');
        parent::__construct($context);
    }
    
    /**
     * @param SendRequest $request
     * @return bool
     * @throws \Ave40\Base\Lib\PHPMailer\Exception
     * @throws \Exception
     */
    public function send(SendRequest $request) {
        $mailer = new \Ave40\Base\Lib\PHPMailer\PHPMailer;
        $mailer->isSMTP();
        $mailer->CharSet = "utf8";
        $mailer->SMTPAuth = true;
        $mailer->Host = $this->_getSmtpHost();
        $mailer->Port = $this->_getSmtpPort();
        $mailer->Username = $this->_getSmtpAccount();
        $mailer->Password = $this->_getSmtpPassword();
        $mailer->SMTPSecure = $this->_getSmtpHttps() == "none" ? '' : $this->_getSmtpHttps();
        $mailer->isHTML(true);
        
        $fromAddress = $request->getFromAddress() ? $request->getFromAddress() : $this->_getSmtpAccount();
        $fromName = $request->getFromName() ? $request->getFromName() : $fromAddress;
        
        $mailer->setFrom($fromAddress, $fromName);
        $mailer->Subject = $request->getSubject();
        $mailer->Body = $request->getContent();
        
        if(empty($request->getRecipients())) {
            throw new Exception('Missing recipient');
        }
        
        foreach ((array)$request->getRecipients() as $email  => $name) {
            $mailer->addAddress($email, $name);
        }
        
        foreach ((array)$request->getCC() as $email => $name) {
            $mailer->addCC($email, $name);
        }
    
        foreach ((array)$request->getBCC() as $email => $name) {
            $mailer->addBCC($email, $name);
        }
    
        // $mailer->SMTPDebug = true;
        try {
            if(!$mailer->send()) {
                throw new Exception($mailer->ErrorInfo);
            }
        } catch (\Exception $e) {
            $logAry = ['h' => $mailer->Host, 'p' => $mailer->Port, 'u' => $mailer->Username, 'secure' => $mailer->SMTPSecure];
            $this->log->addBothFail("Email Sent Fail: Message={$e->getMessage()}; Request={$request->getLog()}; Sender=" . json_encode($logAry));
            throw $e;
        }
    
        $this->log->addInfoSuccess("Email sent: Request={$request->getLog()}");
        return true;
    }
    
    protected function _getSmtpAccount() {
        return $this->scopeConfig->getValue('system/gmailsmtpapp/username');
    }
    
    protected function _getSmtpPort() {
        return $this->scopeConfig->getValue('system/gmailsmtpapp/smtpport');
    }
    
    protected function _getSmtpHost() {
        return $this->scopeConfig->getValue('system/gmailsmtpapp/smtphost');
    }
    
    protected function _getSmtpPassword() {
        return $this->scopeConfig->getValue('system/gmailsmtpapp/password');
    }
    
    protected function _getSmtpHttps() {
        return $this->scopeConfig->getValue('system/gmailsmtpapp/ssl');
    }
    
    public function _getSalesFromName() {
        return $this->scopeConfig->getValue('trans_email/ident_sales/name');
    }
    
    public function _getSalesFormAddress() {
        return $this->scopeConfig->getValue('trans_email/ident_sales/email');
    }
}