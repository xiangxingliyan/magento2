<?php

namespace Ave40\Base\Controller\Adminhtml\Sync\Order;

use Magento\Framework\App\Action\Context;

class ManualUpload extends \Magento\Framework\App\Action\Action
{
    protected $state;
    
    public function __construct(Context $context,
                                \Ave40\Base\Helper\Sync\Order\State $state)
    {
        parent::__construct($context);
        $this->state = $state;
    }
    
    public function execute()
    {
        $orderId = $this->getRequest()->getParam('id');
        if(empty($orderId)) {
            $this->messageManager->addError('缺少订单号');
            $this->_back();
            return;
        }
        
        $state = $this->state;
        
        $return = $state->manualUpload($orderId);
        
        if(isset($return['notice'])) {
            $this->messageManager->addNotice($return['notice']);
            $this->_back();
            return;
        }else {
            $this->messageManager->addError($return['error']);
            $this->_back();
            return;
        }
        
    }
    
    protected function _back()
    {
        echo "<script type='text/javascript'>history.back()</script>";
        die;
    }
}