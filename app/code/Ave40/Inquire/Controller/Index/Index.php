<?php
/**
 * Copyright © 2017 Ihor Vansach (ihor@magefan.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Ave40\Inquire\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;

class Index extends Action
{
    protected $_json;
    protected $_urlInterface;
    protected $_dirRender;
    protected $_dirList;
    protected $_emailer;
    protected $_recordFactory;
    protected $_emailConfig;
    protected $log;
    protected $templateHelper;
    
    use \Ave40\Base\Traits\JsonResponse;
    
    public function __construct(Context $context,
                                \Ave40\Base\Helper\Json $json,
                                \Magento\Framework\Module\Dir\Reader $dirRender,
                                \Magento\Framework\Filesystem\DirectoryList $dirList,
                                \Ave40\Base\Helper\Email $emailer,
                                \Ave40\Inquire\Model\RecordsFactory $recordsFactory,
                                \Ave40\Inquire\Helper\Config $config,
                                \Ave40\Base\Helper\Template $template
    )
    {
        parent::__construct($context);
        $this->log = new \Ave40\Base\Model\Log('inquire');
        $this->_json = $json;
        $this->_urlInterface = $context->getUrl();
        $this->_dirRender = $dirRender;
        $this->_dirList = $dirList;
        $this->_emailer = $emailer;
        $this->_recordFactory = $recordsFactory;
        $this->_emailConfig = $config;
        $this->templateHelper = $template;
    }
    
    public function execute()
    {
        date_default_timezone_set('PRC');
        
        $params = $this->getRequest()->getParams();
        $name = trim($params['name']);
        $email = trim($params['email']);
        $mobile = trim($params['mobile']);
        $comment = trim($params['comment']);
        $ip = $this->getRequest()->getClientIp();
        $referer = $_SERVER['HTTP_REFERER'];
        $userAgent = $_SERVER['HTTP_USER_AGENT'];
        
        if(empty($name)) {
            $this->respondJsonFail('Missing name');
        }
    
        if(empty($email)) {
            $this->respondJsonFail('Missing email address');
        }
    
        if(empty($comment)) {
            $this->respondJsonFail('Missing message');
        }
        
        if(strlen($comment) > 200) {
            $this->respondJsonFail('Message length cannot exceed 200 characters');
        }
    
        $record = $this->_recordFactory->create();
        $record->getResource()->beginTransaction();
        $time = date('Y-m-d H:i:s');
        
        $record->setSenderName($name)
            ->setSenderEamil($email)
            ->setContent($comment)
            ->setSenderIp($ip)
            ->setReferer($referer)
            ->setSenderUseragent($userAgent)
            ->setSenderPhone($mobile)
            ->setCreatedAt($time)
        ;
    
        try {
            $record->save();
        } catch (\Exception $e) {
            $this->log->addBothFail("Save record fail: {$e->getMessage()}");
            $this->respondJsonFail('Server Error', 'SAVED FAIL');
        }
        
        $content = $this->templateHelper->render('Ave40_Inquire::inquire-email-template.phtml', [
            'email' => $email,
            'name' => $name,
            'content' => $comment,
            'phone' => $mobile,
            'ip' => $ip,
            'referer' => $referer,
            'time' => $time
        ]);
        
        $siteHost = $this->getRequest()->getHttpHost();
        
        try {
            $emailReq = new \Ave40\Base\Entities\Email\SendRequest();
            $emailReq->setContent($content)
                ->setSubject("Inquire from $siteHost")
                ->addRecipients($this->_emailConfig->getRecipients())
                ->addBCC($this->_emailConfig->getBcc())
                ->addCC($this->_emailConfig->getCC())
                ->addReplys($email)
            ;
            $this->_emailer->send($emailReq);
            $record->getResource()->commit();
            $this->log->addInfoSuccess("Send OK: {$emailReq->getLog()}");
        } catch (\Exception $e) {
            $this->log->addBothFail("Send email fail: {$e->getMessage()}");
            $record->getResource()->rollBack();
            $this->respondJsonFail('Server Error', 'SEND FAIL');
        }
        
        $this->respondJsonSuccess("Your inquiry was submitted and will be responded to as soon as possible. Thank you for contacting us");
    }
    
}