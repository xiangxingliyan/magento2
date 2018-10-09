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
class CategoryViewPage implements ObserverInterface
{
    /**
     * Check captcha on user login page
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @throws NoSuchEntityException
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /* @var \Magento\Catalog\Model\Product\Interceptor $product  */
        $object = $observer->getCategory();
        $page = isset($_GET['p']) ? $_GET['p'] : null;
        
        if($page > 1) {
            $object->setMetaTitle("page $page | " . $object->getMetaTitle());
            $object->setMetaDescription("page $page | " . $object->getMetaDescription());
        }
        
        return $this;
    }
}
