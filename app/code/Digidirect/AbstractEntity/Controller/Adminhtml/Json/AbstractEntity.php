<?php

namespace Digidirect\AbstractEntity\Controller\Adminhtml\Json;

use Magento\Backend\App\Action;
use Digidirect\AbstractEntity\Model\ResourceModel\AbstractEntity as AbstractEntityResource;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Digidirect\AbstractEntity\Api\Data\AbstractEntityInterface;
use Digidirect\AbstractEntity\Model\AbstractEntity as AbstractEntityModel;
use Magento\Framework\Serialize\Serializer\Json as JsonHelper;

/**
 * Class AbstractEntity
 * @package Digidirect\AbstractEntity\Controller\Adminhtml\Json
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AbstractEntity extends \Magento\Backend\App\Action
{
    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var AttributeRepositoryInterface
     */
    protected $attributeRepository;

    /**
     * @var array
     */
    protected $backendTypes;

    /**
     * @var array
     */
    protected $options;

    /**
     * @var AbstractEntityResource
     */
    protected $abstractEntityResource;

    /**
     * @var JsonHelper
     */
    protected $jsonHelper;

    /**
     * AbstractEntity constructor.
     * @param Action\Context $context
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param AttributeRepositoryInterface $attributeRepository
     * @param AbstractEntityResource $abstractEntityResource
     * @param JsonHelper $jsonHelper
     * @param array $backendTypes
     * @codingStandardsIgnoreStart
     */
    public function __construct(
        Action\Context $context,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        AttributeRepositoryInterface $attributeRepository,
        AbstractEntityResource $abstractEntityResource,
        JsonHelper $jsonHelper,
        array $backendTypes = []
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->attributeRepository = $attributeRepository;
        $this->backendTypes = $backendTypes;
        $this->abstractEntityResource = $abstractEntityResource;
        $this->jsonHelper = $jsonHelper;
        parent::__construct($context);
    }
    // @codingStandardsIgnoreEnd

    /**
     * Return JSON-encoded array of Abstract Entity attributes
     *
     * @return string
     */
    public function execute()
    {
        $arrRes = [];

        $id = $this->getRequest()->getParam('parent');
        if (!empty($id)) {
            $this->searchCriteriaBuilder->addFilter(AbstractEntityInterface::ATTRIBUTE_SET_ID, $id, 'eq');
            if ($this->backendTypes) {
                $this->searchCriteriaBuilder->addFilter(AttributeInterface::BACKEND_TYPE, $this->backendTypes, 'in');
            }
            $searchCriteria = $this->searchCriteriaBuilder->create();
            $attributes = $this->attributeRepository->getList(AbstractEntityModel::ENTITY_TYPE, $searchCriteria);
            foreach ($attributes->getItems() as $item) {
                $arrRes[$item->getBackendType()][$item->getFrontendInput()][] = [
                    'value' => $item->getAttributeCode(),
                    'label' => $item->getFrontendLabel(),
                ];
            }
        }
        $this->getResponse()->representJson(
            $this->jsonHelper->serialize($arrRes)
        );
    }
}
