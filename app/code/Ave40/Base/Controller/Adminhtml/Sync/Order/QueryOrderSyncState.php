<?php

namespace Ave40\Base\Controller\Adminhtml\Sync\Order;

use Magento\Framework\App\Action\Context;

class QueryOrderSyncState extends \Magento\Framework\App\Action\Action
{
    protected $_json;
    
    protected $state;
    
    public function __construct(Context $context,
                                \Ave40\Base\Helper\Json $json,
                                \Ave40\Base\Helper\Sync\Order\State $state)
    {
        parent::__construct($context);
        $this->_json = $json;
        $this->state = $state;
    }
    
    public function execute()
    {
        $orderId = $this->getRequest()->getParam('orderId');
        $apiJson = $this->_json;
        if(empty($orderId)) {
            $apiJson->returnFail('缺少订单号');
        }
        
        $state = $this->state;
        
        $return = $state->queryOrderSyncState($orderId);
        if(is_array($return)) {
            $apiJson->returnSuccess($return);
        }else {
            $apiJson->returnFail($return);
        }
    }
}