<?php
namespace Digidirect\ProductOverlay\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\CatalogRule\Model\ResourceModel\Rule\CollectionFactory as CatalogRuleCollectionFactory;

/**
 * Class CatalogPriceRules
 *
 * @package Digidirect\ProductOverlay\Model\Source
 */
class CatalogPriceRules implements OptionSourceInterface
{
    /**
     * @var CatalogRuleCollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var null
     */
    protected $options = null;

    /**
     * CatalogPriceRules constructor.
     *
     * @param CatalogRuleCollectionFactory $collectionFactory
     */
    public function __construct(CatalogRuleCollectionFactory $collectionFactory)
    {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @return array|null
     */
    public function toOptionArray()
    {
        if (null === $this->options) {
            /**
             * @var $collection \Magento\CatalogRule\Model\ResourceModel\Rule\Collection
             */
            $collection = $this->collectionFactory->create();
            foreach ($collection as $item) {
                $this->options[] = [
                    'value' => $item->getId(),
                    'label' => __($item->getName())
                ];
            }
        }
        return $this->options;
    }
}
