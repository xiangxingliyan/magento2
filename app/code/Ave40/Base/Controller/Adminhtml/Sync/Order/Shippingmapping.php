<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018-5-7
 * Time: 下午 12:29
 */
//
namespace Ave40\Base\Controller\Adminhtml\Sync\Order;

abstract class Shippingmapping extends \Magento\Backend\App\Action
{
    /** @var \Magento\Framework\View\Result\PageFactory  */
    protected $resultPageFactory;
    /** @var \Magento\Framework\Filesystem  */
    protected $_fileSystem;
    /** @var \Ave40\Base\Helper\Sync\Order\Shippingmapping  */
    protected $_shippingmapping;
    /** @var \Ave40\Base\Model\ShippingmappingFactory  */
    protected $_shippingmappingModelFactory;
    /** @var \Ave40\Base\Model\ResourceModel\Shippingmapping\CollectionFactory  */
    protected $_shippingmappingCollectionFactory;
    /** @var \Ave40\Base\Model\ResourceModel\Shippingmapping  */
    protected $_resourceModel;
    
    
    /**
     * Constructor
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Filesystem $_filesystem
     * @param \Ave40\Base\Helper\Sync\Order\Shippingmapping $shippingmapping
     * @param \Ave40\Base\Model\ShippingmappingFactory $shippingmappingModelFactory
     * @param \Ave40\Base\Model\ResourceModel\Shippingmapping $shippingmappingResource
     * @param \Ave40\Base\Model\ResourceModel\Shippingmapping\CollectionFactory $shippingmappingCollectionFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Filesystem $_filesystem,
        \Ave40\Base\Helper\Sync\Order\Shippingmapping $shippingmapping,
        \Ave40\Base\Model\ShippingmappingFactory $shippingmappingModelFactory,
        \Ave40\Base\Model\ResourceModel\Shippingmapping $shippingmappingResource,
        \Ave40\Base\Model\ResourceModel\Shippingmapping\CollectionFactory $shippingmappingCollectionFactory
    ) {
        parent::__construct($context);
        
        $this->resultPageFactory = $resultPageFactory;
        $this->_fileSystem = $_filesystem;
        $this->_shippingmapping = $shippingmapping;
        $this->_shippingmappingModelFactory = $shippingmappingModelFactory;
        $this->_resourceModel = $shippingmappingResource;
        $this->_shippingmappingCollectionFactory = $shippingmappingCollectionFactory;
    }
  
    protected function _redirectToIndex() {
        return $this->_redirect('*/*/index');
    }
    
    protected function _redirectToEdit($id=null) {
        if($id) {
            return $this->_redirect('*/*/edit', ['id' => $id]);
        } else {
            return $this->_redirect('*/*/edit');
        }
    }
}
