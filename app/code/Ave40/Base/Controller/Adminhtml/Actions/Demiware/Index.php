<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/26 0026
 * Time: 19:22
 */

namespace Ave40\Base\Controller\Adminhtml\Actions\Demiware;

class Index extends \Ave40\Base\Controller\Adminhtml\Actions\Demiware
{
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        return $resultPage;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Ave40_Base::root_actionsConfig_DemiwareList');
    }
}