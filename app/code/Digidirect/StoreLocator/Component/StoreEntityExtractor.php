<?php

namespace Digidirect\StoreLocator\Component;

use Digidirect\AbstractEntity\Api\AttributeSetRepositoryInterface;
use Digidirect\StoreLocator\Helper\Config;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory;

class StoreEntityExtractor
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * @var AttributeSetRepositoryInterface
     */
    protected $attributeSetRepository;

    /**
     * @var
     */
    protected $storeCollectionFactory;

    /**
     * @var array
     */
    protected $setByName = [];

    /**
     * @var array
     */
    protected $setById = [];

    /**
     * StoreEntityExtractor constructor.
     * @param AttributeSetRepositoryInterface $attributeSetRepository
     * @param Config $config
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        AttributeSetRepositoryInterface $attributeSetRepository,
        Config $config,
        CollectionFactory $collectionFactory
    ) {
        $this->storeCollectionFactory = $collectionFactory;
        $this->config = $config;
        $this->attributeSetRepository = $attributeSetRepository;
    }

    /**
     * @param null $storeId
     * @return \Magento\Eav\Api\Data\AttributeSetInterface|null
     */
    public function extractSet($storeId = null)
    {
        $entityId = $this->config->getMainEntityId($storeId);
        if ($entityId) {
            try {
                if (!isset($this->setById[$entityId])) {
                    $this->setById[$entityId] = $this->attributeSetRepository->get($entityId);
                }
                return $this->setById[$entityId];
            } catch (\Throwable $exception) {
                return null;
            }
        }

        return null;
    }

    /**
     * @param null $storeId
     * @return null|string
     */
    public function extractSetName($storeId = null)
    {
        $set = $this->extractSet($storeId);
        if ($set) {
            return $set->getAttributeSetName();
        }

        return Config::ATTRIBUTE_SET_NAME;
    }

    /**
     * @param null $storeId
     * @return mixed
     */
    public function extractSetId($storeId = null)
    {
        return $this->extractFromConfiguration($storeId);
    }

    /**
     * @param null $storeId
     * @return mixed
     */
    protected function extractFromConfiguration($storeId = null)
    {
        return $this->config->getMainEntityId($storeId);
    }
}
