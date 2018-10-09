<?php

namespace Ave40\Base\Setup;

use Magento\Sales\Model\Order;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * Class InstallData
 * @package Ave40\Base\Setup
 * 添加订单反馈状态字段
 */
class InstallData implements \Magento\Framework\Setup\InstallDataInterface
{
    private $eavSetupFactory;
    
    private $eavConfig;
    
    private $attributeResource;
    
    public function __construct(
        \Magento\Eav\Setup\EavSetupFactory $eavSetupFactory,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Sales\Model\ResourceModel\Attribute $attributeResource
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->eavConfig = $eavConfig;
        $this->attributeResource = $attributeResource;
    }
    
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        
        $eavSetup->addAttribute(
            Order::ENTITY,
            'preparing_order_no', [
                'type' => 'varchar',
                'backend' => '',
                'frontend' => '',
                'label' => 'Shipping Type',
                'input' => 'select',
                'class' => '',
                'source' => '',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'default' => 0,
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => true,
                'unique' => false,
                'apply_to' => ''
            ]
        );
        
        $attribute = $this->eavConfig->getAttribute(Customer::ENTITY, 'attribute_code');
        $attribute->setData('used_in_forms', ['adminhtml_customer']);
        $this->attributeResource->save($attribute);
    }
}