<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018-6-11
 * Time: 下午 05:51
 */

namespace Ave40\Base\Helper\Demiware;

use Magento\Framework\App\Helper\Context;

class Demiware extends \Magento\Framework\App\Helper\AbstractHelper
{
    //订单同步的配置
    const CONFIG_DEMIWARE_GROUP = 'ave40_demiware_setting/base_config/';

    const CONFIG_DEMIWARE_GROUP_MINIMUM = 'minimum';
    const CONFIG_DEMIWARE_GROUP_START_ACTION = 'start_action';
    const CONFIG_DEMIWARE_GROUP_LAST_DATE = 'last_date';
    const CONFIG_DEMIWARE_GROUP_PRODUCT_INFO = 'product_info';
    const CONFIG_DEMIWARE_GROUP_DEMIWARE_CODE = 'demiware_code';
    const CONFIG_DEMIWARE_GROUP_DEMIWARE_QUANTITY = 'demiware_quantity';

    const LOGFILENAME = 'demiware';

    protected $_log;

    protected $_demiwareCollectionFactory;

    protected $_demiwareFactory;
    /**
     * @var Session
     */
    protected $session;

    public function __construct(
        Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Ave40\Base\Model\ResourceModel\Demiware\CollectionFactory $demiwareCollectionFactory,
        \Ave40\Base\Model\DemiwareFactory $demiwareFactory
    )
    {
        parent::__construct($context);
        $this->session = $customerSession;
        $this->_log = new \Ave40\Base\Model\Log(self::LOGFILENAME);
        $this->_demiwareCollectionFactory = $demiwareCollectionFactory;
        $this->_demiwareFactory = $demiwareFactory;

    }

    protected function getConfigKey($code = '')
    {
        return self::CONFIG_DEMIWARE_GROUP . $code;
    }

    public function getMinimum()
    {
        return $this->getconfig($this->getConfigKey(self::CONFIG_DEMIWARE_GROUP_MINIMUM));
    }

    public function getStartAction()
    {
        return $this->getconfig($this->getConfigKey(self::CONFIG_DEMIWARE_GROUP_START_ACTION));
    }

    public function getLastDate()
    {
        return $this->getconfig($this->getConfigKey(self::CONFIG_DEMIWARE_GROUP_LAST_DATE));
    }

    public function getProductInfo()
    {
        return $this->getconfig($this->getConfigKey(self::CONFIG_DEMIWARE_GROUP_PRODUCT_INFO));
    }

    public function getDemiwareCode()
    {
        return $this->getconfig($this->getConfigKey(self::CONFIG_DEMIWARE_GROUP_DEMIWARE_CODE));
    }

    public function getDemiwareQuantity()
    {
        return $this->getconfig($this->getConfigKey(self::CONFIG_DEMIWARE_GROUP_DEMIWARE_QUANTITY));
    }

    //查看配置
    public function getconfig($config)
    {
        return $this->scopeConfig->getValue(
            $config,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    //活动是否有效
    public function demiwareIsActive()
    {
        $time = date('Y-m-d H:i:s');
        if ($this->getStartAction() && $time < $this->getLastDate()) {
            return true;
        }

        return false;
    }

    //获取申请人数
    public function getApplications()
    {
        $collection = $this->_demiwareCollectionFactory->create();
        $collection ->addFieldToFilter('demiware_code', $this->getDemiwareCode());
        return null === $collection ? 0 : $collection->count();

    }

    //验证此用户是否已经申请了此次活动
    public function getCustomerActionInfo($customerId = '', $actionCode = '')
    {
        $customerId = !empty($customerId) ? $customerId : $this->session->getCustomerId();
        $actionCode = !empty($actionCode) ? $actionCode : $this->getDemiwareCode();
        $collection = $this->_demiwareCollectionFactory->create();
        $collection->addFieldToFilter('customer_id', $customerId);
        $collection->addFieldToFilter('demiware_code', $actionCode);
        $collection->setPage(1, 1);
        $info = $collection->getFirstItem();
        if ($info->getId()) {
            return $info;
        }
        return false;
    }
}