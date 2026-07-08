<?php

namespace Digidirect\StoreLocator\Controller\Index;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\Registry;
use Magento\Eav\Api\AttributeSetRepositoryInterface;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory;
use Digidirect\Locator\Model\Locator;
use Magento\Framework\View\LayoutInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Digidirect\StoreLocator\Helper\Config as ConfigHelper;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\Page;

/**
 * Class Search
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Search extends AbstractStoreLocator
{
    const ADDITIONAL_SET_HANDLE = 'digidirect_storelocator_search';

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Framework\Controller\Result\ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * @var Locator
     */
    protected $locator;

    /**
     * @var LayoutInterface
     */
    protected $layout;

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * Search constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param ForwardFactory $resultForwardFactory
     * @param Registry $coreRegistry
     * @param AttributeSetRepositoryInterface $attributeSetRepository
     * @param CollectionFactory $attributeSetCollection
     * @param ConfigHelper $configHelper
     * @param Locator $locator
     * @param LayoutInterface $layout
     * @param JsonFactory $resultJsonFactory
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        ForwardFactory $resultForwardFactory,
        Registry $coreRegistry,
        AttributeSetRepositoryInterface $attributeSetRepository,
        CollectionFactory $attributeSetCollection,
        ConfigHelper $configHelper,
        Locator $locator,
        LayoutInterface $layout,
        JsonFactory $resultJsonFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->locator = $locator;
        $this->layout = $layout;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context, $coreRegistry, $attributeSetRepository, $attributeSetCollection, $configHelper);
    }

    /**
     * @return $this| ResponseInterface| ResultInterface| Page
     * @throws \Exception
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
            if ($this->getRequest()->getParam('isAjax')) {
                $resultJson = $this->resultJsonFactory->create();
                $resultLayout = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
                $block = $resultLayout->getLayout()->getBlock($this->getRequest()->getParam('blockName'));
                if (!$block) {
                    $resultJson->setData(['error' => 'Block not found']);
                }
                $entities = $block->getData('entities');
                $result = $this->locator->getEntities(
                    $entities,
                    $this->getRequest()->getParams()
                );

                return $resultJson->setData(['result' => $result]);
            }
            $resultPage = $this->resultPageFactory->create();
            $resultPage->addHandle(static::ADDITIONAL_SET_HANDLE);
            $resultPage->getConfig()->getTitle()->set($attributeSet->getAttributeSetName() . ' Locator');
            return $resultPage;
        } else {
            return $this->resultForwardFactory->create()->forward('noroute');
        }
    }
}
