<?php

namespace Ave40\Base\Helper\Sync\Order;

use Magento\Framework\App\Helper\Context;

class ActualAmountToAccount extends \Magento\Framework\App\Helper\AbstractHelper
{
    const LOGFILENAME = 'order/actual_amount';
    
    protected $_orderHelper;
    
    protected $_log;
    
    public function __construct(Context $context)
    {
        parent::__construct($context);
        $this ->_log = new \Ave40\Base\Model\Log(self::LOGFILENAME);
    }
    
    public function getActualAmount($order) {
        $default = 0;
    
        if($order->getActualAmountToAccount() != 0) {
            return $order->getActualAmountToAccount();
        }
        
        $log = $this->_log;
        
        //实际到账金额  vladdin 暂时只有paypal
        if ($order->getPayment()->getMethod() == 'paypal_express') {
            //paypal
            try {
                $payment = $order->getPayment();
                $transactionDetails = $payment
                    ->getMethodInstance()
                    ->setStore($order->getStoreId())
                    ->fetchTransactionInfo($payment, $payment->getLastTransId());
                
                $default = floatval($order->getGrandTotal()) - floatval($transactionDetails['FEEAMT']);
                
                $log->addInfo("订单同步,从Paypal获取实际到账金额({$order->getIncrementId()}):$default");
            } catch (\Exception $e) {
                $log->addError("订单同步,从Paypal获取实际到账金额异常({$order->getIncrementId()}):".$e->getMessage());
            }
        }
        
        if($default != 0) {
            $order->setActualAmountToAccount(floatval($default))->save();
        }
        
        return $default;
    }
    
    
}