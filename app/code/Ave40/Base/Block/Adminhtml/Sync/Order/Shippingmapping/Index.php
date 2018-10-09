<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017-10-20
 * Time: 下午 05:57
 */
namespace Ave40\Base\Block\Adminhtml\Sync\Order\Shippingmapping;
use Magento\Framework\View\Element\Template;

class Index extends Template
{
    protected $_template = 'Sync/Order/Shippingmapping/index.phtml';
    
    protected $_shippingmappingHelper;
    
    public function __construct(
        Template\Context $context,
        array $data = [],
        \Ave40\Base\Helper\Sync\Order\Shippingmapping $shippingmappingHelper
    )
    {
        parent::__construct($context, $data);
        $this->_shippingmappingHelper = $shippingmappingHelper;
    }
    
    /**
     * @return \Ave40\Base\Helper\Sync\Order\Shippingmapping
     */
    public function getShippingmappingHelper() {
        return $this->_shippingmappingHelper;
    }
    
}