<?php
namespace Ave40\Base\Helper;
use Magento\Framework\App\Helper\Context;

/**
 * 同步订单到中间件
 */

class Mdw extends \Magento\Framework\App\Helper\AbstractHelper
{
    //订单同步的配置
    const CK_ENABLE_ORDER_SYNC = 'ave40_base_config_erp/general/order_sync_enabled';
    
    private $records;
    
    public function __construct(Context $context/*,\Ave40\Base\Model\Mdw\Records $records*/)
    {
        /*$this->records = $records;*/
        parent::__construct($context);
    }
    
    public function isOrderSyncEnabled()
    {
        return $this->getconfig(self::CK_ENABLE_ORDER_SYNC);
    }
    
    public function getconfig($config)
    {
        return $this->scopeConfig->getValue(
            $config,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    
    public function orderCanBeSync(\Magento\Sales\Model\Order $order) {
        
        if(in_array($order->getState(), [\Magento\Sales\Model\Order::STATE_CANCELED, \Magento\Sales\Model\Order::STATE_CLOSED])) {
            return false;
        }
        
        //看看有没有上传过的记录
        $record = $this->getSyncRecord(\Ave40\Base\Model\Mdw\Records::TYPE_ORDER_UPLOAD, $order->getId());
    
        if(!$record->getId()) {
            return true;
        }
    
        
        if($record->getStatus() == \Ave40\Base\Model\Mdw\Records::STATUS_FAILURE) {
            return true;
        }
    
        if($order->getState() == \Magento\Sales\Model\Order::STATE_PROCESSING &&
            in_array($record->getStatus(), [\Ave40\Base\Model\Mdw\Records::STATUS_PENDING, \Ave40\Base\Model\Mdw\Records::STATUS_ARRIVED_DMW])) {
            return true;
        }
        
        return false;
    }
    
    public function getSyncRecord($type,$entityId) {
        
        $collection = $this->records->getCollection();
        $collection->addFieldToFilter('entity_id', $entityId);
        $collection->addFieldToFilter('type', $type)->load();
        
        return $collection->getFirstItem();
    }
}