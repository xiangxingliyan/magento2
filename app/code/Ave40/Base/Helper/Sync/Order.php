<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018-6-11
 * Time: 下午 05:51
 */
namespace Ave40\Base\Helper\Sync;


use Ave40\Base\Model\Request\MdwApi\Product\Erpinfo;
use Magento\Framework\App\Helper\Context;

class Order extends \Magento\Framework\App\Helper\AbstractHelper
{
    //订单同步的配置
    const CK_ENABLE_ORDER_SYNC = 'ave40_base_config_erp/general/order_sync_enabled';
    const CK_ALLOW_SYNC_PENDING_ORDER_ENABLED = 'ave40_base_erp/general/allow_sync_pending_order_enabled';
    const CK_PAYMENT_PAYPAL_FEES = 'ave40_base_erp/payment/paypal_fees';
    
    const POST_CODE_LENGTH = 14;
    const LOGFILENAME = 'order/order_sync';
    
    const SITE = 'vladdin';
    
    protected $_mdwHepler;
    
    protected $_orderFactory;
    
    protected  $_records;
    
    protected $_create;
    
    protected $_orderHelper;
    
    protected $_shippingmap;
    
    protected $_orderinfo;
    
    protected $_product;
    
    protected $_configurable;
    
    protected $_shippingmapping;
    
    protected $_erpinfo;
    
    protected $_actualamountHelper;
    
    protected $_log;
    
    public function __construct(Context $context,
                                \Magento\Sales\Model\OrderFactory $orderFactory,
                                \Ave40\Base\Model\Mdw\Records $records,
                                \Ave40\Base\Model\Request\MdwApi\Order\Create $create,
                                \Ave40\Base\Model\Request\MdwApi\Product\Erpinfo $erpinfo,
                                \Ave40\Base\Helper\Sync\Order\Shippingmapping $shippingmap,
                                \Magento\Catalog\Model\Product $product,
                                \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurable,
                                \Ave40\Base\Model\Shippingmapping $shippingmapping,
                                \Ave40\Base\Helper\Sync\Order\ActualAmountToAccount $actualAmountToAccount)
    {
        parent::__construct($context);
        $this->_orderFactory = $orderFactory;
        $this->_records = $records;
        $this->_create = $create;
        $this->_shippingmap = $shippingmap;
        $this->_configurable = $configurable;
        $this->_shippingmapping = $shippingmapping;
        $this->_erpinfo = $erpinfo;
        $this->_product = $product;
        $this->_actualamountHelper = $actualAmountToAccount;
        $this ->_log = new \Ave40\Base\Model\Log(self::LOGFILENAME);
    
    }
    
    /**
     * 开始同步订单
     * @param \Magento\Sales\Model\Order $order
     */
    public function SyncOrder(\Magento\Sales\Model\Order $order) {
        //后台是否开启了订单同步
        if(!$this->isOrderSyncEnabled()) {
            return;
        }
    
        /**
         * 改成所有状态都可以同步了
         */
        // if(!in_array($order->getState(),[\Magento\Sales\Model\Order::STATE_PROCESSING,\Magento\Sales\Model\Order::STATE_NEW])) {
        //     return;
        // }elseif(!$this->orderCanBeSync($order)) {
        //     return;
        // }
        
        if(!$this->orderCanBeSync($order)) {
            return;
        }
    
        /**
         * @var $recordModel \Ave40\Base\Model\Mdw\Records
         */
        $recordModel = $this->getSyncRecord(\Ave40\Base\Model\Mdw\Records::TYPE_ORDER_UPLOAD, $order->getId());
        
        if($recordModel->getStatus() == \Ave40\Base\Model\Mdw\Records::STATUS_PROCESSING ||
            $recordModel->getStatus() == \Ave40\Base\Model\Mdw\Records::STATUS_COMPLETED) {
            return ;
        }
    
        if(!$recordModel->getId()) {
            $recordModel = $this->_records;
            $recordModel->setType(\Ave40\Base\Model\Mdw\Records::TYPE_ORDER_UPLOAD);
            $recordModel->setExtData($order->getIncrementId());
            $recordModel->setEntityId($order->getId());
            $recordModel->setLastUpdatedAt(date('Y-m-d H:i:s'));
            if($order->getState() == \Magento\Sales\Model\Order::STATE_NEW) {
                $recordModel->setStatus(\Ave40\Base\Model\Mdw\Records::STATUS_ARRIVED_DMW);
            } else {
                $recordModel->setStatus(\Ave40\Base\Model\Mdw\Records::STATUS_PROCESSING);
            }
        
            $recordModel->save();
        }
    
    
        $recordModel->getResource()->beginTransaction();
    
        /**
         * 请求的方法
         * \Ave40\Base\Model\Request\MdwApi\Order\Create
         */
        $orderuploadRequest = $this->_create;
    
        try {
            //组装订单信息结构
            $orderDetail = $this->packOrderAvaliableInfo($order);
            // echo '<pre>';
            // print_r($orderDetail);die;
            $dataJson = json_encode($orderDetail);
        } catch(\Exception $e) {
            $recordModel->setMessage('订单同步到ERP失败: ' . $e->getMessage());
            $recordModel->setFailCount($recordModel->getFailCount()+1);
            $recordModel->setStatus(\Ave40\Base\Model\Mdw\Records::STATUS_FAILURE);
            $recordModel->save();
            $recordModel->getResource()->commit();
            return;
        }
    
        $orderuploadRequest->setRequestData('data', $dataJson);
        $this->_log->addInfo("{$order->getState()} 准备发起订单同步到MDW({$order->getIncrementId()})");
    
        if($orderuploadRequest->send()->fail()) {
            $this->_log->addError("{$dataJson}\n{$order->getState()} 订单上传到MDW失败: {$orderuploadRequest->getHttpCode()}:{$orderuploadRequest->getCurlMessage()}");
            $recordModel->setMessage("订单上传到MDW失败: {$orderuploadRequest->getHttpCode()}:{$orderuploadRequest->getCurlMessage()}");
            $recordModel->setExtData([
                'curl_http_code' => $orderuploadRequest->getHttpCode(),
                'curl_message' => $orderuploadRequest->getCurlMessage(),
                'curl_response' => $orderuploadRequest->getContent()
            ]);
        
            $recordModel->setExtData(json_encode(['orderNo'=>$order->getIncrementId(), 'response' => $orderuploadRequest->getResult(), 'response_raw' => $orderuploadRequest->getContent()]));
            $recordModel->setFailCount($recordModel->getFailCount()+1);
            $recordModel->setStatus(\Ave40\Base\Model\Mdw\Records::STATUS_FAILURE);
            $recordModel->save();
            $recordModel->getResource()->commit();
            return;
        }
    
        $this->_log->addInfo("{$order->getState()} 订单同步请求发送完成({$order->getIncrementId()}): $dataJson");
    
        if($orderuploadRequest->getResultFail()) {
            if(!$orderuploadRequest->ifResultCodeIs(\Ave40\Base\Model\Request\Base::CODE_ORDER_EXISTS)) {
                $this->_log->addError("{$dataJson}\n在订单上传到MDW失败，中间件响应：{$orderuploadRequest->getResultMessage()}");
                $recordModel->setMessage('在订单上传到MDW失败，中间件响应：'. $orderuploadRequest->getResultMessage());
                $recordModel->setFailCount($recordModel->getFailCount()+1);
                $recordModel->setExtData(json_encode(['orderNo'=>$order->getIncrementId(), 'response' => $orderuploadRequest->getResult(), 'response_raw' => $orderuploadRequest->getContent()]));
                $recordModel->setStatus(\Ave40\Base\Model\Mdw\Records::STATUS_FAILURE);
                $recordModel->save();
                $recordModel->getResource()->commit();
                return;
            }
        }
    
        if($order->getState() == \Magento\Sales\Model\Order::STATE_NEW) {
            $recordModel->setStatus(\Ave40\Base\Model\Mdw\Records::STATUS_ARRIVED_DMW);
        } else {
            $recordModel->setStatus(\Ave40\Base\Model\Mdw\Records::STATUS_PROCESSING);
        }
    
        $recordModel->setMessage('');
        $recordModel->setExtData(json_encode(['orderNo' => $order->getIncrementId(), 'items' => $orderDetail['itemInfo']]));
        $recordModel->save();
        $recordModel->getResource()->commit();
    
    }
    
    //查看订单后台是否开启了订单同步
    public function isOrderSyncEnabled()
    {
        return $this->getconfig(self::CK_ENABLE_ORDER_SYNC);
    }
    
    //查看配置
    public function getconfig($config)
    {
        return $this->scopeConfig->getValue(
            $config,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    
    /**
     * 检查当前订单是否可以进行同步
     * @param \Magento\Sales\Model\Order $order
     * @return bool
     */
    public function orderCanBeSync(\Magento\Sales\Model\Order $order) {
        
        if(in_array($order->getState(), [\Magento\Sales\Model\Order::STATE_CANCELED, \Magento\Sales\Model\Order::STATE_CLOSED])) {
            return false;
        }
        
        //看看有没有上传过的记录
        $record = $this->getSyncRecord(\Ave40\Base\Model\Mdw\Records::TYPE_ORDER_UPLOAD, $order->getId());
        
        if(!$record->getId()) {
            return true;
        }
        
        
        if($record->getStatus() == \Ave40\Base\Model\Mdw\Records::STATUS_FAILURE) {
            return true;
        }
        
        if($order->getState() == \Magento\Sales\Model\Order::STATE_PROCESSING &&
            in_array($record->getStatus(), [\Ave40\Base\Model\Mdw\Records::STATUS_PENDING, \Ave40\Base\Model\Mdw\Records::STATUS_ARRIVED_DMW])) {
            return true;
        }
        
        return false;
    }
    
    /**
     * 检查同步记录
     * @param $type
     * @param $entityId
     * @return mixed
     */
    public function getSyncRecord($type,$entityId) {
        $collection = $this->_records->getCollection();
        $collection->addFieldToFilter('entity_id', $entityId);
        $collection->addFieldToFilter('type', $type)->load();
        
        return $collection->getFirstItem();
    }
    
    /**
     * 打包订单数据给中间件使用
     * @param $order
     * @return array
     * @throws Exception
     */
    public function packOrderAvaliableInfo($order) {
        $shippingAddress = $order->getShippingAddress();
        $orderItems = [];
        $emptyItemNoList = [];
        /**
         * \Ave40\Base\Helper\Shippingmap
         */
        $ave40ShippingHelper = $this->_shippingmap;
        
        $shippingFee = floatval($order->getShippingAmount());
        $grandTotal = $order->getGrandTotal();
        $actualAmountToAccount = floatval($order->getActualAmountToAccount());
        /**
         * 实际到账金额的判断和获取
         */
        if(empty($actualAmountToAccount)) {
            $actualAmountToAccount = $this->_actualamountHelper->getActualAmount($order);
        }
        
        $actualDiscountAmount =  $order->getSubtotal()+$order->getShippingAmount() - $actualAmountToAccount;
        $actualDiscountAmount = $actualDiscountAmount < 0 ? 0 : $actualDiscountAmount;
        
        //短款先摊到运费中
        if($shippingFee > $actualDiscountAmount) {
            $shippingFee -= $actualDiscountAmount;
            $actualDiscountAmount = 0;
        } else {
            $actualDiscountAmount -= $shippingFee;
            $shippingFee = 0;
        }
        
        //运费不足， 将多余的短款分摊到所有商品
        $productRate = $actualDiscountAmount/($order->getSubtotal() + $order->getShippingAmount());
        
        if(!$productRate) {
            $productRate = 1;
        } else {
            $productRate = 1 - $productRate;
            if($productRate < 0) {
                $productRate = 1;
            }
        }
        
        $subtotal = 0;
        
        /**
         * @var $item \Magento\Sales\Model\Order\Item
         */
        foreach($order->getAllVisibleItems() as $item) {
            $productOption = $item->getProductOptions();
            $superAttributes = $productOption['info_buyRequest']['super_attribute'];
            $product = $this->_product->load($item->getProductId());
            
            if($item->getProductType() == 'configurable') {
                /** @var Mage_Catalog_Model_Product $subproduct */
                $subproduct = $this->_configurable->getProductByAttributes($superAttributes, $product);
                
                if(!$subproduct) {
                    throw new \Exception("父产品的子产品已经不存在或者父产品属性已经调整 sku:" . $product->getSku());
                }
                
                $subproduct = $this->_product->load($subproduct->getId());
            } else {
                $subproduct = $product;
            }
            
            if(empty($subproduct->getItemnum())) {
                $emptyItemNoList []= $subproduct->getId();
            }
            
            $price = round(floatval($item->getPrice()) * $productRate, 2);
            $subtotal += $price * $item->getQtyOrdered();
            
            $orderItems []= [
                'itemNo' => $subproduct->getItemnum(),
                'qty' => $item->getQtyOrdered(),
                'price' => $price,
                'origin_price' => $item->getPrice(),
                'sku' => $item->getSku(),
                'productId' => $item->getProductId(),
                'name' => $item->getName(),
                'weight' => $item->getWeight()
            ];
        }
        
        //继续分摊差价补到某个商品里面
        $index = 0;
        $oldErpTotal = null;
        
        while($index++<9) {
            $erpTotal = round($subtotal + $shippingFee, 2);
            
            if($erpTotal > $actualAmountToAccount) {
                $gap = $erpTotal - $actualAmountToAccount;
                
                if($gap < $shippingFee) {
                    $shippingFee -= $gap;
                    break;
                }
                
                $gapRate = 1 - $gap / $erpTotal;
                $subtotal = 0;
                $shippingFee = round($shippingFee * $gapRate, 2);
                
                foreach ($orderItems as $i => $row) {
                    $orderItems[$i]['price'] = round($gapRate * $row['price'], 2);
                    $subtotal += $orderItems[$i]['price'] * $orderItems[$i]['qty'];
                }
                
                if($oldErpTotal === $erpTotal) {
                    break;
                } else {
                    $oldErpTotal = $erpTotal;
                }
            } else {
                break;
            }
        }
        
        if(!empty($emptyItemNoList)) {
            throw new \Exception("以下商品ID缺少Itemnum: " . implode(',', $emptyItemNoList));
        }
        
        /**
         * @var $shippingmappingModel \Ave40\Base\Model\Shippingmapping
         */
        $shippingmappingModel = $this->_shippingmapping;
        try {
            $withBattery = $this->isProductWithBatteries(array_column($orderItems, 'itemNo'));
            $erpCode = $ave40ShippingHelper->findShipping($order->getShippingMethod(), $shippingAddress->getCountryId(), $withBattery);
            if(null == $erpCode) {
                throw new \Exception("物流 [{$order->getShippingDescription()}, {$shippingAddress->getCountryId()}, {$withBattery}] 没有找到对应的erp运输方式");
            }
        } catch(\Exception $e) {
            throw new \Exception($e->getMessage());
        }
        
        $street = $shippingAddress->getStreet();
        $street = implode(' ', $street);
        $postcode = substr(trim($shippingAddress->getPostcode()), 0, self::POST_CODE_LENGTH);
        
        $shippingContactFirstName = trim($shippingAddress->getFirstname());
        $shippingContactMiddleName = trim($shippingAddress->getMiddlename());
        $shippingContactLastName = trim($shippingAddress->getLastname());
        $shippingContactEmail = $shippingAddress->getEmail();
        $shippingPhoneNumber = trim($shippingAddress->getTelephone());
        $shippingPhoneNumber = empty($shippingPhoneNumber) ? '-' : $shippingPhoneNumber;
        
        //解决因为crm不支持超过20个字符的firstname, 手动替用户分隔firstname, middlename和lastname
        if(empty($shippingContactMiddleName) && empty($shippingContactLastName)) {
            $_nameAry = $this->splitName($shippingContactFirstName);
            $shippingContactFirstName =  $_nameAry['first'];
            $shippingContactLastName =  $_nameAry['mid'];
            $shippingContactMiddleName =  $_nameAry['last'];
        }
        
        $customerEmail = $order->getCustomerEmail() ? $order->getCustomerEmail() : $shippingContactEmail;
        $customerFirstName = trim($order->getCustomerFirstname());
        $customerMiddleName = trim($order->getCustomerMiddlename());
        $customerLastName = trim($order->getCustomerLastname());
        
        if(empty($customerFirstName) && empty($customerMiddleName) && empty($customerLastName)) {
            $customerFirstName = $shippingContactFirstName;
            $customerMiddleName = $shippingContactMiddleName;
            $customerLastName = $shippingContactLastName;
        }
        
        //解决因为crm不支持超过20个字符的firstname, 手动替用户分隔firstname, middlename和lastname
        if(empty($customerMiddleName) && empty($customerLastName)) {
            $_nameAry = $this->splitName($customerFirstName);
            $customerFirstName =  $_nameAry['first'];
            $customerMiddleName =  $_nameAry['mid'];
            $customerLastName =  $_nameAry['last'];
        }
        
        $customerLastName = $customerLastName ? $customerLastName : '.';
        
        if(empty($customerEmail)) {
            $customerEmail = $shippingContactEmail;
        }
        
        return [
            'id' => $order->getId(),
            'site' => self::SITE, //  vladdin
            'orderNo' => $order->getIncrementId(),
            'orderState' => $order->getState(),
            'comment' => $order->getCustomerNote(),
            'shippingDescription' => $order->getShippingDescription(),
            'isVitual' => $order->getIsVirtual(),
            'storeId' => $order->getStoreId(),
            'grandTotal' => $grandTotal,
            'actualAmountToAccount' => $actualAmountToAccount,
            'orderDiscountAmount' => $order->getDiscountAmount(),
            'actualDiscountAmount' => 0,
            'shippingAmount' => $shippingFee,
            'subtotal' => $subtotal,
            'weight' => $order->getWeight(),
            'battery' => $withBattery,
            'baseCurrencyCode' => $order->getOrderCurrencyCode(),
            'paymentMethod' => $order->getPayment()->getMethod(),
            'itemInfo' => $orderItems,
            'customerInfo' => [
                'email' => $customerEmail,
                'name' => implode(' ', array_filter([
                    $customerFirstName,
                    $customerMiddleName,
                    $customerLastName
                ])),
                'firstName' => $customerFirstName,
                'middleName' => $customerMiddleName,
                'lastName' => $customerLastName,
            ],
            'ipAddress' => $order->getRemoteIp(),
            'shippingInfo' => [
                'method' => $erpCode,
                'ave40_method' => $order->getShippingMethod(),
                'amount' => $shippingFee,
                'address' => [
                    'country' => empty($shippingAddress->getCountryId()) ? '-' : $shippingAddress->getCountryId(),
                    'region' => empty($shippingAddress->getRegion()) ? '-' : $shippingAddress->getRegion(),
                    'city' => empty($shippingAddress->getCity()) ? '-' : $shippingAddress->getCity(),
                    'street' => $street,
                    'postcode' => empty($postcode) ? '0' : $postcode,
                ],
                'contact' => [
                    'firstName' => $shippingContactFirstName,
                    'middleName' => $shippingContactMiddleName,
                    'lastName' => $shippingContactLastName,
                    'phoneNo' => $shippingPhoneNumber,
                    'email' => $shippingAddress->getEmail()
                ]
            ],
            'created_at' => $order->getCreatedAt(),
            'updated_at' => $order->getUpdatedAt(),
        ];
    }
    
    
    public function isProductWithBatteries($itemno)
    {
        if (empty($itemno)) {
            return 0;
        }
        
        if (is_string($itemno)) {
            $itemno = [$itemno];
        }
        
        $req = $this->_erpinfo;
        $req->setItems($itemno);
        if ($req->send()->fail()) {
            throw new \Exception("查询产品是否带电失败: {$req->getCurlMessage()} (httpcode:{$req->getHttpCode()})");
        }
        
        if ($req->getResultFail()) {
            throw new \Exception("查询产品是否带电失败: {$req->getResultMessage()} (result:{$req->getResultCode()})");
        }
        
        $data = $req->getResultData();
        
        foreach ($data as $row) {
            if ($row['TBAT_0'] == 'Y') {
                return 1;
            }
        }
        
        return 2;
    }
    
    /**
     * crm的客户名有字符限制，手动分割一下
     */
    public function splitName($str) {
        $_nameAry = explode(' ',$str);
        
        return [
            'first' => array_shift($_nameAry),
            'mid' => array_pop($_nameAry),
            'last' => implode(' ', $_nameAry)
        ];
    }
    
    public function isAllowSyncPendingOrder() {
        return $this->getconfig(self::CK_ALLOW_SYNC_PENDING_ORDER_ENABLED);
    }
    
}