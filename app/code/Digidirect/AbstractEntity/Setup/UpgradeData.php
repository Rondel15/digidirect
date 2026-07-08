<?php

namespace Digidirect\AbstractEntity\Setup;

use Digidirect\AbstractEntity\Api\Data\AbstractEntityInterface;
use Digidirect\AbstractEntity\Model\AbstractEntity;
use Digidirect\AbstractEntity\Model\ResourceModel\Eav\Attribute;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * Setup factory
     *
     * @var AbstractEntitySetupFactory
     */
    protected $abstractEntitySetupFactory;

    /**
     * @param AbstractEntitySetupFactory $abstractEntitySetupFactory
     */
    public function __construct(AbstractEntitySetupFactory $abstractEntitySetupFactory)
    {
        $this->abstractEntitySetupFactory = $abstractEntitySetupFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        /** @var \Digidirect\AbstractEntity\Setup\AbstractEntitySetup $abstractEntitySetup */
        $abstractEntitySetup = $this->abstractEntitySetupFactory->create(['setup' => $setup]);
        if (version_compare($context->getVersion(), '1.0.1', '<')) {
            $newAttributes = [
                AbstractEntityInterface::VISIBLE_ON_FRONTEND => [
                    'type' => 'int',
                    'label' => 'Visible on Frontend',
                    'input' => 'select',
                    'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                    'required' => false,
                    'sort_order' => 130,
                    'position' => 130,
                    'default' => '1',
                    'is_global' => ScopedAttributeInterface::SCOPE_WEBSITE,
                    'group' => 'General'
                ],
                AbstractEntityInterface::URL_KEY => [
                    'type' => 'varchar',
                    'label' => 'URL Key',
                    'input' => 'text',
                    'required' => false,
                    'sort_order' => 140,
                    'position' => 140,
                    'is_global' => ScopedAttributeInterface::SCOPE_STORE,
                    'group' => 'General'
                ]
            ];

            foreach ($newAttributes as $attributeCode => $attributeData) {
                $abstractEntitySetup->addAttribute(AbstractEntity::ENTITY_TYPE, $attributeCode, $attributeData);
                $abstractEntitySetup->updateAttribute(
                    AbstractEntity::ENTITY_TYPE,
                    $attributeCode,
                    'is_global',
                    $attributeData['is_global'],
                    $attributeData['sort_order'] ?? null
                );
            }
        }

        if (version_compare($context->getVersion(), '1.0.2', '<')) {
            $attribute = $abstractEntitySetup->getAttribute(
                AbstractEntity::ENTITY_TYPE,
                AbstractEntityInterface::VISIBLE_ON_FRONTEND
            );
            if ($attribute) {
                $abstractEntitySetup->updateAttribute(
                    AbstractEntity::ENTITY_TYPE,
                    AbstractEntityInterface::VISIBLE_ON_FRONTEND,
                    'is_global',
                    ScopedAttributeInterface::SCOPE_STORE
                );
            }
        }

        if (version_compare($context->getVersion(), '1.0.7', '<')) {
            $newAttributes = [
                AbstractEntityInterface::ADD_TO_SITEMAP => [
                    'type' => 'int',
                    'label' => 'Add Page To Sitemap',
                    'input' => 'select',
                    'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                    'required' => false,
                    'sort_order' => 135,
                    'position' => 135,
                    'default' => '1',
                    'is_global' => ScopedAttributeInterface::SCOPE_STORE,
                    'group' => 'General'
                ]
            ];

            foreach ($newAttributes as $attributeCode => $attributeData) {
                $abstractEntitySetup->addAttribute(AbstractEntity::ENTITY_TYPE, $attributeCode, $attributeData);
                $abstractEntitySetup->updateAttribute(
                    AbstractEntity::ENTITY_TYPE,
                    $attributeCode,
                    'is_global',
                    $attributeData['is_global'],
                    $attributeData['sort_order'] ?? null
                );
            }
        }

        if (version_compare($context->getVersion(), '1.1.1', '<')) {
            foreach ([AbstractEntityInterface::NAME, AbstractEntityInterface::STATUS] as $gridAttribute) {
                $attribute = $abstractEntitySetup->getAttribute(
                    AbstractEntity::ENTITY_TYPE,
                    $gridAttribute
                );
                if ($attribute) {
                    $abstractEntitySetup->updateAttribute(
                        AbstractEntity::ENTITY_TYPE,
                        $gridAttribute,
                        Attribute::KEY_IS_USED_IN_GRID,
                        1
                    );
                    $abstractEntitySetup->updateAttribute(
                        AbstractEntity::ENTITY_TYPE,
                        $gridAttribute,
                        Attribute::KEY_IS_FILTERABLE_IN_GRID,
                        1
                    );
                }
            }
        }

        $setup->endSetup();
    }
}