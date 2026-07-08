<?php
namespace Digidirect\Digi\Setup;

use Digidirect\AbstractEntity\Setup\AbstractEntitySetup;

/**
 * Class SeoBrandDescriptionEntitySetup
 *
 * @package Digidirect\Store\Setup
 */
class SeoBrandDescriptionEntitySetup extends AbstractEntitySetup
{
    const ABSTRACT_ENTITY_NAME = 'Seo Brand Description';

    /**
     * @param null $entities
     * @return void
     */
    public function installEntities($entities = null)
    {
        $attributeSet = $this->getOrCreateAttributeSet(self::ABSTRACT_ENTITY_NAME);
        parent::installEntities($entities);
        $this->addAttributeToEntity($attributeSet, [
            'catogory' => [
                'label' => 'Category',
                'type' => 'int',
                'input' => 'select',
                'source' => \Digidirect\Digi\Model\Source\Categories::class,
                'backend' => \Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend::class,
                'required' => false,
                'visible' => true,
                'visible_on_front' => false,
                'sort_order' => 20,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'used_in_product_listing' => false,
                'used_for_sort_by' => false,
                'user_defined' => true,
            ],
            'brand' => [
                'label' => 'Brand',
                'type' => 'int',
                'input' => 'select',
                'source' => \Digidirect\Digi\Model\Source\Brands::class,
                'backend' => \Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend::class,
                'required' => false,
                'visible' => true,
                'visible_on_front' => false,
                'sort_order' => 30,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'used_in_product_listing' => false,
                'used_for_sort_by' => false,
                'user_defined' => true,
            ],
            'category_brand_description' => [
                'type' => 'text',
                'label' => 'Category & Brand description',
                'input' => 'textarea',
                'required' => false,
                'sort_order' => 40,
                'position' => 40,
                'user_defined' => true,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'wysiwyg_enabled' => true,
                'is_html_allowed_on_front' => true
            ]
        ]);
    }
}
