<?php
namespace Digidirect\Digi\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Digidirect\AbstractAttributes\Api\AbstractAttributeRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Digidirect\AbstractAttributes\Api\Data\AbstractAttributeInterface;
use Digidirect\AbstractAttributes\Api\OptionRepositoryInterface;
use Magento\Store\Api\Data\StoreInterface;

/**
 * Class Brands
 * @package Digidirect\Digi\Model\Source
 */
class Brands extends AbstractSource implements OptionSourceInterface
{
    const NAME_ATTRIBUTE_CODE = 'brand';
    /**
     * @var AbstractAttributeRepositoryInterface
     */
    private $abstractAttributeRepository;
    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;
    /**
     * @var OptionRepositoryInterface
     */
    private $optionRepository;
    /**
     * @var StoreInterface
     */
    private $storeManager;

    /**
     * Brands constructor.
     * @param AbstractAttributeRepositoryInterface $abstractAttributeRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param OptionRepositoryInterface $optionRepository
     * @param StoreInterface $storeManager
     */
    public function __construct(
        AbstractAttributeRepositoryInterface $abstractAttributeRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        OptionRepositoryInterface $optionRepository,
        StoreInterface $storeManager
    ) {
        $this->abstractAttributeRepository = $abstractAttributeRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->optionRepository = $optionRepository;
        $this->storeManager = $storeManager;
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getAllOptions()
    {
        $options = [];

        $attributes = $this->abstractAttributeRepository->getList(
            $this->searchCriteriaBuilder
                ->addFilter(AbstractAttributeInterface::ATTRIBUTE_CODE, self::NAME_ATTRIBUTE_CODE)
                ->create()
        )->getItems();

        $attrItem = array_shift($attributes);

        if ($attrItem) {
            $attrOptions = $this->optionRepository
                ->getAttributeOptions($attrItem->getAttributeId(), $this->storeManager->getStore());

            foreach ($attrOptions as $item) {
                $options[$item->getLabel() . $item->getId()] = [
                    'value' => $item->getId(),
                    'label' => $item->getLabel(),
                ];
            }

            ksort($options);
            $options = array_values($options);

        }

        return $options;
    }
}
