<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_ShippingTableRates
 */


namespace Ave40\Inquire\Setup;

use Ave40\Inquire\Model\ResourceModel\Records;
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
        if($this->_isVersionLessThan('1.0.1')):
            /**/
            $this->_createTable(
                $this->_newTable(Records::TABLE)
                ->addColumn(
                    'id',
                    Table::TYPE_BIGINT,
                    null,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                    'id'
                )
                ->addColumn(
                    'sender_eamil',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => false, 'default' => ''],
                    '询盘发送者邮箱'
                )->addColumn(
                    'sender_name',
                    Table::TYPE_TEXT,
                    64,
                    ['nullable' => true, 'default' => ''],
                    '询盘发送者姓名'
                )->addColumn(
                        'sender_phone',
                        Table::TYPE_TEXT,
                        20,
                        ['nullable' => true, 'default' => ''],
                        '询盘发送者手机号'
                )->addColumn(
                    'sender_useragent',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => true, 'default' => ''],
                    '询盘发送者useragent'
                )->addColumn(
                    'sender_ip',
                    Table::TYPE_TEXT,
                    15,
                    ['nullable' => true, 'default' => ''],
                    '询盘发送者的ip'
                )->addColumn(
                    'recipient_mails',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => true, 'default' => ''],
                    '询盘接收者email'
                )
                ->addColumn(
                    'referer',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => true, 'default' => ''],
                    '询盘来源网站'
                )
                ->addColumn(
                    'content',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => true, 'default' => ''],
                    '询盘内容'
                )->addColumn(
                    'user_type',
                    Table::TYPE_TEXT,
                    20,
                    ['nullable' => false, 'default' => ''],
                    '用户类型'
                )
                ->addColumn(
                    'created_at',
                    Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE],
                    '创建时间'
                )
                ->addColumn(
                    'reading_time',
                    Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => true],
                    '询盘阅读时间'
                )
            );
        endif;/*end of 1.0.1*/
    
        if($this->_isVersionLessThan('1.0.2')):
            $this->_setup->getConnection()->addColumn(
                Records::TABLE,
                'site',
                [
                    'type' => Table::TYPE_TEXT,
                    'length' => 32,
                    'nullable' => true,
                    'default' => 'vladdin',
                    'comment' => '询盘来源站点'
                ]
                );
        endif;
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
