<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Ave40\Base\Observer\Seo;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProductViewPage implements ObserverInterface
{
    protected $_pricingHelper ;
    
    public function __construct(\Magento\Framework\Pricing\Helper\Data $pricingHelper)
    {
        $this->_pricingHelper = $pricingHelper;
    }
    
    /**
     * Check captcha on user login page
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @throws NoSuchEntityException
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /* @var \Magento\Catalog\Model\Product\Interceptor $object  */
        $object = $observer->getProduct();
        $price = $object->getFinalPrice();
        $price = $price ? $price : $object->getPrice();
        $productPrice = $this->_pricingHelper->currency($price, true, false);
        
        //产品名 - 价格 - sku
        /*
         * $object->setMetaTitle(sprintf("%s - %s - %s",
            $object->getName(),
            $productPrice,
            $object->getSku()
            ));
        */
        
        
        /*$object->setMetaKeyword($object->getName());*/
        
        //Buy hot sale ｛产品名｝on ecigfit.com. Inquiry about the lowest price!
//        $object->setMetaDescription(sprintf('Buy Hot Sale %s on vladdinvapor.com. Inquiry about the lowest price!', $object->getName()));

        return $this;
    }
}
