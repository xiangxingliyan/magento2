<?php
/**
 * Copyright © 2017 Ihor Vansach (ihor@magefan.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Ave40\Base\Controller\Checkout;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;

class Deal extends Action
{
    protected $resultJsonFactory;
    protected $formKeyValidator;
    public function __construct(
        Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
    ){
        $this->resultJsonFactory = $resultJsonFactory;
        $this->formKeyValidator = $formKeyValidator;
        parent::__construct($context);
    }
    
    /**
     * View blog homepage action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $result */
        $resultJsonFactory = $this->resultJsonFactory->create();
        $postdata = $this->getRequest()->getPost();
        $formKey = $postdata['form_key'];
        if (!$this->formKeyValidator->validate($this->getRequest())) {
            $result['message'] = 'Form key is invalid';
            $result['status_code'] = 0;
            return $resultJsonFactory->setData($result);
        }
        
        $orderIncrementId = $postdata['orderIncrementId'];
        $telephone = trim($postdata['telephone']);
        if(empty($telephone)) {
            $result['message'] = 'Phone number can not be empty!';
            $result['status_code'] = 0;
            return $resultJsonFactory->setData($result);
        }
        if(empty($orderIncrementId)) {
            $result['message'] = 'Order Id invalid!';
            $result['status_code'] = 0;
            return $resultJsonFactory->setData($result);
        }
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $order = $objectManager->create('Magento\Sales\Model\Order')->loadByIncrementId($orderIncrementId);
    
        try {
            $billingAddress["telephone"] = $telephone;
            $shiipingAddress["telephone"] = $telephone;
            $order->getBillingAddress()->addData($billingAddress)->save();
            $order->getShippingAddress()->addData($shiipingAddress)->save();
            $result['message'] = 'Thank you! Your phone number has been saved.';
            $result['status_code'] = 1;
        }
        catch (Exception $e) {
            $result['status_code'] = 0;
            $result['error'] = 'Sorry, we failed to save your phone number.';
        }
    
        return $resultJsonFactory->setData($result);
    }
}
