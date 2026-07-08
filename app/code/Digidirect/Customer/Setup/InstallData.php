<?php
namespace Digidirect\Customer\Setup;

use Magento\Customer\Model\Customer;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class InstallData implements \Magento\Framework\Setup\InstallDataInterface
{
    private $eavSetupFactory;
    
    private $eavConfig;
    
    private $attributeResource;
    
    public function __construct(
        \Magento\Eav\Setup\EavSetupFactory $eavSetupFactory,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Customer\Model\ResourceModel\Attribute $attributeResource
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->eavConfig = $eavConfig;
        $this->attributeResource = $attributeResource;
    }
    
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    { 
        $attrsData = [
            
            'qff_lastname' => [
                'type' => 'varchar',
                'label' => 'QFF Lastname',
                'input' => 'text',
                'required' => false,
                'visible' => false,
                'user_defined' => true,
                'sort_order' => 200,
                'position' => 210,
                'system' => 0,
            ],
            'qff_number' => [
                'type' => 'varchar',
                'label' => 'QFF Number',
                'input' => 'text',
                'required' => false,
                'visible' => false,
                'user_defined' => true,
                'sort_order' => 210,
                'position' => 210,
                'system' => 0,
            ],
            'qff_points' => [
                'type' => 'int',
                'label' => 'QFF Total Points',
                'input' => 'text',
                'required' => false,
                'visible' => false,
                'user_defined' => true,
                'sort_order' => 220,
                'position' => 220,
                'system' => 0,
            ]
        ];
        
        foreach ($attrsData as $attrCode => $attrData) 
        {
            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
            
            $entityName = $attrCode;
            
            $eavSetup->removeAttribute(Customer::ENTITY, $entityName);
            
            $attributeSetId = $eavSetup->getDefaultAttributeSetId(Customer::ENTITY);
            $attributeGroupId = $eavSetup->getDefaultAttributeGroupId(Customer::ENTITY);
            
            $eavSetup->addAttribute(Customer::ENTITY, $entityName, $attrData);
            
            $attribute = $this->eavConfig->getAttribute(Customer::ENTITY, $entityName);
            $attribute->setData('attribute_set_id', $attributeSetId);
            $attribute->setData('attribute_group_id', $attributeGroupId);
            
            $attribute->setData('used_in_forms', [
                'adminhtml_customer',
                'customer_account_create',
                'customer_account_edit'
            ]);

            $this->attributeResource->save($attribute);
        }
    }
}
?>