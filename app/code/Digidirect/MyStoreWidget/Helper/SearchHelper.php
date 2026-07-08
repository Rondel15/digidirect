<?php

namespace Digidirect\MyStoreWidget\Helper;

use Magento\Framework\DB\Select;

class SearchHelper
{
    /**
     * @var array
     */
    protected $rangeAttributes;

    /**
     * @var Config
     */
    protected $configHelper;

    /**
     * SearchHelper constructor.
     *
     * @param array $rangeAttributes
     * @param Config $configHelper
     */
    public function __construct(
        Config $configHelper,
        $rangeAttributes = ['postcode']
    ) {
        $this->rangeAttributes = $rangeAttributes;
        $this->configHelper = $configHelper;
    }

    /**
     * @param Select $select
     * @param string $searchTerm
     * @return void
     */
    public function addRangeSearch(Select $select, $searchTerm)
    {
        $searchAttributes = $this->configHelper->getAttributes();
        if (trim($searchTerm) && is_numeric($searchTerm)) {
            foreach ($this->rangeAttributes as $range) {
                if (!in_array($range, $searchAttributes)) {
                    continue;
                }
                $from = 'SUBSTRING_INDEX(' . $range . ', "-", 1)';
                $to = 'SUBSTRING_INDEX(' . $range . ', "-", -1)';
                $select->where('? BETWEEN ' . $from . ' AND ' . $to, (int)$searchTerm);
            }
        }
    }
}
