<?php
namespace Digidirect\AbstractEntity\Block\Adminhtml\Set;

use Digidirect\AbstractEntity\Block\Adminhtml\Set\Main\Formset;
use Magento\Backend\Block\AbstractBlock;
use Magento\Backend\Block\Widget\Button;

class Main extends \Magento\Catalog\Block\Adminhtml\Product\Attribute\Set\Main
{
    const BACK_ACTION = 'digidirect_abstractentity/*/';
    const MOVE_ACTION = 'digidirect_abstractentity/set/save';
    const DELETE_ACTION = 'digidirect_abstractentity/set/delete';

    /**
     * @return void
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        /** @var AbstractBlock $toolbar */
        $toolbar = $this->getToolbar();
        $this->unsetChild('edit_set_form');
        $toolbar->unsetChild('back_button');

        $this->addChild('ae_edit_set_form', Formset::class);
        $toolbar->addChild(
            'ae_back_button',
            Button::class,
            [
                'label' => __('Back'),
                'onclick' => 'setLocation(\'' . $this->getUrl(static::BACK_ACTION) . '\')',
                'class' => 'back'
            ]
        );

        if (!$this->getIsCurrentSetDefault()) {
            $toolbar->getChildBlock('delete_button')
                ->setData('onclick', 'deleteConfirm(\'' . $this->escapeJsQuote(
                    __('Are you sure  (all entity data will be deleted without back up)?')
                ) . '\', \'' . $this->getUrl(
                    static::DELETE_ACTION,
                    ['id' => $this->_getSetId()]
                ) . '\')');
        }
    }

    /**
     * Retrieve Unused in Attribute Set Attribute Tree as JSON
     *
     * @return string
     */
    public function getAttributeTreeJson()
    {
        $items = [];
        $setId = $this->_getSetId();

        $attributesIds = ['0'];
        $collection = $this->_collectionFactory->create()->setAttributeSetFilter($setId)->load();

        /* @var $item \Magento\Eav\Model\Entity\Attribute */
        foreach ($collection->getItems() as $item) {
            $attributesIds[] = $item->getAttributeId();
        }

        $attributes = $this->_collectionFactory->create()->setAttributesExcludeFilter(
            $attributesIds
        )->load();

        foreach ($attributes as $child) {
            $items[] = [
                'text' => $child->getAttributeCode(),
                'id' => $child->getAttributeId(),
                'cls' => 'leaf',
                'allowDrop' => false,
                'allowDrag' => true,
                'leaf' => true,
                'is_user_defined' => $child->getIsUserDefined(),
                'entity_id' => $child->getEntityId(),
            ];
        }

        if (count($items) == 0) {
            $items[] = [
                'text' => __('Empty'),
                'id' => 'empty',
                'cls' => 'folder',
                'allowDrop' => false,
                'allowDrag' => false,
            ];
        }

        return $this->_jsonEncoder->encode($items);
    }

    /**
     * Retrieve Attribute Set Group Tree as JSON format
     *
     * @return string
     */
    public function getGroupTreeJson()
    {
        $items = [];
        $setId = $this->_getSetId();

        /* @var $groups \Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\Collection */
        $groups = $this->_groupFactory->create()->getResourceCollection()->setAttributeSetFilter(
            $setId
        )->setSortOrder()->load();

        /* @var $node \Magento\Eav\Model\Entity\Attribute\Group */
        foreach ($groups as $node) {
            $item = [];
            $item['text'] = $node->getAttributeGroupName();
            $item['id'] = $node->getAttributeGroupId();
            $item['cls'] = 'folder';
            $item['allowDrop'] = true;
            $item['allowDrag'] = true;

            $nodeChildren = $this->_collectionFactory->create()->setAttributeGroupFilter(
                $node->getId()
            )->load();

            if ($nodeChildren->getSize() > 0) {
                $item['children'] = [];
                foreach ($nodeChildren->getItems() as $child) {
                    $item['children'][] = $this->attributeMapper->map($child);
                }
            }

            $items[] = $item;
        }

        return $this->_jsonEncoder->encode($items);
    }

    /**
     * Retrieve Attribute Set Save URL
     *
     * @return string
     */
    public function getMoveUrl()
    {
        return $this->getUrl(static::MOVE_ACTION, ['id' => $this->_getSetId()]);
    }

    /**
     * Retrieve Attribute Set Edit Form HTML
     *
     * @return string
     */
    public function getSetFormHtml()
    {
        return $this->getChildHtml('ae_edit_set_form');
    }
}
