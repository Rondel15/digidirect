<?php
namespace Digidirect\AbstractEntity\Helper;

use Digidirect\AbstractEntity\Api\Data\AbstractEntityInterface;
use Digidirect\AbstractEntity\Api\AbstractEntityRepositoryInterface;
use Digidirect\AbstractEntity\Model\AbstractEntityRepository;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class Data
 * @package Digidirect\AbstractEntity\Helper
 */
class Loader extends AbstractHelper
{
    /**
     * @var AbstractEntityRepositoryInterface|AbstractEntityRepository
     */
    protected $abstractEntityRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * Loader constructor.
     * @param Context $context
     * @param AbstractEntityRepositoryInterface $abstractEntityRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        Context $context,
        AbstractEntityRepositoryInterface $abstractEntityRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        parent::__construct($context);
        $this->abstractEntityRepository = $abstractEntityRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @param int|string|array $ids
     * @param string|null $entityName
     * @param array $attributes
     * @param int $status
     * @return AbstractEntityInterface[]|AbstractEntityInterface|null
     */
    public function getAbstractEntitiesByIds($ids, $entityName = null, array $attributes = [], $status = null)
    {
        if (is_int($ids)) {
            try {
                return $this->abstractEntityRepository->getById($ids);
            } catch (NoSuchEntityException $e) {
                return null;
            }
        }

        if (is_string($ids)) {
            $ids = array_filter(array_map('trim', explode(',', $ids)));
        }

        if (!empty($ids)) {
            $this->searchCriteriaBuilder->addFilter(AbstractEntityInterface::ENTITY_ID, $ids, 'in');
            if (null !== $status) {
                $this->searchCriteriaBuilder->addFilter(AbstractEntityInterface::STATUS, $status);
            }

            return $this->abstractEntityRepository->getList(
                $this->searchCriteriaBuilder->create(),
                $entityName,
                $attributes
            )->getItems();
        }

        return [];
    }

    /**
     * @param $entityName
     * @param array $attributes
     * @param null $status
     * @return AbstractEntityInterface[]
     */
    public function getAbstractEntities($entityName, array $attributes = [], $status = null)
    {
        if (null !== $status) {
            $this->searchCriteriaBuilder->addFilter(AbstractEntityInterface::STATUS, $status);
        }

        return $this->abstractEntityRepository->getList(
            $this->searchCriteriaBuilder->create(),
            $entityName,
            $attributes
        )->getItems();
    }

    /**
     * @param null $entityName
     * @param array $attributes
     * @return \Magento\Framework\Data\Collection\AbstractDb
     */
    public function getCollection($entityName = null, array $attributes = [])
    {
        return $this->abstractEntityRepository->getCollection($entityName, $attributes);
    }
}
