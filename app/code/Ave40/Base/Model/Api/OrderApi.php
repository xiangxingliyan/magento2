<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018-6-12
 * Time: 下午 05:04
 */
namespace Ave40\Base\Model\Api;
use Ave40\Base\Api\OrderInterface;
use Magento\Framework\Exception\LocalizedException;

class OrderApi extends ApiAbstract implements OrderInterface {
    const LOGFILENAME = 'order/state_sync';
    
    
    protected $_orderFactory;
    protected $_resourceModel;
    protected $_orderRepository;
    protected $_orderCollectionFactory;
    protected $_convertOrder;
    protected $_objectManager;
    protected $_trackFactory;
    protected $_shipmentNotifier;
    protected $_log;
    protected $_shippmentItem;
    protected $_emailer;
    protected $templateHelper;
    protected $_countryFactory;
    //物流单号查询地址
    protected $transferAddress = [
        'dhl' => 'http://www.dhl.com/en/express/tracking.html?AWB={{ no }}&brand=DHL',
        'other' => 'https://t.17track.net/en/#nums={{ no }}'
    ];
    //dhl
    protected $dhl = ['豪速通'];
    
    //捡货
    const STAGE_PICKING = 'picking';
    //捡货完成
    const STAGE_PICKED = 'picked';
    //已发货
    const STAGE_SHIPPED = 'shipped';
    //已填快递单号
    const STAGE_COMPLETE = 'complete';
    //同步完成
    const STAGE_SYNC_SUCCESS = 'sync_success';
    
    // 订单状态
    protected $_stageStatus = [
        self::STAGE_PICKING => 'picking',
        self::STAGE_PICKED => 'picked',
        self::STAGE_SHIPPED => 'shipped',
        self::STAGE_SYNC_SUCCESS => 'processing',
        self::STAGE_COMPLETE => 'complete'
    ];
    
    protected $_orderStatusLevel = [];
    /**
     * ProductApi constructor.
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
     * @param \Magento\Sales\Model\ResourceModel\Order $resourceModel
     * @param \Magento\Sales\Model\OrderRepository $orderRepository
     * @param \Magento\Sales\Model\Convert\Order $convertOrder
     */
    public function __construct(
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Sales\Model\ResourceModel\Order $resourceModel,
        \Magento\Sales\Model\OrderRepository $orderRepository,
        \Magento\Sales\Model\Convert\Order $convertOrder,
        \Magento\Sales\Model\Order\Shipment\TrackFactory $trackFactory,
        \Magento\Shipping\Model\ShipmentNotifier $shipmentNotifier,
        \Ave40\Base\Helper\Email $emailer,
        \Ave40\Base\Helper\Template $template,
        \Magento\Directory\Model\CountryFactory $countryFactory
    )
    {
        $this->_orderFactory = $orderFactory;
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->_resourceModel = $resourceModel;
        $this->_orderRepository = $orderRepository;
        $this->_convertOrder = $convertOrder;
        $this->_log = new \Ave40\Base\Model\Log(self::LOGFILENAME);
        $this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->_trackFactory = $trackFactory;
        $this->_shipmentNotifier = $shipmentNotifier;
        $this->_emailer = $emailer;
        $this->templateHelper = $template;
        $this->_countryFactory = $countryFactory;
    
        $this->_orderStatusLevel = [
            $this->_stageStatus[self::STAGE_SYNC_SUCCESS],
            $this->_stageStatus[self::STAGE_PICKING],
            $this->_stageStatus[self::STAGE_PICKED],
            $this->_stageStatus[self::STAGE_SHIPPED],
            $this->_stageStatus[self::STAGE_COMPLETE]
        ];
    }
    
    /**
     * @api
     * @param mixed $orderInfo
     * @return mixed|string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Exception
     */
    public function updateOrderState($orderInfo) {
        error_reporting(E_ALL ^ E_NOTICE);
        
        $orderId = trim($orderInfo['order_id']);
        $preparingOrderNo = trim($orderInfo['preparing_order_no']);
        $erpOrderId = trim($orderInfo['erp_order_id']);
        $logisticsProvider = trim($orderInfo['logistics_provider']);
        $expressNo = trim($orderInfo['express_no']);
        $deliveryNo = trim($orderInfo['delivery_no']);
        $waybillNo = trim($orderInfo['waybill_no']);
        $stage = strtolower(trim($orderInfo['stage']));
    
        $this->_log->setPrefix("$orderId, $stage");
        $this->_log->addInfoDown("收到订单状态同步请求:" . json_encode($orderInfo));
    
        if(in_array($stage, [self::STAGE_PICKING, self::STAGE_PICKED])) {
            $msg = "跳过阶段: $stage";
            $this->_log->addInfoSkip($msg);
            return $this->makeFailedReturn($msg);
        }
        
        if(!isset($this->_stageStatus[$stage])) {
            $msg = "不支持的stage: $stage";
            $this->_log->addBothFail($msg);
            return $this->makeFailedReturn($msg);
        }
    
        if (empty($orderId)) {
            return $this->makeFailedReturn('Argument [order_id] can\'t be empty', 'invalid_params');
        }
    
        /**
         * @var $orderInfo \Magento\Sales\Model\Order
         */
        $order = $this->_orderFactory->create();
        $order->loadByIncrementId($orderId);
    
        if(!$order->getId()) {
            return $this->makeFailedReturn("Order [$orderId] does not exist", 'order_not_exist');
        }
    
        //订单生命周期已经结束, 不允许修改
        if(in_array($order->getState(), [
            \Magento\Sales\Model\Order::STATE_CLOSED,
            \Magento\Sales\Model\Order::STATE_CANCELED
        ])) {
            $msg = "此订单state决定该订单不允许再次被修改, state: {$order->getState()}";
            $this->_log->addBothFail($msg);
            return $this->makeFailedReturn($msg, 'LOGIC_FAIL');
        }
    
        $index = array_search($order->getStatus(), $this->_orderStatusLevel);
        $targetStatus = $this->_stageStatus[$stage];
        $targetIndex = array_search($targetStatus, $this->_orderStatusLevel);
    
        if(false !== $index) {
            if($targetIndex < $index) {
                $targetStatus = false;
            }
        }
    
        $save = false;
    
        if (!empty($preparingOrderNo)) {
            $order->setData('preparing_order_no', $preparingOrderNo);
            $save = true;
        }
    
        if (!empty($waybillNo)) {
            $order->setData('waybill_no', $waybillNo);
            $save = true;
        }
    
        if (!empty($erpOrderId)) {
            $order->setData('erp_order_id', $erpOrderId);
            $save = true;
        }
    
        if (!empty($logisticsProvider)) {
            $order->setData('logistics_provider', $logisticsProvider);
            $save = true;
        }
    
        if (!empty($deliveryNo)) {
            $order->setData('delivery_no', $deliveryNo);
            $save = true;
        }
    
        // $sendShippedEmail = false;
        if (!empty($expressNo)) {
            $order->setData('express_no', $expressNo);
            $save = true;
        }
    
        if($targetStatus) {
            $order->setState(\Magento\Sales\Model\Order::STATE_PROCESSING);
            $order->setStatus($targetStatus);
            $save = true;
        }
    
        if ($save) {
            try {
                $this->_resourceModel->save($order);
            } catch (\Exception $e) {
                $msg = "Order save faild";
                $this->_log->addBothFail($msg . ':' . $e->getMessage());
                return $this->makeFailedReturn($msg, 'unknown_error');
            }
        }
    
        if($stage == self::STAGE_SHIPPED) {
            if(empty($expressNo)) {
                $msg = "缺少快递单号";
                $this->_log->addBothFail($msg);
                return $this->makeFailedReturn($msg);
            }
            
            //发货
            try {
                $this->_shipped($order, $expressNo);
            } catch (\Exception $e) {
                $this->_log->addBothFail($e->getMessage());
                return $this->makeFailedReturn($e->getMessage());
            }
            
            //非豪速通发货时需要发邮件
            if(!in_array($logisticsProvider, ['豪速通'])) {
                try {
                    $this->sendOrderShipmentMail($order, $expressNo, $logisticsProvider);
                } catch(\Exception $e) {
                    $msg = "{$logPrefix}发送发货通知失败: {$e->getMessage()}";
                    $this->_log->addBothFail($msg);
                    return $this->makeFailedReturn($msg, 'send_mail_fail');
                }
            }
            
            $order->setState(\Magento\Sales\Model\Order::STATE_PROCESSING);
            $order->setStatus('shipped');
            $this->_resourceModel->save($order);
        }
    
        $this->_log->addInfoSuccess("状态同步完成 state: {$order->getState()}, status: {$order->getStatus()}, expressNo: {$order->getExpressNo()}");
        return $this->makeSuccessfulReturn();
    }
    
    /**
     * @param \Magento\Sales\Model\Order $order
     * @param $expressNo
     * @param string $method
     * @param string $methodTitle
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Exception
     */
    public function _shipped(\Magento\Sales\Model\Order $order, $expressNo, $method=null, $methodTitle=null) {
        $method = $method ?: $order->getShippingMethod();
        $methodTitle = $methodTitle ?: $order->getShippingDescription();
        
        if(!$order->canShip()) {
            throw new \Exception("该订单不能发货:{$order->getIncrementId()}");
        }
    
        $shipment = $this->_convertOrder->toShipment($order);
    
        foreach($order->getAllItems() AS $orderItem) {
            if(!$orderItem->getQtyToShip() || $orderItem->getIsVirtual()) {
                continue;
            }
    
            $qtyShipped = $orderItem->getQtyToShip();
            $shipmentItem = $this->_convertOrder->itemToShipmentItem($orderItem);
            $shipmentItem->setQty($qtyShipped);
            $shipment->addItem($shipmentItem);
        }
    
        $shipment->register();
    
        $data = [
            'carrier_code' => $method,
            'title' => $methodTitle,
            'number' => $expressNo, // Replace with your tracking number
        ];
    
        $shipment->getOrder()->setIsInProcess(true);
    
        try {
            $track = $this->_trackFactory->create()->addData($data);
            $shipment->addTrack($track)->save();
            $shipment->save();
            $shipment->getOrder()->save();
            // $this->_shipmentNotifier->notify($shipment); //通知客户?
            $shipment->save();
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __($e->getMessage())
            );
        }
    }
    
    public function sendOrderShipmentMail(\Magento\Sales\Model\Order $order, $expressNo, $logisticsProvider) {
        
        if (preg_match('#^[0-9a-zA-Z\-_]+$#i', $expressNo)) {
            //发邮件
            $billingAddress = $order->getBillingAddress()->getData();
            $shippingAddress = $order->getShippingAddress()->getData();
            $billCountry =  $this->_countryFactory->create()->loadByCode($billingAddress['country_id']);
            $shippCountry =  $this->_countryFactory->create()->loadByCode($shippingAddress['country_id']);
            
            $transferAddress = '';
            $url = '';
            $trankNo = empty($order->getTransferNo()) ? $order->getExpressNo() : $order->getTransferNo();
            if(in_array($logisticsProvider, $this->dhl)) {
                $transferAddress = str_replace('{{ no }}', $trankNo, $this->transferAddress['dhl']);
                $url = 'http://www.dhl.com/en.html';
            }else {
                $transferAddress = str_replace('{{ no }}', $trankNo, $this->transferAddress['other']);
                $url = 'https://www.17track.net/en';
            }
            $content = $this->templateHelper->render('Ave40_Base::shipped-email-template.phtml', [
                'orderNo' => $order->getIncrementId(),
                'billingAddress' => $billingAddress,
                'shippingAddress' => $shippingAddress,
                'billCountry' => $billCountry->getName(),
                'shippCountry' => $shippCountry->getName(),
                'allItems' => $order->getAllItems(),
                'shipping' => $order->getShippingDescription(),
                'trackNo' => $trankNo,
                'transferAddress' => $transferAddress,
                'url' => $url,
                'createAt' =>  date('m/d/Y',strtotime($order->getCreatedAt()))
            ]);
            $emailReq = new \Ave40\Base\Entities\Email\SendRequest();
            $emailReq->setContent($content)
                ->setSubject("Vladdin Order Tracking Information E Mail")
                ->addRecipients($order->getCustomerEmail())
                ->setFromName($this->_emailer->_getSalesFromName());
            $this->_emailer->send($emailReq);
            $this->_log->addInfo("发送发货邮件: Provider: {$logisticsProvider}, expressNo: {$expressNo}, email:".$order->getCustomerEmail());
        } else {
            $msg = "快递单号格式不符合: Provider: {$logisticsProvider}, expressNo: {$expressNo}";
            throw new \Exception($msg);
        }
    }
}