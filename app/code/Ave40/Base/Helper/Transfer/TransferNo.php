<?php

namespace Ave40\Base\Helper\Transfer;

use Magento\Framework\App\Helper\Context;

class TransferNo extends \Magento\Framework\App\Helper\AbstractHelper
{
    const LOGFILENAME = 'order/update_order_transger_no';
    
    protected $EXPRESS_QUERY_ADDRESS = [
        '豪速通' => 'http://59.42.10.40:83/webservice/track.aspx?sid={{no}}',
        '豪速通_转单号' => 'https://t.17track.net/en#nums={{no}}'
    ];
    
    protected $_log;
    protected $_orderApi;
    
    public function __construct(Context $context,
                                \Ave40\Base\Model\Api\OrderApi $orderApi
    )
    {
        parent::__construct($context);
        $this->_log = new \Ave40\Base\Model\Log(self::LOGFILENAME);
        $this->_orderApi = $orderApi;
    }
    
    public function updateOrderTransferNo(\Magento\Sales\Model\Order $order, $type)
    {
        if(!empty($order->getId())) {
            $orderId = $order->getId();
            $head = "orderID[$orderId]:";
            $head .= $order->getCustomerEmail() . ':';
            $this->_log->addInfo($head . 'start');
            
            if(in_array($order->getLogisticsProvider(), ['豪速通'])) {
                $this->_log->addInfo($head . '获取转单号前->' . $order->getTransferNo());
                $transferNo = $this->fetchOrderTransferNo($order);
                $this->_log->addInfo($head . '获取转单号后->' . $transferNo);
    
                if($transferNo) {
                    $logPrefix = "订单发货查询({$order->getIncrementId()})";
                    //发货
                    try {
                        $this->_orderApi->_shipped($order,$transferNo);
                        $this->_log->addInfo($logPrefix.': 订单发货成功，获取转单号成功');
                    } catch (\Exception $e) {
                        $this->_log->addBothFail($e->getMessage());
                    }
        
                    try {
                        $this->_orderApi->sendOrderShipmentMail($order, $transferNo, $order->getLogisticsProvider());
                        $this->_log->addInfo("{$logPrefix} 发送发货通知邮件: {$order->getCustomerEmail()}");
                    } catch(\Exception $e) {
                        $msg = "{$logPrefix}发送发货通知失败: {$e->getMessage()}";
                        $this->_log->addBothFail($msg);
                    }
        
                }
            } else {
                if ($type == $this->_orderApi::STAGE_SHIPPED) {
                    $this->_log->addInfo($head . '非豪速通订单不需要更新status为shipped!');
                    return false;
                }
                $transferNo = !empty($order->getExpressNo()) ? $order->getExpressNo() : false;
                $this->_log->addInfo($head . '获取转单号->' . '非豪速通用户,无转单号');
            }
            if ($transferNo === false) {
                $this->_log->addInfo($head . '还没有运单号');
                return false;
            }
            
            if (!in_array($order->getState(), [
                \Magento\Sales\Model\Order::STATE_NEW,
                \Magento\Sales\Model\Order::STATE_PENDING_PAYMENT,
                \Magento\Sales\Model\Order::STATE_PROCESSING,
                \Magento\Sales\Model\Order::STATE_COMPLETE
            ])) {
                $this->_log->addInfo($head . '订单状态异常, 订单状态为closed,cancel 等非正常状态的订单直接过滤');
                return false;
            }
            
            $this->_log->addInfo($head . '改前status[' . $order->getStatus() . '] state [' . $order->getState() . ']');
            
            if ($type == $this->_orderApi::STAGE_COMPLETE) {
                $order->setData('status', $this->_orderApi::STAGE_COMPLETE);
                $order->setData('state', \Magento\Sales\Model\Order::STATE_COMPLETE);
            } else {
                $order->setData('status', $this->_orderApi::STAGE_SHIPPED);
                if (in_array($order->getState(), [
                    \Magento\Sales\Model\Order::STATE_NEW,
                    \Magento\Sales\Model\Order::STATE_PENDING_PAYMENT,
                    \Magento\Sales\Model\Order::STATE_PROCESSING
                ])) {
                    $order->setData('state', \Magento\Sales\Model\Order::STATE_PROCESSING);
                }
            }
            $order->save();
            $this->_log->addInfo($head . '改后status[' . $order->getStatus() . '] state [' . $order->getState() . '],设置状态为:' . $type);
            $this->_log->addInfo($head . '状态保存成功');
        } else {
            $this->_log->addInfo('订单不存在或者订单ID异常!');
            return false;
        }
    }
    
    public function fetchOrderTransferNo(\Magento\Sales\Model\Order $order) {
        if ($order->getTransferNo()) {
            return $order->getTransferNo();
        }
    
        if ($order->getLogisticsProvider() == '豪速通') {
            $queryUrl = str_replace('{{no}}', $order->getExpressNo(), $this->EXPRESS_QUERY_ADDRESS[$order->getLogisticsProvider()]);
            $htmlContent = file_get_contents($queryUrl);
            //从豪速通查询网站中获取转单号
            preg_match('#运单号[\s\S]*?<td[^>]*>[\s\S]*?转单号[\s\S]*?</td>[\s\S]*?</tr>[\s\S]*?<tr[^>]*>[\s\S]*?<td>[\s\S]*?</td>[\s\S]*?<td[^>]*>([\s\S]*?)</td>#i', $htmlContent, $match);
            $transferNo = trim(strip_tags($match[1]));
        
            $expressNo = !empty($order->getExpressNo()) ? $order->getExpressNo() : time();
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            /** @var \Magento\Framework\Filesystem\DirectoryList $directory */
            $directory = $objectManager->get('\Magento\Framework\Filesystem\DirectoryList');
            $logFile = $directory->getRoot() . '/var/log/' . $this->_log::BASE_LOG_DIR . '/getTransferHtml/' . $expressNo . '.html';
            $dir = dirname($logFile);
        
            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }
            file_put_contents($logFile, $htmlContent);
            if (!empty($transferNo) && $transferNo != $order->getExpressNo()) {
                $order->setTransferNo($transferNo)->save();
                return $transferNo;
            } else {
                return false;
            }
        }
    
        return false;
    }
}