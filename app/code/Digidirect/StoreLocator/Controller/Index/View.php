<?php

namespace Digidirect\StoreLocator\Controller\Index;

use Digidirect\AbstractEntity\Api\AbstractEntityRepositoryInterface;
use Digidirect\AbstractEntity\Api\Data\AbstractEntityInterface;
use Digidirect\StoreLocator\Helper\Config;
use Magento\Eav\Api\AttributeSetRepositoryInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\Page;
use Digidirect\AbstractEntity\Controller\AbstractEntity\View as ViewAction;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\StoreManagerInterface;

class View extends ViewAction
{
    /**
     * @var Config
     */
    protected $configHelper;

    /**
     * @var null|AbstractEntityInterface
     */
    protected $currentAe;

    /**
     * View constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param ForwardFactory $resultForwardFactory
     * @param Registry $coreRegistry
     * @param StoreManagerInterface $storeManager
     * @param AbstractEntityRepositoryInterface $abstractEntityRepository
     * @param AttributeSetRepositoryInterface $attributeSetRepository
     * @param Config $config
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        ForwardFactory $resultForwardFactory,
        Registry $coreRegistry,
        StoreManagerInterface $storeManager,
        AbstractEntityRepositoryInterface $abstractEntityRepository,
        AttributeSetRepositoryInterface $attributeSetRepository,
        Config $config
    ) {
        $this->configHelper = $config;
        parent::__construct(
            $context,
            $resultPageFactory,
            $resultForwardFactory,
            $coreRegistry,
            $storeManager,
            $abstractEntityRepository,
            $attributeSetRepository
        );
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|Page
     */
    public function execute()
    {
        $ae = $this->initAbstractEntity();
        if (!$ae) {
            return parent::execute();
        }

        $currentAttributeSetId = $ae->getAttributeSetId();
        $layout = $this->configHelper->getLayoutMapping($currentAttributeSetId);
        if ($layout) {
            $attributeSet = $this->attributeSetRepository->get($ae->getAttributeSetId());
            $attributeSetName = preg_replace(
                '/[^a-z_0-9]/',
                '_',
                strtolower($attributeSet->getAttributeSetName())
            );
            $resultPage = $this->resultPageFactory->create();
            $resultPage->addHandle(static::ADDITIONAL_SET_HANDLE . $attributeSetName);
            $resultPage->addHandle(static::ADDITIONAL_HANDLE . $ae->getId());
            $resultPage->addHandle($layout);
            $resultPage->getConfig()->getTitle()->set($ae->getName());
            return $resultPage;
        }

        return parent::execute();
    }

    /**
     * @return bool|AbstractEntityInterface
     */
    protected function initAbstractEntity()
    {
        if (!($this->currentAe instanceof AbstractEntityInterface)) {
            $this->currentAe = parent::initAbstractEntity();
        }
        return $this->currentAe;
    }
}
