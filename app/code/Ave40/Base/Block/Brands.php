<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017-10-20
 * Time: 下午 05:57
 */
namespace Ave40\Base\Block;

use Magento\Framework\View\Element\Template;

class Brands extends Template
{
    protected $_template="Own_Base::brand-list.phtml";
    
    public function getBrands()
    {
        $_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        /* @var $categoryCollection \Magento\Catalog\Model\ResourceModel\Category\Collection  */
        $categoryCollection = $_objectManager->create('Magento\Catalog\Model\Category')->getCollection();
        $categoryCollection->addAttributeToFilter('url_key', 'brands');
        
        /* @var $brandsCategory \Magento\Catalog\Model\Category  */
        $brandsCategory = $categoryCollection->getFirstItem();
        $subCategorys = $brandsCategory->getCategories($brandsCategory->getId());
    
        $brands = $_objectManager->create('Magento\Catalog\Model\Category')->getCollection()
            ->addAttributeToFilter('entity_id', ['in' => array_keys($subCategorys->getNodes())])
            ->addAttributeToFilter('is_active', 1)
            ->addAttributeToSelect('*')
            ->getItems();
        
        return $brands;
    }
}