<?php
namespace Digidirect\AbstractEntity\Plugin\Backend\Model\Menu\Config;

use Digidirect\AbstractEntity\Controller\Adminhtml\AbstractEntity as AbstractEntityController;
use Digidirect\AbstractEntity\Model\AttributeSetRepository;

class Reader
{
    const SET_ACTION = 'digidirect_abstractentity/abstractentity/index/attribute_set_id/';

    /**
     * @var AttributeSetRepository
     */
    protected $attributeSetRepository;

    /**
     * @param AttributeSetRepository $attributeSetRepository
     */
    public function __construct(
        AttributeSetRepository $attributeSetRepository
    ) {
        $this->attributeSetRepository = $attributeSetRepository;
    }

    /**
     * @param string $attributeSetName
     * @return string
     */
    public function getMenuItemName($attributeSetName)
    {
        if (strlen($attributeSetName) > 50) {
            $attributeSetName = substr($attributeSetName, 0, 47) . '...';
        }
        if (strlen($attributeSetName) < 3) {
            $attributeSetName .= '...';
        }
        return $attributeSetName;
    }

    /**
     * @param \Magento\Backend\Model\Menu\Config\Reader $subject
     * @param array $result
     * @return array
     */
    public function afterRead(
        \Magento\Backend\Model\Menu\Config\Reader $subject,
        array $result
    ) {
        $sets = $this->attributeSetRepository->getList()->getItems();
        foreach ($sets as $set) {
            $title = $this->getMenuItemName($set->getAttributeSetName());
            $result[] = [
                'type' => 'add',
                'title' => $title,
                'id' => AbstractEntityController::ADMIN_RESOURCE_PREFIX . $set->getAttributeSetId(),
                'resource' => AbstractEntityController::ADMIN_RESOURCE_PREFIX . $set->getAttributeSetId(),
                'parent' => 'Digidirect_AbstractEntity::menu',
                'module' => 'Digidirect_AbstractEntity',
                'sortOrder' => $set->getAttributeSetId() * 10,
                'action' => static::SET_ACTION . $set->getAttributeSetId(),
            ];
        }
        return $result;
    }
}
