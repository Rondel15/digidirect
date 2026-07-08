<?php

namespace Digidirect\Blog\Ui\DataProvider\Category\Form\Modifier;

use Digidirect\Blog\Api\Data\CategoryInterface;
use Digidirect\Blog\Model\Category;
use Digidirect\Blog\Model\CurrentStoreFetcher;
use Digidirect\Blog\Model\StoreContent\DataModifier;
use Magento\Framework\Registry;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;

class DisableNonStoreViewProperties implements ModifierInterface
{
    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var CurrentStoreFetcher
     */
    protected $currentStoreFetcher;

    /**
     * @var DataModifier
     */
    protected $dataModifier;

    /**
     * DefaultData constructor.
     *
     * @param Registry $registry
     * @param CurrentStoreFetcher $currentStoreFetcher
     * @param DataModifier $dataModifier
     */
    public function __construct(
        Registry $registry,
        CurrentStoreFetcher $currentStoreFetcher,
        DataModifier $dataModifier
    ) {
        $this->dataModifier = $dataModifier;
        $this->currentStoreFetcher = $currentStoreFetcher;
        $this->registry = $registry;
    }

    /**
     * @param array $data
     * @return array
     */
    public function modifyData(array $data)
    {
        return $data;
    }

    /**
     * @return CategoryInterface|Category
     */
    protected function getCurrentCategory()
    {
        return $this->registry->registry(CategoryInterface::CURRENT_ITEM);
    }

    /**
     * @param array $meta
     * @return array
     */
    public function modifyMeta(array $meta)
    {
        if ($this->currentStoreFetcher->getIsDefault()) {
            return $meta;
        }
        return $meta;
    }
}
