<?php
namespace Digidirect\AbstractEntity\Controller\AbstractEntity;

use Digidirect\AbstractEntity\Api\AbstractEntityRepositoryInterface;
use Digidirect\AbstractEntity\Model\AbstractEntityRepository;
use Digidirect\AbstractEntity\Model\Registry\Constants;
use Magento\Framework\App\Action\Action;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Eav\Api\AttributeSetRepositoryInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\Page;

class View extends Action
{
    const ADDITIONAL_SET_HANDLE = 'digidirect_abstractentity_view_abstractentity_';
    const ADDITIONAL_HANDLE = 'digidirect_abstractentity_view_';

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
     * @var AbstractEntityRepository
     */
    protected $abstractEntityRepository;

    /**
     * @var AttributeSetRepositoryInterface
     */
    protected $attributeSetRepository;

    /**
     * Index constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Controller\Result\ForwardFactory $resultForwardFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param AbstractEntityRepositoryInterface $abstractEntityRepository
     * @param AttributeSetRepositoryInterface $attributeSetRepository
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        ForwardFactory $resultForwardFactory,
        Registry $coreRegistry,
        StoreManagerInterface $storeManager,
        AbstractEntityRepositoryInterface $abstractEntityRepository,
        AttributeSetRepositoryInterface $attributeSetRepository
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->coreRegistry = $coreRegistry;
        $this->storeManager = $storeManager;
        $this->abstractEntityRepository = $abstractEntityRepository;
        $this->attributeSetRepository = $attributeSetRepository;
        parent::__construct($context);
    }

    /**
     * @return bool|\Digidirect\AbstractEntity\Api\Data\AbstractEntityInterface
     */
    protected function initAbstractEntity()
    {
        $aeId = (int)$this->getRequest()->getParam('id');
        try {
            $ae = $this->abstractEntityRepository->getById(
                $aeId,
                $this->storeManager->getStore()->getId()
            );
        } catch (NoSuchEntityException $e) {
            return false;
        }
        $this->coreRegistry->register(Constants::CURRENT_ABSTRACT_ENTITY, $ae);
        return $ae;
    }

    /**
     * View abstract entity page action
     * @return ResponseInterface|ResultInterface|Page
     * @throws NoSuchEntityException
     */
    public function execute()
    {
        $ae = $this->initAbstractEntity();
        if ($ae && $ae->getStatus() && $ae->isVisibleOnFrontend()) {
            $resultPage = $this->resultPageFactory->create();
            $attributeSet = $this->attributeSetRepository->get($ae->getAttributeSetId());
            $attributeSetName = preg_replace('/[^a-z_0-9]/', '_', strtolower($attributeSet->getAttributeSetName()));
            $resultPage->addHandle(static::ADDITIONAL_SET_HANDLE . $attributeSetName);
            $resultPage->addHandle(static::ADDITIONAL_HANDLE . $ae->getId());
            $resultPage->getConfig()->getTitle()->set($ae->getName());
            return $resultPage;
        } else {
            return $this->resultForwardFactory->create()->forward('noroute');
        }
    }
}
