<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018-5-7
 * Time: 下午 12:29
 */
//
namespace Ave40\Inquire\Controller\Adminhtml\Records;

class Index extends \Ave40\Inquire\Controller\Adminhtml\Records
{
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        return $resultPage;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Ave40_Base::root_inquire_records');
    }
}
