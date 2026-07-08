<?php

namespace Digidirect\StoreLocator\Controller\Index;

use Digidirect\AbstractEntity\Model\Registry\Constants;
use Digidirect\StoreLocator\Helper\Config;
use Magento\Framework\App\Action\Action;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Eav\Api\AttributeSetRepositoryInterface;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory;
use Digidirect\StoreLocator\Helper\Config as ConfigHelper;
use Magento\Eav\Model\Entity\Attribute\Set;

/**
 * @since 1.6.0
 * Needed as controllers have common logic.
 */
abstract class AbstractStoreLocator extends Action
{
    const ADDITIONAL_SET_HANDLE = 'digidirect_storelocator_index';
    const DEFAULT_BASE_ENTITY = 'store';

    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var AttributeSetRepositoryInterface
     */
    protected $attributeSetRepository;

    /**
     * @var CollectionFactory
     */
    protected $attributeSetCollection;

    /**
     * @var ConfigHelper
     */
    protected $configHelper;

    /**
     * AbstractStoreLocator constructor.
     * @param Context $context
     * @param Registry $coreRegistry
     * @param AttributeSetRepositoryInterface $attributeSetRepository
     * @param CollectionFactory $attributeSetCollection
     * @param ConfigHelper $configHelper
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        AttributeSetRepositoryInterface $attributeSetRepository,
        CollectionFactory $attributeSetCollection,
        ConfigHelper $configHelper
    ) {
        $this->coreRegistry = $coreRegistry;
        $this->attributeSetRepository = $attributeSetRepository;
        $this->attributeSetCollection = $attributeSetCollection;
        $this->configHelper = $configHelper;
        parent::__construct($context);
    }

    /**
     * @return bool|\Magento\Eav\Api\Data\AttributeSetInterface
     */
    protected function initAttributeSet()
    {
        $setId = (int)$this->getAttrSetIdByName();
        try {
            $set = $this->attributeSetRepository->get($setId);
        } catch (NoSuchEntityException $e) {
            return false;
        }
        $this->coreRegistry->register(Constants::CURRENT_ATTRIBUTE_SET, $set);
        return $set;
    }


    /**
     * @return int|mixed|null
     */
    protected function getAttrSetIdByName()
    {
        if ($attributeSetId = $this->configHelper->getMainEntityId()) {
            return $attributeSetId;
        }
        /**
         * @var $attributeSet Set
         */
        $attributeSet = $this->attributeSetCollection->create()
            ->addFieldToFilter(
                'attribute_set_name',
                Config::ATTRIBUTE_SET_NAME
            )
            ->getFirstItem();

        return $attributeSet->getAttributeSetId();
    }
}
