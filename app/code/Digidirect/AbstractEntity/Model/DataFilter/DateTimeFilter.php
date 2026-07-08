<?php

namespace Digidirect\AbstractEntity\Model\DataFilter;

use Digidirect\AbstractEntity\Model\DataFilterInterface;
use Digidirect\AbstractEntity\Model\AbstractEntity;
use Magento\Eav\Model\Config;
use Magento\Framework\Exception\LocalizedException;

class DateTimeFilter implements DataFilterInterface
{
    /**
     * @var Config
     */
    protected $eavConfig;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\Filter\Date
     */
    protected $dateFilter;

    /**
     * DateTimeFilter constructor.
     * @param Config $eavConfig
     * @param \Magento\Framework\Stdlib\DateTime\Filter\Date $dateFilter
     */
    public function __construct(
        Config $eavConfig,
        \Magento\Framework\Stdlib\DateTime\Filter\Date $dateFilter
    ) {
        $this->eavConfig = $eavConfig;
        $this->dateFilter = $dateFilter;
    }

    /**
     * @param array $data
     * @return void
     * @throws LocalizedException
     */
    public function execute(array &$data)
    {
        if (!empty($data['attribute_set_id'])) {
            $entityType = $this->eavConfig->getEntityType(AbstractEntity::ENTITY_TYPE);
            $attributes = $entityType->getAttributeCollection($data['attribute_set_id'])->getItems();
            $dateFieldFilters = [];
            foreach ($attributes as $attribute) {
                if ($attribute->getBackend()->getType() == 'datetime') {
                    $dateFieldFilters[$attribute->getAttributeCode()] = $this->dateFilter;
                }
            }

            // make sure the date is converted to internal format
            $inputFilter = new \Magento\Framework\Filter\FilterInput(
                $dateFieldFilters,
                [],
                $data
            );

            try {
                $data = $inputFilter->getUnescaped();
            } catch (\Exception $e) {
                throw new LocalizedException(__('Post Data Validation Error: %1', $e->getMessage()));
            }
        }
    }
}
