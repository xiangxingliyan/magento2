<?php

namespace Ave40\Base\Controller\Adminhtml\Sync\Order;

use Magento\Framework\App\Action\Context;

class SaveActualAmount extends \Magento\Framework\App\Action\Action
{
    protected $_order;
    
    protected $_json;
    
    public function __construct(Context $context,
                                \Magento\Sales\Model\Order $order,
                                \Ave40\Base\Helper\Json $json)
    {
        parent::__construct($context);
        $this->_order = $order;
        $this->_json = $json;
    }
    
    public function execute()
    {
        $amount = $this->getRequest()->getParam('amount');
        $orderId = $this->getRequest()->getParam('orderId');
        $apiJson = $this->_json;
    
        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->_order->load($orderId);
    
        if(!$order->getId()) {
            $apiJson->returnFailMessage('订单不存在!');
            return;
        }
    
        $order->setActualAmountToAccount(floatval($amount));
    
        try {
            $order->save();
        } catch (Exception $e) {
            $apiJson->returnFailMessage($e->getMessage());
        }
    
        $apiJson->returnSuccess(['amount' => $order->getActualAmountToAccount()], 'ok');
    }
}