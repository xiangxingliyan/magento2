<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018-5-7
 * Time: 下午 12:29
 */
//
namespace Ave40\Base\Controller\Adminhtml\Sync\Order\Shippingmapping;

class Copy extends \Ave40\Base\Controller\Adminhtml\Sync\Order\Shippingmapping
{
    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     * @throws \Exception
     */
    public function execute()
    {
        $origin = $this->getRequest()->getParam('origin');
        $target = $this->getRequest()->getParam('target');
    
        if(empty($origin)) {
            $this->messageManager->addErrorMessage('缺少复制物流');
            return $this->_redirect('*/*/index');
        }
    
        if(empty($target)) {
            $this->messageManager->addErrorMessage('缺少目标物流');
            return $this->_redirect('*/*/index');
        }
    
        if($origin == $target) {
            $this->messageManager->addErrorMessage('复制物流和目标物流不可相同');
            return $this->_redirect('*/*/index');
        }
    
        /** @var \Ave40\Base\Model\ResourceModel\Shippingmapping\Collection $targetCollection */
        $targetCollection = $this->_shippingmappingCollectionFactory->create();
        // $targetCollection = Mage::getModel('ave40_base/erp_shippingmapping')->getCollection();
        $targetCollection->addFieldToFilter('shipping_code', $target);
        $targetCollection->load();
        $deleteCount = 0;
        
        /** @var \Ave40\Base\Model\Shippingmapping $row */
        foreach ($targetCollection as $row) {
            $this->_resourceModel->delete($row);
            $deleteCount ++;
        }
    
        /** @var \Ave40\Base\Model\ResourceModel\Shippingmapping\Collection $originCollection */
        $originCollection = $this->_shippingmappingCollectionFactory->create();
        $originCollection->addFieldToFilter('shipping_code', $origin);
        $originCollection->load();
        $successCount = 0;
        $errorCount = 0;
        $error = [];
    
    
        /** @var \Ave40\Base\Model\Shippingmapping $row */
        foreach($originCollection as $row) {
            /** @var \Ave40\Base\Model\Shippingmapping $model */
            $model = $this->_shippingmappingModelFactory->create();
            $model->setShippingCode($target)
                ->setBattery($row->getBattery())
                ->setErpCode($row->getErpCode())
                ->setCountry($row->getCountry())
                ->setLastUpdatedAt(date('Y-m-d H:i:s'))
            ;
        
            try {
                $this->_resourceModel->save($model);
                $successCount ++;
            } catch (\Exception $e) {
                $error [] = $e->getMessage();
                $errorCount ++;
            }
        }
    
        $deleteCountStr = $deleteCount>0 ? ", 删除目标数据 $deleteCount 条":'';
    
        if($errorCount == 0) {
            $this->messageManager->addSuccessMessage("复制完成, 已复制 $successCount 条$deleteCountStr");
        } else if($successCount>0) {
            $this->messageManager->addWarningMessage("复制完成, 已复制 $successCount 条, 失败 $errorCount 条$deleteCountStr");
        } else {
            $this->messageManager->addErrorMessage("复制失败");
        }
    
        if(!empty($error)) {
            foreach($error as $e) {
                $this->messageManager->addErrorMessage($e);
            }
        }
    
        $this->_redirect('*/*/index');
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Ave40_Base::root_orderSync_shippingMapping');
    }
}
