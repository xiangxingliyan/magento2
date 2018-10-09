<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018-5-7
 * Time: 下午 12:29
 */
//
namespace Ave40\Base\Controller\Adminhtml\Sync\Order\Shippingmapping;

class Delete extends \Ave40\Base\Controller\Adminhtml\Sync\Order\Shippingmapping
{
    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
    
        if(empty($id)) {
            $this->messageManager->addErrorMessage('缺少id');
            return $this->_redirect('*/*/index');
        }
    
    
        /**
         * @var $model \Ave40\Base\Model\Shippingmapping
         */
        $model = $this->_shippingmappingModelFactory->create();
        $this->_resourceModel->load($model, $id);
    
        if($model->getId()) {
            try {
                $this->_resourceModel->delete($model);
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage("删除失败:{$e->getMessage()}");
                return $this->_redirect('*/*/index');
            }
        }
    
        $this->messageManager->addSuccessMessage('删除成功');
        $this->_redirect('*/*/index');
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Ave40_Base::root_orderSync_shippingMapping');
    }
}
