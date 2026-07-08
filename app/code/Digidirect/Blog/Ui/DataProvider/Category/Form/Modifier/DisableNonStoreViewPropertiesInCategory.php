<?php

namespace Digidirect\Blog\Ui\DataProvider\Category\Form\Modifier;

use Digidirect\Blog\Model\CurrentStoreFetcher;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;

/**
 * Class DisableNonStoreViewPropertiesInCategory
 */
class DisableNonStoreViewPropertiesInCategory extends AbstractModifier
{
    /**
     * @var CurrentStoreFetcher
     */
    protected $currentStoreFetcher;

    /**
     * @var array
     */
    protected $fieldCodes;

    /**
     * DisableNonStoreViewPropertiesInCategory constructor.
     * @param CurrentStoreFetcher $currentStoreFetcher
     * @param array $fieldCodes
     */
    public function __construct(
        CurrentStoreFetcher $currentStoreFetcher,
        array $fieldCodes = []
    ) {
        $this->currentStoreFetcher = $currentStoreFetcher;
        $this->fieldCodes = $fieldCodes;
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
     * @param array $meta
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function modifyMeta(array $meta)
    {
        if (!$this->currentStoreFetcher->getIsDefault()) {
            foreach ($this->fieldCodes as $fieldCode) {
                $meta['category']['children'][$fieldCode] = [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'disabled' => true
                            ],
                        ],
                    ],
                ];
            }
        }
        return $meta;
    }
}
