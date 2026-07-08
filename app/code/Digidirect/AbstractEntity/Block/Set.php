<?php

namespace Digidirect\AbstractEntity\Block;

use Digidirect\AbstractEntity\Helper\Image;
use Digidirect\AbstractEntity\Model\Registry\Constants;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\Element\Template;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Digidirect\AbstractEntity\Api\AbstractEntityRepositoryInterface;
use Digidirect\AbstractEntity\Api\Data\AbstractEntityInterface;
use Digidirect\AbstractEntity\Model\AbstractEntity\Attribute\Source\Status;
use Digidirect\AbstractEntity\Model\AbstractEntity\Attribute\Source\VisibleOnFrontend;
use Digidirect\AbstractEntity\Model\ResourceModel\AdditionalAttributes;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Set extends Template
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;

    /**
     * @var Image
     */
    protected $imageHelper;

    /**
     * @var AbstractEntityRepositoryInterface
     */
    protected $abstractEntityRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var AdditionalAttributes
     */
    protected $additionalAttributes;

    /**
     * @var AbstractEntityInterface[]
     */
    protected $collection;

    /**
     * Set constructor.
     *
     * @param Registry $coreRegistry
     * @param Image $imageHelper
     * @param Context $context
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param AbstractEntityRepositoryInterface $abstractEntityRepository
     * @param AdditionalAttributes $additionalAttributes
     * @param array $data
     */
    public function __construct(
        Registry $coreRegistry,
        Image $imageHelper,
        Context $context,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        AbstractEntityRepositoryInterface $abstractEntityRepository,
        AdditionalAttributes $additionalAttributes,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->coreRegistry = $coreRegistry;
        $this->imageHelper = $imageHelper;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->abstractEntityRepository = $abstractEntityRepository;
        $this->additionalAttributes = $additionalAttributes;
    }

    /**
     * @return \Magento\Eav\Model\Entity\Attribute\Set
     */
    public function getCurrentAttributeSet()
    {
        return $this->coreRegistry->registry(Constants::CURRENT_ATTRIBUTE_SET);
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getDescription()
    {
        return $this->additionalAttributes->getAdditionalAttributeById(
            $this->getCurrentAttributeSet()->getId(),
            AdditionalAttributes::DESCRIPTION
        );
    }

    /**
     * @return AbstractEntityInterface[]
     */
    protected function _getCollection()
    {
        $defaultAttributes = [
            AbstractEntityInterface::NAME,
            AbstractEntityInterface::URL_KEY,
            AbstractEntityInterface::STATUS,
            AbstractEntityInterface::VISIBLE_ON_FRONTEND,
        ];
        $attributes = $this->getData('attributes');
        if (is_array($attributes)) {
            $defaultAttributes = array_merge($defaultAttributes, $attributes);
        }

        $collection = $this->abstractEntityRepository->getCollection(
            $this->getCurrentAttributeSet()->getAttributeSetName()
        );
        if ($attributes !== null) {
            $collection->addAttributeToSelect($defaultAttributes, true);
        }
        $collection->addFieldToFilter(AbstractEntityInterface::STATUS, ['eq' => Status::STATUS_ENABLED]);
        $collection->addFieldToFilter(
            AbstractEntityInterface::VISIBLE_ON_FRONTEND,
            ['eq' => VisibleOnFrontend::VISIBLE_ON_FRONTEND_ENABLED]
        );
        $collection->addFieldToFilter(
            AbstractEntityInterface::ATTRIBUTE_SET_ID,
            ['eq' => $this->getCurrentAttributeSet()->getAttributeSetId()]
        );

        return $collection;
    }

    /**
     * @return AbstractEntityInterface[]
     */
    public function getCollection()
    {
        if (!$this->collection) {
            $this->collection = $this->_getCollection();
        }

        return $this->collection;
    }

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        /** @var \Magento\Theme\Block\Html\Pager $toolbar */
        $toolbar = $this->getLayout()->getBlock('set_list_toolbar');
        $limit = $this->getEntitiesPerPage();
        $toolbar->setLimit($limit);
        $toolbar->setAvailableLimit([]);
        if ($toolbar) {
            $toolbar->setCollection($this->getCollection());
            $this->setChild('toolbar', $toolbar);
        }
        return $this;
    }

    /**
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getEntitiesPerPage()
    {
        $limit = (int)$this->additionalAttributes->getAdditionalAttributeById(
            $this->getCurrentAttributeSet()->getId(),
            AdditionalAttributes::ENTITIES_PER_LISTING_PAGE
        );

        if (!$limit) {
            $limit = AdditionalAttributes::DEFAULT_ENTITIES_PER_LISTING_PAGE;
        }

        return $limit;
    }
}
