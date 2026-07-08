<?php
declare(strict_types=1);

namespace Digidirect\MSI\Model\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Eav\Model\Entity\Attribute\Source\SourceInterface as EavAttributeSourceInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\Option\ArrayInterface;
use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\Inventory\Model\Source\Command\GetListInterface;

class Source extends AbstractSource implements EavAttributeSourceInterface, OptionSourceInterface, ArrayInterface
{
    /**
     * @var GetListInterface
     */
    protected $sourceGetListCommand;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var SortOrderBuilder
     */
    protected $sortOrderBuilder;

    /**
     * @var null
     */
    protected $optionArray = null;

    /**
     * Source constructor.
     * @param GetListInterface $sourceGetListCommand
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param SortOrderBuilder $sortOrderBuilder
     */
    public function __construct(
        GetListInterface $sourceGetListCommand,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SortOrderBuilder $sortOrderBuilder
    ) {
        $this->sourceGetListCommand = $sourceGetListCommand;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->sortOrderBuilder = $sortOrderBuilder;
    }

    /**
     * @return array
     */
    protected function getOptionArray(): array
    {
        /**
         * @var $source SourceInterface
         */
        if ($this->optionArray === null) {
            $optionArray = [];
            $sortOrder = $this->sortOrderBuilder->setField(SourceInterface::NAME)->setAscendingDirection()->create();
            $searchCriteria = $this->searchCriteriaBuilder->addSortOrder($sortOrder)->create();
            $searchResult = $this->sourceGetListCommand->execute($searchCriteria);
            foreach ($searchResult->getItems() as $source) {
                $optionArray[$source->getSourceCode()] = $source->getName();
            }
            $this->optionArray = $optionArray;
        }
        return $this->optionArray;
    }

    /**
     * @param bool $withEmpty
     * @return string[]
     */
    public function getAllOptions($withEmpty = true): array
    {
        $result = [];
        if ($withEmpty) {
            $result[] = ['value' => '', 'label' => '-- Please select --'];
        }
        foreach ($this->getOptionArray() as $index => $value) {
            $result[] = ['value' => $index, 'label' => $value];
        }
        return $result;
    }

    /**
     * @param string $optionId
     * @return string
     */
    public function getOptionText($optionId)
    {
        $options = $this->getOptionArray();
        return $options[$optionId] ?? null;
    }

    /**
     * @param bool $isMultiselect
     * @return array
     */
    public function toOptionArray($isMultiselect = false): array
    {
        return $this->getAllOptions(!$isMultiselect);
    }
}

