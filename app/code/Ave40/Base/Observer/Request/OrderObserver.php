<?php

namespace Ave40\Base\Observer\Request;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Session\ini_set;
use Magento\TestFramework\Event\Magento;

class OrderObserver implements ObserverInterface
{
    protected $_orderHelper;
    
    public function __construct(\Ave40\Base\Helper\Sync\Order $orderHelper)
    {
        $this->_orderHelper = $orderHelper;
    }
    
    //订单状态改变之后同步到crm
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        // \Magento\Sales\Model\Order $order
        $order = $observer->getEvent()->getOrder();
        $this->_orderHelper->SyncOrder($order);
    }
    
    
    
}