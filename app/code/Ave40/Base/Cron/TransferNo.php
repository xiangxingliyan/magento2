<?php
namespace Ave40\Base\Cron;

class TransferNo
{
    protected $_order;
    protected $_log;
    protected $_transferNo;
    protected $_orderApi;
    
    public function __construct(\Magento\Sales\Model\Order $order,
                                \Ave40\Base\Helper\Transfer\TransferNo $transferNo,
                                \Ave40\Base\Model\Api\OrderApi $orderApi
    )
    {
        $this->_order = $order;
        $this->_transferNo = $transferNo;
        $this->_log = new \Ave40\Base\Model\Log($transferNo::LOGFILENAME);
        $this->_orderApi = $orderApi;
    }
    
    /**
     * 定时获取豪速通转单号
     */
    public function execute()
    {
        $this->_log->addInfo('==================='.date('Y-m-d H:i:s').'开始执行获取豪速通转单号任务==============');
        $collection = $this->_order->getCollection()
            ->addFieldToFilter('logistics_provider', ['豪速通'])
            ->addFieldToFilter('express_no', ["neq" => ''])
            ->addFieldToFilter('express_no', ["neq" => '无'])
            ->addFieldToFilter('express_no', ["notnull" => true])
            ->addFieldToFilter(['transfer_no', 'transfer_no'], [["eq" => ''], ['null'=>true]])
            ->load();
        
        $this->_log->addInfo('开始执行sql:' . $collection->getSelectSql(true));
        $this->_log->addInfo('查询条数:' . count($collection));
        echo "total: " . count($collection) . "\n";
    
        foreach ($collection as $item) {
            $this->_transferNo->updateOrderTransferNo($item, $this->_orderApi::STAGE_SHIPPED);
        }
    }
}