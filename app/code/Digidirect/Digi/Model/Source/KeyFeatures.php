<?php
namespace Digidirect\Digi\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Digidirect\AbstractEntity\Model\ResourceModel\AbstractEntity as AbstractEntityResource;
use Digidirect\AbstractEntity\Api\AbstractEntityRepositoryInterface;
use Digidirect\AbstractEntity\Api\Data\AbstractEntityInterface;
use Digidirect\AbstractEntity\Model\AbstractEntity;

/**
 * Class KeyFeatures
 * @package Digidirect\Digi\Model\Source
 */
class KeyFeatures extends AbstractSource implements OptionSourceInterface
{
    const ATTRIBUTE_SET_NAME = 'Key Feature';
    const NAME_ATTRIBUTE_CODE = 'name';

    /**
     * @var AbstractEntityRepositoryInterface
     */
    protected $abstractEntityRepository;

    /**
     * @var AbstractEntityResource
     */
    protected $abstractEntityResource;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * Entity constructor.
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param AbstractEntityRepositoryInterface $abstractEntityRepository
     * @param AbstractEntityResource $abstractEntityResource
     */
    public function __construct(
        SearchCriteriaBuilder $searchCriteriaBuilder,
        AbstractEntityRepositoryInterface $abstractEntityRepository,
        AbstractEntityResource $abstractEntityResource
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->abstractEntityRepository = $abstractEntityRepository;
        $this->abstractEntityResource = $abstractEntityResource;
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getAllOptions()
    {
        $options = [];
        $attributeSetId = $this->abstractEntityResource->getAttributeSetIdByName(self::ATTRIBUTE_SET_NAME);

        if (!empty($attributeSetId)) {
            $searchCriteria = $this->searchCriteriaBuilder
                ->addFilter(AbstractEntityInterface::ATTRIBUTE_SET_ID, $attributeSetId)
                ->create();

            $stores = $this->abstractEntityRepository->getList(
                $searchCriteria,
                AbstractEntity::ENTITY_TYPE,
                [self::NAME_ATTRIBUTE_CODE]
            );

            foreach ($stores->getItems() as $item) {
                $options[] = [
                    'value' => $item->getId(),
                    'label' => $item->getName(),
                ];
            }
        }

        return $options;
    }
}
