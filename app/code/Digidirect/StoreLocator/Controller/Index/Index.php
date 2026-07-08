<?php

namespace Digidirect\StoreLocator\Controller\Index;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\Registry;
use Magento\Eav\Api\AttributeSetRepositoryInterface;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory;
use Digidirect\StoreLocator\Helper\Config as ConfigHelper;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Index extends AbstractStoreLocator
{
    const ADDITIONAL_SET_HANDLE = 'digidirect_storelocator_index';

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Framework\Controller\Result\ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * Index constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param ForwardFactory $resultForwardFactory
     * @param Registry $coreRegistry
     * @param AttributeSetRepositoryInterface $attributeSetRepository
     * @param CollectionFactory $attributeSetCollection
     * @param ConfigHelper $configHelper
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        ForwardFactory $resultForwardFactory,
        Registry $coreRegistry,
        AttributeSetRepositoryInterface $attributeSetRepository,
        CollectionFactory $attributeSetCollection,
        ConfigHelper $configHelper
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->resultForwardFactory = $resultForwardFactory;
        parent::__construct($context, $coreRegistry, $attributeSetRepository, $attributeSetCollection, $configHelper);
    }


    /**
     * View abstract entity page action
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $status = $this->configHelper->isEnable();
        if (!$status) {
            $resultForward = $this->resultForwardFactory->create();
            $resultForward->forward('noroute');
            return $resultForward;
        }

        $attributeSet = $this->initAttributeSet();
        if ($attributeSet) {
            $resultPage = $this->resultPageFactory->create();
            $resultPage->addHandle(static::ADDITIONAL_SET_HANDLE);
            $resultPage->getConfig()->getTitle()->set(__($attributeSet->getAttributeSetName() . ' Locator'));
            return $resultPage;
        } else {
            return $this->resultForwardFactory->create()->forward('noroute');
        }
    }
}
