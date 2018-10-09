<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_ShippingTableRates
 */


namespace Ave40\Base\Setup;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    protected $_version;
    /** @var SchemaSetupInterface $setup */
    protected $_setup;
    /** @var ModuleContextInterface $context */
    protected $_context;
    
    /**
     * @throws \Zend_Db_Exception
     */
    protected function _doUpgrade() {
        if($this->_isVersionLessThan('1.1.0')):
            $this->_createTable(
                $this->_newTable(\Ave40\Base\Model\ResourceModel\Shippingmapping::TABLE)
                    ->addColumn(
                        \Ave40\Base\Model\ResourceModel\Shippingmapping::PRIMARY,
                        Table::TYPE_INTEGER,
                        12,
                        ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                        'Id'
                    )
                    ->addColumn(
                        'shipping_code',
                        Table::TYPE_TEXT,
                        255,
                        ['default' => null],
                        '物流编码'
                    )
                    ->addColumn(
                        'erp_code',
                        Table::TYPE_TEXT,
                        255,
                        ['default' => null],
                        'erp物流编码'
                    )
                    ->addColumn(
                        'country',
                        Table::TYPE_TEXT,
                        3,
                        ['default' => '','nullable' => false],
                        '国家, 为空为全部国家'
                    )
                    ->addColumn(
                        'battery',
                        Table::TYPE_INTEGER,
                        1,
                        ['default' => 0],
                        '是否带电, 0全部, 1带电, 2不带电'
                    )
                    ->addColumn(
                        'last_updated_at',
                        Table::TYPE_DATETIME,
                        null,
                        [],
                        '最后更新时间'
                    )
                    ->addIndex(
                        'uk_ave40_erp_shipping_mapping_cccb4',
                        ['shipping_code', 'erp_code', 'country', 'battery'],
                        ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
                    )
            );

            /**/
            $this->_createTable(
                $this->_newTable(\Ave40\Base\Model\ResourceModel\MdwRecords::TABLE)
                ->addColumn(
                    'id',
                    Table::TYPE_BIGINT,
                    null,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                    'id'
                )
                ->addColumn(
                    'type',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => false, 'default' => ''],
                    '类型, 比如order_upload'
                )->addColumn(
                    'status',
                    Table::TYPE_TEXT,
                    32,
                    ['nullable' => false, 'default' => 'pending'],
                    '状态, 如completed, pending'
                )->addColumn(
                    'fail_count',
                    Table::TYPE_INTEGER,
                    10,
                    ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                    '失败次数'
                )->addColumn(
                    'entity_id',
                    Table::TYPE_INTEGER,
                    10,
                    ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                    '实体id'
                )->addColumn(
                    'message',
                    Table::TYPE_TEXT,
                    255,
                    ['default' => ''],
                    '日志内容'
                )->addColumn(
                    'ext_data',
                    Table::TYPE_TEXT,
                    null,
                    ['default' => ''],
                    '其他信息'
                )->addColumn(
                    'created_at',
                    Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE],
                    '创建时间'
                )->addColumn(
                    'last_updated_at',
                    Table::TYPE_DATETIME,
                    null,
                    ['nullable' => true, 'default' => null],
                    '最后更新时间'
                )->addIndex('type__entity_id',
                    ['type', 'entity_id'],
                    ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
                )->setComment("Erp 同步日志表")
            );
        endif;/*end of 1.1.0*/

        if($this->_isVersionLessThan('1.1.1')):
            $this->_createTable(
                $this->_newTable(\Ave40\Base\Model\ResourceModel\Demiware::TABLE)
                    ->addColumn(
                        \Ave40\Base\Model\ResourceModel\Demiware::PRIMARY,
                        Table::TYPE_INTEGER,
                        12,
                        ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                        'Id'
                    )->addColumn(
                        'demiware_code',
                        Table::TYPE_TEXT,
                        32,
                        ['default' => null],
                        '活动编号'
                    )->addColumn(
                        'first_name',
                        Table::TYPE_TEXT,
                        64,
                        ['default' => null],
                        '姓'
                    )->addColumn(
                        'last_name',
                        Table::TYPE_TEXT,
                        64,
                        ['default' => null],
                        '名'
                    )->addColumn(
                        'customer_id',
                        Table::TYPE_INTEGER,
                        12,
                        ['default' => 0],
                        '客户'
                    )->addColumn(
                        'country',
                        Table::TYPE_TEXT,
                        32,
                        ['default' => '','nullable' => false],
                        '国家'
                    )->addColumn(
                        'email',
                        Table::TYPE_TEXT,
                        32,
                        ['default' => '','nullable' => false],
                        '邮箱'
                    )->addColumn(
                        'street',
                        Table::TYPE_TEXT,
                        64,
                        ['default' => '','nullable' => false],
                        '街道'
                    )->addColumn(
                        'city',
                        Table::TYPE_TEXT,
                        32,
                        ['default' => '','nullable' => false],
                        '城市'
                    )->addColumn(
                        'state',
                        Table::TYPE_TEXT,
                        32,
                        ['default' => '','nullable' => false],
                        '州/省'
                    )->addColumn(
                        'phone',
                        Table::TYPE_TEXT,
                        32,
                        ['default' => '','nullable' => false],
                        '电话'
                    )->addColumn(
                        'smoke_for_years',
                        Table::TYPE_TEXT,
                        32,
                        ['default' => '','nullable' => false],
                        '电子烟烟龄'
                    )->addColumn(
                        'reason',
                        Table::TYPE_TEXT,
                        512,
                        ['default' => '','nullable' => false],
                        '申请理由'
                    )->addColumn(
                        'other_pod_systems',
                        Table::TYPE_TEXT,
                        128,
                        ['default' => '','nullable' => false],
                        '使用过别的什么电子系统'
                    )->addColumn(
                        'last_updated_at',
                        Table::TYPE_DATETIME,
                        null,
                        [],
                        '最后更新时间'
                    )->addIndex(
                        'uk_ave40_demiware',
                        ['customer_id', 'demiware_code'],
                        ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
                    )
            );


        endif;/*end of 1.1.1*/
    }
    
    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws \Zend_Db_Exception
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context) {
        $setup->startSetup();
        $this->_setup = $setup;
        $this->_context = $context;
        $this->_version = $context->getVersion();
        $this->_doUpgrade();
        $setup->endSetup();
    }
    
    /**
     * 版本比较
     * @param $ver
     * @return bool
     */
    protected function _isVersionLessThan($ver) {
        return version_compare($this->_version, $ver) < 0;
    }
    
    protected function _isTableExists($table) {
        return $this->getConnection()->isTableExists($table);
    }
    
    protected function getContext() {
        return $this->_context;
    }
    
    protected function getSetup() {
        return $this->_setup;
    }
    
    protected function getConnection() {
        return $this->getSetup()->getConnection();
    }
    
    protected function _newTable($table) {
        return $this->getConnection()->newTable($table);
    }
    
    /**
     * @param Table $table
     * @return \Zend_Db_Statement_Interface
     * @throws \Zend_Db_Exception
     */
    protected function _createTable($table) {
        return $this->getConnection()->createTable($table);
    }
    
    protected function _addColumn($table, $columnName, $definiton) {
        return $this->getConnection()->addColumn($table, $columnName, $definiton);
    }
}
