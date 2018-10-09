<?php

namespace Ave40\Base\Block\Adminhtml\Order\View;

class OrderSyncInfo extends \Magento\Sales\Block\Adminhtml\Order\AbstractOrder
{
    protected $_template = 'Ave40/Base/view/adminhtml/templates/Order/View/syncinfo.phtml';
    /**
     * 获取当前的订单对象
     */
    public function getCurrentOrder() {
        return $this->getOrder();
    }
    
    public function getQueryStateUrl()
    {
        return $this->getUrl('ave40_base/adminhtml_sync_order_order/queryOrderSyncState');
    }
    
    public function getSaveActualAmountUrl()
    {
        return $this->getUrl('ave40_base/adminhtml_sync_order_order/saveActualAmount');
    }
    
}