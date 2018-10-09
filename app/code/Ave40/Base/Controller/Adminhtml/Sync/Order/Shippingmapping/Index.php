<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018-5-7
 * Time: 下午 12:29
 */
//
namespace Ave40\Base\Controller\Adminhtml\Sync\Order\Shippingmapping;

class Index extends \Ave40\Base\Controller\Adminhtml\Sync\Order\Shippingmapping
{
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        return $resultPage;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Ave40_Base::root_orderSync_shippingMapping');
    }
}
