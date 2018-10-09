<?php
namespace Ave40\Base\Block\Order\Email;

use Magento\Framework\View\Element\Template;
use Magento\Sales\Model\Order\Creditmemo\Item as CreditmemoItem;
use Magento\Sales\Model\Order\Invoice\Item as InvoiceItem;
use Magento\Sales\Model\Order\Item as OrderItem;

class Items extends \Magento\Framework\View\Element\Template
{
    
    protected $_coreRegistry;
    
    public function __construct(Template\Context $context,
                                array $data = [],
                                \Magento\Framework\Registry $registry)
    {
        parent::__construct($context, $data);
        $this->_coreRegistry = $registry;
    }
    
    /**
     * Retrieve current order model instance
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        if ($this->hasOrder()) {
            return $this->getData('order');
        }
        
        if($this->_coreRegistry->registry('current_order')) {
            return $this->_coreRegistry->registry('current_order');
        }else {
            return $this->_coreRegistry->registry('order');
        }
        
    }
    
}