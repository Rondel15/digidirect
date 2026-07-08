<?php
namespace Digidirect\MyStoreWidget\CustomerData;

use Magento\Customer\CustomerData\SectionSourceInterface;
use Digidirect\MyStoreWidget\Helper\Data as Helper;

/**
 * Class MyStore
 * @package Digidirect\MyStoreWidget\CustomerData
 */
class MyStore implements SectionSourceInterface
{
    /**
     * @var Helper
     */
    protected $helper;

    /**
     * MyStore constructor.
     * @param Helper $helper
     */
    public function __construct(
        Helper $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * @return array
     */
    public function getSectionData()
    {
        $currentStoreData = [];
        $currentStore = $this->helper->getCurrentStore();
        if ($currentStore) {
            $currentStoreData = $currentStore->getData();
            $currentStoreData['entity_name'] = $currentStore->getEntityName();
        }

        return [
            'currentStoreData' => $currentStoreData
        ];
    }
}
