<?php

namespace Ave40\Base\Block\Adminhtml\Order\View;

class Info extends \Magento\Sales\Block\Adminhtml\Order\View\Info
{
    protected $_template = "Ave40/Base/view/adminhtml/templates/Order/View/info.phtml";
    
    protected $_actualAmount;
    
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Helper\Admin $adminHelper,
        \Magento\Customer\Api\GroupRepositoryInterface $groupRepository,
        \Magento\Customer\Api\CustomerMetadataInterface $metadata,
        \Magento\Customer\Model\Metadata\ElementFactory $elementFactory,
        \Magento\Sales\Model\Order\Address\Renderer $addressRenderer,
        \Ave40\Base\Helper\Sync\Order\ActualAmountToAccount $actualAmountToAccount,
        array $data = [])
    {
        parent::__construct($context, $registry, $adminHelper, $groupRepository, $metadata, $elementFactory, $addressRenderer, $data);
        $this->_actualAmount = $actualAmountToAccount;
    }
    
    public function _toHtml()
    {
        return parent::_toHtml();
    }
    
    public function getQueryStateUrl()
    {
        return $this->getUrl('ave40_base/sync_order/queryOrderSyncState');
    }
    
    public function getSaveActualAmountUrl()
    {
        return $this->getUrl('ave40_base/sync_order/saveActualAmount');
    }
    
    public function getActualAmount() {
        $order = $this->getOrder();
        return $this->_actualAmount->getActualAmount($order);
        //return 0;
    }
}