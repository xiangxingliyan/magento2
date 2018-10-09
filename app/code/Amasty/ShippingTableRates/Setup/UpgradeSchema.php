<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_ShippingTableRates
 */


namespace Amasty\ShippingTableRates\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '1.0.1') < 0) {
            $setup->getConnection()->addColumn(
                $setup->getTable('amasty_table_method'),
                'free_types',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => false,
                    'default' => '',
                    'length' => 255,
                    'comment' => 'Free Types'
                ]
            );
        }
        
        if(version_compare($context->getVersion(), '1.0.2', '<')) {
            $this->addFirstWeightColumnToRateTable($setup);
        }
    }
    
    /**
     * @param SchemaSetupInterface $setup
     */
    protected function addFirstWeightColumnToRateTable($setup)
    {
        $setup->getConnection()->addColumn(
            $setup->getTable('amasty_table_rate'),
            'first_weight',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_FLOAT,
                'nullable' => true,
                'default' => 0,
                'after' => 'weight_to',
                'comment' => 'First Weight'
            ]
        );
    }
}
