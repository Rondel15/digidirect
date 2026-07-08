<?php
namespace Digidirect\ProductOverlay\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;

/**
 * Class AttributeGroup
 * @package Digidirect\ProductOverlay\Ui\DataProvider\Product\Form\Modifier
 */
class AttributeGroup extends AbstractModifier
{
    const OVERLAYS_GROUP_ID = 'overlays';

    /**
     * @var \Digidirect\ProductOverlay\Helper\Data
     */
    protected $_helper;

    /**
     * AttributeGroup constructor.
     * @param \Digidirect\ProductOverlay\Helper\Data $_helper
     */
    public function __construct(\Digidirect\ProductOverlay\Helper\Data $_helper)
    {
        $this->_helper = $_helper;
    }

    /**
     * @param array $meta
     * @return array
     */
    public function modifyMeta(array $meta)
    {
        /** @var $meta array */
        if (!$this->_helper->isEnabledOverlayManagement()) {
            if (!empty($meta[self::OVERLAYS_GROUP_ID])) {
                unset($meta[self::OVERLAYS_GROUP_ID]);
            }
        }
        return $meta;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data)
    {
        return $data;
    }
}
