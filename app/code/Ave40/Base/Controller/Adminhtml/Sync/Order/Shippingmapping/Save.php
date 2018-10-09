<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018-5-7
 * Time: 下午 12:29
 */
//
namespace Ave40\Base\Controller\Adminhtml\Sync\Order\Shippingmapping;

class Save extends \Ave40\Base\Controller\Adminhtml\Sync\Order\Shippingmapping
{
    public function execute()
    {
        $postData = $this->getRequest()->getPostValue();
        $shipping = $postData['shipping'];
        $origin = $postData['origin'];
        $ids = $shipping['id'];
        $error = '';
        $warning = '';
        $shippingHelper = $this->_shippingmapping;
    
        foreach ($ids as $index => $id) {
            $change = false;
        
            foreach($shipping as $key => $row) {
                if($key == 'id') {
                    continue;
                }
            
                if($origin[$key][$index] != $shipping[$key][$index]) {
                    $change = true;
                }
            }
        
            if($change || $id == '') {
                /**
                 * @var $model \Ave40\Base\Model\Shippingmapping
                 */
                $model = $this->_shippingmappingModelFactory->create();
                $erpCode = $shipping['erp_code'][$index];
                $battery = $shipping['battery'][$index];
                $country = $shipping['country'][$index];
                $mgcode = $shipping['mgcode'][$index];
            
                if($id) {
                    $model->load($id);
                
                    if(!$model->getId()) {
                        continue;
                    }
                }
            
                $model->setErpCode($erpCode);
                $model->setCountry($country);
                $model->setBattery($battery);
                $model->setShippingCode($mgcode);
                // print_r([$id, $mgcode, $erpCode, $country, $battery]);
            
                try {
                    $this->_resourceModel->save($model);
                } catch (\Exception $e) {
                    if($e->getCode() == 23000) {
                        $countryText = $country?$country:'全部';
                        $batteryText = ['全部', '带电', '不带电'][$battery];
                        $mgText = $shippingHelper->getMethodName($mgcode);
                        $erpCodeText = $shippingHelper->getErpShippingName($erpCode);
                        $warning .= "已经存在相同记录: $mgText, $countryText, $batteryText, $erpCodeText<br>";
                    } else {
                        $error .= "{$e->getMessage()}<br>";
                    }
                }
            }
        }
    
        if($error) {
            $this->messageManager->addErrorMessage('保存时出现错误:');
            $this->messageManager->addErrorMessage($error);
        } else {
            if($warning) {
                $this->messageManager->addWarningMessage($warning);
            }
            $this->messageManager->addNoticeMessage('保存成功');
        }
    
        $this->_redirect('*/*/index');
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Ave40_Base::root_orderSync_shippingMapping');
    }
}
