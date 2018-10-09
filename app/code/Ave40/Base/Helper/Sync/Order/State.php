<?php

namespace Ave40\Base\Helper\Sync\Order;

use Magento\Framework\App\Helper\Context;

class State extends \Magento\Framework\App\Helper\AbstractHelper
{
    const SYNC_FAIL = 2;
    const SYNC_SUCCESS = 1;
    const SYNC_START = 0;
    
    protected $_order;
    
    protected $_json;
    
    protected $_orderHelper;
    
    protected $_syncstatus;
    protected $_backendUrlModel;
    
    public function __construct(Context $context,
                                \Magento\Sales\Model\Order $order,
                                \Ave40\Base\Helper\Json $json,
                                \Ave40\Base\Helper\Sync\Order $orderHelper,
                                \Ave40\Base\Model\Request\MdwApi\Order\Syncstatus $syncstatus,
    \Magento\Backend\Model\UrlInterface $backendUrlModel
    )
    {
        parent::__construct($context);
        $this->_order = $order;
        $this->_json = $json;
        $this->_orderHelper = $orderHelper;
        $this->_syncstatus = $syncstatus;
        $this->_backendUrlModel = $backendUrlModel;
    }
    
    /**
     * 手动同步订单
     */
    public function manualUpload($orderId)
    {
        /**
         * @var $orderHelper Ave40\Base\Helper\Sync\Order
         */
        $orderHelper = $this->_orderHelper;
        $item = $orderHelper->getSyncRecord(\Ave40\Base\Model\Mdw\Records::TYPE_ORDER_UPLOAD, $orderId);
        
        if($item->getId() && in_array($item->getStatus(),
                [\Ave40\Base\Model\Mdw\Records::STATUS_COMPLETED, \Ave40\Base\Model\Mdw\Records::STATUS_PROCESSING])) {
            return ['notice' => '此订单正在同步或者已经同步完成, 不需要再同步!'];
        }
        
        /** @var Magento\Sales\Model\Order $order */
        $order = $this->_order->load($orderId);
        if(empty($order) || empty($order->getId())) {
            return ['error' => '订单:' . $orderId . ' 不存在'];
        }
        
        // if(!$orderHelper->isAllowSyncPendingOrder() && $order->getState() != \Magento\Sales\Model\Order::STATE_PROCESSING) {
        //     return ['error' => '此订单不能同步, 只有processing的订单才能同步, 如果需同步, 可在后台开启相关功能'];
        // }
        
        if(!$orderHelper->orderCanBeSync($order)) {
            return ['error' => '此订单无需同步'];
        }
    
        $orderHelper->syncOrder($order);
        return ['notice' => '同步请求已经发送, 请在订单同步信息栏中查看状态'];
    }
    
    /**
     * 查询订单同步状态
     * @param $orderId
     * @return array|string
     */
    public function queryOrderSyncState($orderId)
    {
        /**
         * @var $orderHelper \Ave40\Base\Helper\Sync\Order
         */
        $orderHelper = $this->_orderHelper;
    
        /** @var Magento\Sales\Model\Order $order */
        $order = $this->_order->load($orderId);
    
        if(!$order->getId()) {
            return '订单号不存在('.$order->getId().')';
        }
    
        $responseAry = ['html' => ''];
        /**
         * @var $recordModel Ave40\Base\Model\Mdw\Records
         */
        $recordModel = $orderHelper->getSyncRecord(\Ave40\Base\Model\Mdw\Records::TYPE_ORDER_UPLOAD, $order->getId());
        $extMessage = '';
        $manualSyncButtonHtml = "<button onclick='confirm(\"确定要发送同步请求吗?\") ? location.href=\"".$this->getSyncUrl($orderId)."\" :0;' class='scalable' type='button'>手动同步</button>";
        $ReSyncButtonHtml = <<<html
<button onclick='confirm("确定要发送同步请求吗?") ? location.href="{$this->getSyncUrl($orderId)}":0;' class='scalable' type='button'>重新同步</button>
html;
    
        if(!$orderHelper->orderCanBeSync($order)) {
            $manualSyncButtonHtml = $ReSyncButtonHtml = '<span style="color: gray"><i>(不可同步)</i></span>';
        }
    
        $textAreaHtml = <<<html
<textarea readonly='readonly' disabled='disabled' style='font-size:12px;display:block;width:95%;height:26px;background:lightgrey;overflow: hidden;text-overflow: ellipsis;'>
 {$recordModel->getExtData()}
</textarea>
html;
        $lastSyncTimeHtml = <<<html
<div>最后同步时间: {$recordModel->getLastUpdatedAt('PRC')}</div>
html;
    
        //当前为未完成的同步请求, 到mdw中查询当前状态
        if($recordModel->getStatus() == \Ave40\Base\Model\Mdw\Records::STATUS_PROCESSING) {
            $statusReq = $this->_syncstatus;
            $statusReq->setRequestData('data', json_encode(['orderNo'=>$order->getIncrementId(),'site' => \Ave40\Base\Helper\Sync\Order::SITE]));
            
            if($statusReq->send()->fail()) {
                $extMessage .= "查询状态失败: " . $statusReq->getHttpCode() . ':' . $statusReq->getCurlMessage();
            } else if($statusReq->getResultFail()) {
                $extMessage .= "查询状态失败: " . $statusReq->getResultCode() . ':' . $statusReq->getResultMessage();
            } else {
                $sync = $statusReq->getResultData('sync_state');
            
                switch($sync) {
                    case self::SYNC_FAIL:
                        $extMessage .= '(SYNC_FAIL #'.self::SYNC_FAIL." MDW同步到ERP失败 {$statusReq->getResultData('msg')})";
                        $recordModel->setStatus(\Ave40\Base\Model\Mdw\Records::STATUS_FAILURE)->save();
                        break;
                    case self::SYNC_SUCCESS:
                        $recordModel->setStatus(\Ave40\Base\Model\Mdw\Records::STATUS_COMPLETED)->save();
                        break;
                    case self::SYNC_START:
                        $extMessage .= '(#'.self::SYNC_START.' MDW正在开始同步)';
                        break;
                }
            
                $recordModel->setMessage($extMessage)
                    ->setLastUpdatedAt(date('Y-m-d H:i:s'))
                    ->save();
                $extMessage = null;
            }
        }
    
        $responseAry['syncdata'] = $recordModel->getId() ? $recordModel->getData() : false;
    
        if($recordModel->getStatus() == \Ave40\Base\Model\Mdw\Records::STATUS_FAILURE) {
            $responseAry['html'] = <<<html
<div style='color:red;'>{$recordModel->getMessage()}{$extMessage} </div>
$textAreaHtml
$lastSyncTimeHtml
$ReSyncButtonHtml
html;
            return $responseAry;
        }
    
        if($recordModel->getStatus() == \Ave40\Base\Model\Mdw\Records::STATUS_PROCESSING) {
            $responseAry['html'] = <<<html
<div style='color:orangered;'>同步中...{$extMessage}</div>
$textAreaHtml
$lastSyncTimeHtml
html;
            return $responseAry;
        }
    
        if($recordModel->getStatus() == \Ave40\Base\Model\Mdw\Records::STATUS_COMPLETED) {
            $responseAry['html'] =  <<<html
<div style='color:green;'>已同步</div>
$lastSyncTimeHtml
html;
            return $responseAry;
        }
    
        if($recordModel->getStatus() == \Ave40\Base\Model\Mdw\Records::STATUS_ARRIVED_DMW) {
            $tmpButton = '';
            $noticeText = '订单已达中间平台, 等待用户付款';
        
            if(in_array($order->getState(), [\Magento\Sales\Model\Order::STATE_PROCESSING, \Magento\Sales\Model\Order::STATE_COMPLETE])) {
                $tmpButton = $manualSyncButtonHtml;
                $noticeText = '订单状态已变更, 若系统没有同步, 可以手动同步至ERP';
            }
        
            $responseAry['html'] =  <<<html
<div style='color:gray;'>$noticeText</div>
$lastSyncTimeHtml
$tmpButton
html;
            return $responseAry;
        }
    
        // if(!in_array($order->getPayment()->getMethod(), ['paypal_express', 'payonlinecc_payment'])) {
        //     // return "此支付方式不支持同步订单到ERP";
        //     $responseAry['html'] =  "未同步 &nbsp;&nbsp;$manualSyncButtonHtml";
        // } else {
        $responseAry['html'] =  "未同步 &nbsp;&nbsp;$manualSyncButtonHtml";
        // }
        
        return $responseAry;
    
    }
    
    /**
     * 获取同步处理url
     * @return mixed
     */
    public function getSyncUrl($orderId)
    {
        return $this->getBackendUrl('*/sync_order/manualUpload/id/' . $orderId);
    }
    
    public function getBackendUrl($path) {
        return $this->_backendUrlModel->getUrl($path);
    }
}