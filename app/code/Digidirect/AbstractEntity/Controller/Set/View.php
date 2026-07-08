<?php
namespace Digidirect\AbstractEntity\Controller\Set;

use Digidirect\AbstractEntity\Model\Registry\Constants;
use Magento\Framework\App\Action\Action;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Eav\Api\AttributeSetRepositoryInterface;
use Digidirect\AbstractEntity\Model\ResourceModel\AdditionalAttributes;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\Page;

/**
 * Class View
 * @package Digidirect\AbstractEntity\Controller\Set
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class View extends Action
{
    const ADDITIONAL_SET_HANDLE = 'digidirect_abstractentity_view_set_';
    const ADDITIONAL_HANDLE = 'digidirect_attributeset_view_';

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Framework\Controller\Result\ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var AttributeSetRepositoryInterface
     */
    protected $attributeSetRepository;

    /**
     * @var AdditionalAttributes
     */
    protected $additionalAttributes;

    /**
     * Index constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Controller\Result\ForwardFactory $resultForwardFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param AttributeSetRepositoryInterface $attributeSetRepository
     * @param AdditionalAttributes $additionalAttributes
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        ForwardFactory $resultForwardFactory,
        Registry $coreRegistry,
        StoreManagerInterface $storeManager,
        AttributeSetRepositoryInterface $attributeSetRepository,
        AdditionalAttributes $additionalAttributes
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->coreRegistry = $coreRegistry;
        $this->storeManager = $storeManager;
        $this->attributeSetRepository = $attributeSetRepository;
        $this->additionalAttributes = $additionalAttributes;
        parent::__construct($context);
    }

    /**
     * @return bool|\Magento\Eav\Api\Data\AttributeSetInterface
     */
    protected function initAttributeSet()
    {
        $setId = (int)$this->getRequest()->getParam('id');
        try {
            $set = $this->attributeSetRepository->get($setId);
        } catch (NoSuchEntityException $e) {
            return false;
        }
        $this->coreRegistry->register(Constants::CURRENT_ATTRIBUTE_SET, $set);
        return $set;
    }

    /**
     * View abstract entity page action
     *
     * @return $this|ResponseInterface|ResultInterface|Page
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        $attributeSet = $this->initAttributeSet();
        $isVisible = $this->_isSetVisibleOnFrontend($attributeSet);
        if ($attributeSet && $isVisible) {
            $resultPage = $this->resultPageFactory->create();
            $attributeSetName = preg_replace('/[^a-z_0-9]/', '_', strtolower($attributeSet->getAttributeSetName()));
            $resultPage->addHandle(static::ADDITIONAL_SET_HANDLE . $attributeSetName);
            $resultPage->addHandle(static::ADDITIONAL_HANDLE . $attributeSet->getId());
            $resultPage->getConfig()->getTitle()->set(__($attributeSet->getAttributeSetName()));
            return $resultPage;
        } else {
            return $this->resultForwardFactory->create()->forward('noroute');
        }
    }

    /**
     * @param \Magento\Eav\Api\Data\AttributeSetInterface $set
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _isSetVisibleOnFrontend($set)
    {
        return (int) $this->additionalAttributes->getAdditionalAttributeById(
            $set->getId(),
            AdditionalAttributes::VISIBLE_ON_FRONTEND
        );
    }
}
