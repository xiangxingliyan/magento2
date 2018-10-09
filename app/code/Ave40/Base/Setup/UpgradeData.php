<?php

namespace  Ave40\Base\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Sales\Setup\SalesSetupFactory;
use Magento\Sales\Model\Order;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * Sales setup factory
     *
     * @var SalesSetupFactory
     */
    protected $salesSetupFactory;
    
    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $eavConfig;
    
    protected $_setup;
    
    protected $_context;
    
    protected $_version;
    
    /**
     * @param SalesSetupFactory $salesSetupFactory
     * @param \Magento\Eav\Model\Config $eavConfig
     */
    public function __construct(
        SalesSetupFactory $salesSetupFactory,
        \Magento\Eav\Model\Config $eavConfig
    ) {
        $this->salesSetupFactory = $salesSetupFactory;
        $this->eavConfig = $eavConfig;
    }
    
    
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $this->_setup = $setup;
        $this->_context = $context;
        $this->_version = $context->getVersion();
        $this->_doUpgrade();
        $setup->endSetup();

    }
    
    protected function _doUpgrade() {
    
        /** @var SalesSetup $salesSetup */
        $salesSetup = $this->salesSetupFactory->create(['resourceName' => 'sales_setup','setup' => $this->_setup]);
    
        if (version_compare($this->_version, '1.0.1', '<')) {
            $salesSetup->addAttribute(
                Order::ENTITY,
                'preparing_order_no',
                [
                    'type' => 'varchar',
                    'length'=> 64,
                    'visible' => false,
                    'nullable' => true,
                    'required' => false,
                    'default' => NULL,
                    'comment' => '备货单号'
                ]
            );
    
            $salesSetup->addAttribute(
                Order::ENTITY,
                'waybill_no',
                [
                    'type' => 'varchar',
                    'length'=> 64,
                    'visible' => false,
                    'nullable' => true,
                    'required' => false,
                    'default' => NULL,
                    'comment' => '运单号'
                ]
            );
    
            $salesSetup->addAttribute(
                Order::ENTITY,
                'actual_amount_to_account',
                [
                    'type' => 'decimal',
                    'length' => 10,
                    'size' => 2,
                    'visible' => false,
                    'nullable' => true,
                    'required' => false,
                    'default' => NULL,
                    'comment' => '实际到账金额'
                ]
            );
    
            $salesSetup->addAttribute(
                Order::ENTITY,
                'last_logistics_info',
                [
                    'type' => 'varchar',
                    'length'=> 255,
                    'visible' => false,
                    'nullable' => true,
                    'required' => false,
                    'default' => NULL,
                    'comment' => '最后一条物流信息'
                ]
            );
    
            $salesSetup->addAttribute(
                Order::ENTITY,
                'erp_order_id',
                [
                    'type' => 'varchar',
                    'length'=> 64,
                    'visible' => false,
                    'nullable' => true,
                    'required' => false,
                    'default' => NULL,
                    'comment' => 'ERP订单号'
                ]
            );
    
            $salesSetup->addAttribute(
                Order::ENTITY,
                'logistics_provider',
                [
                    'type' => 'varchar',
                    'length'=> 64,
                    'visible' => false,
                    'nullable' => true,
                    'required' => false,
                    'default' => NULL,
                    'comment' => '物流商'
                ]
            );
    
            $salesSetup->addAttribute(
                Order::ENTITY,
                'express_no',
                [
                    'type' => 'varchar',
                    'length'=> 64,
                    'visible' => false,
                    'nullable' => true,
                    'required' => false,
                    'default' => NULL,
                    'comment' => '快递单号'
                ]
            );
    
            $salesSetup->addAttribute(
                Order::ENTITY,
                'delivery_no',
                [
                    'type' => 'varchar',
                    'length'=> 64,
                    'visible' => false,
                    'nullable' => true,
                    'required' => false,
                    'default' => NULL,
                    'comment' => '发货单号'
                ]
            );
    
            $salesSetup->addAttribute(
                Order::ENTITY,
                'transfer_no',
                [
                    'type' => 'varchar',
                    'length'=> 64,
                    'visible' => false,
                    'nullable' => true,
                    'required' => false,
                    'default' => NULL,
                    'comment' => '部分物流的转单号, 可以根据转单号到物流运输公司查询'
                ]
            );
    
        }
        $this->eavConfig->clear();
    }
}
