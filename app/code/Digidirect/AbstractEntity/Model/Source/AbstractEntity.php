<?php
namespace Digidirect\AbstractEntity\Model\Source;

use Digidirect\AbstractEntity\Api\AbstractEntityRepositoryInterface;
use Digidirect\AbstractEntity\Api\Data\AbstractEntityInterface;
use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Eav\Model\ResourceModel\Helper as EavResourceHelper;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\ObjectManager;

/**
 * Class AbstractEntity
 *
 * @package Digidirect\AbstractEntity\Model\Source
 */
class AbstractEntity extends AbstractSource
{
    const DEFAULT_FIELD_FOR_OPTION_LABEL = 'name';

    /**
     * @var AbstractEntityRepositoryInterface
     */
    protected $abstractEntityRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var EavResourceHelper
     */
    protected $eavResourceHelper;

    /**
     * AbstractEntity constructor.
     *
     * @param AbstractEntityRepositoryInterface $abstractEntityRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Eav\Model\ResourceModel\Helper $eavResourceHelper
     */
    public function __construct(
        AbstractEntityRepositoryInterface $abstractEntityRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        EavResourceHelper $eavResourceHelper = null
    ) {
        $this->abstractEntityRepository = $abstractEntityRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->eavResourceHelper = $eavResourceHelper ?: ObjectManager::getInstance()->get(EavResourceHelper::class);
    }

    /**
     * Retrieve All options
     *
     * @return array
     */
    public function getAllOptions()
    {
        $options = [];
        $options[] = [
            'label' => ' ',
            'value' => '',
        ];

        $abstractEntityType = $this->getAttribute()->getSourceEntityType() ?: $this->getAttribute()->getNote();
        if (empty($abstractEntityType)) {
            return $options;
        }

        $abstractEntities = $this->abstractEntityRepository->getList(
            $this->searchCriteriaBuilder->create(),
            $abstractEntityType,
            [self::DEFAULT_FIELD_FOR_OPTION_LABEL]
        )->getItems();

        foreach ($abstractEntities as $entity) {
            /** @var AbstractEntityInterface $entity */
            $options[] = [
                'label' => $entity->getName(),
                'value' => $entity->getId(),
            ];
        }

        return $options;
    }

    /**
     * Retrieve flat column definition
     *
     * @return array
     */
    public function getFlatColumns()
    {
        $attributeType = $this->getAttribute()->getBackendType();
        $attributeCode = $this->getAttribute()->getAttributeCode();

        return [
            $attributeCode => [
                'unsigned' => true,
                'default' => null,
                'extra' => null,
                'type' => $this->eavResourceHelper->getDdlTypeByColumnType($attributeType),
                'nullable' => true,
            ],
        ];
    }
}
