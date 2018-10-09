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
class SearchViewPage implements ObserverInterface
{
    protected $_page;
    
    public function __construct(\Magento\Framework\View\Result\Page $page)
    {
        $this->_page = $page;
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
        $config = $this->_page->getConfig();
        $q = isset($_GET['q']) ? $_GET['q'] : '';
        $config->setMetadata('keywords', $q);
        $config->setMetadata('description', "Find the best $q at vladdinvapor.com");
        return $this;
    }
}
