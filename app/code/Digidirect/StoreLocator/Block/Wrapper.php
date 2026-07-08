<?php

namespace Digidirect\StoreLocator\Block;

/**
 * Class Wrapper
 * @package Digidirect\StoreLocator\Block
 */
class Wrapper extends \Digidirect\StoreLocator\Block\AbstractBlock
{
    const DEFAULT_STORE_LOCATOR_BLOCK = 'storelocator.locator';

    /**
     * @return string
     */
    public function getEntities()
    {
        $child = $this->getData('child_name') ?: static::DEFAULT_STORE_LOCATOR_BLOCK;
        $block = $this->getChildBlock($child);
        if (!$block) {
            return json_encode([]);
        }
        $entities = $block->getEntities();

        return $entities;
    }

    /**
     * @return \Magento\Framework\View\Element\AbstractBlock
     */
    protected function _prepareLayout()
    {
        $this->pageConfig->setDescription($this->configHelper->getPageMetaDescription());

        return parent::_prepareLayout();
    }
}
