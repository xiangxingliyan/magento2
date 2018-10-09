<?php
namespace Ave40\Erpapi\Model;
use Ave40\Erpapi\Api\ProductInfoInterface;

class ProductInfo implements ProductInfoInterface
{
    
    protected $_collection;
    protected $_productRepository;
    
    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collection,
        \Magento\Catalog\Model\ProductRepository $productRepository
    )
    {
        $this->_collection = $collection;
        $this->_productRepository = $productRepository;
    }

/**
     * Returns greeting message to user
     *
     * @api
     * @param string $name Users name.
     * @return string Greeting message with users name.
     */
    public function ProductInfo($itemnum)
    {
        $productCollection = $this->_collection->create()
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('itemnum', $itemnum)
            ->load();
        $productData = $productCollection->getFirstItem()->getData();
        return json_encode($productData);
    }
}