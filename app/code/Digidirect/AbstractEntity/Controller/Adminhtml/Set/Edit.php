<?php
namespace Digidirect\AbstractEntity\Controller\Adminhtml\Set;

use Digidirect\AbstractEntity\Controller\Adminhtml\Set;
use Digidirect\AbstractEntity\Model\ResourceModel\AbstractEntity as AbstractEntityResource;
use Magento\Backend\App\Action\Context;
use Magento\Eav\Api\AttributeSetRepositoryInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;

class Edit extends Set
{
    const CURRENT_ATTRIBUTE_SET = 'current_attribute_set';
    const BACK_ACTION = 'digidirect_abstractentity/*/index';

    /**
     * @var AttributeSetRepositoryInterface
     */
    protected $attributeSetRepository;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param AbstractEntityResource $abstractEntityResource
     * @param AttributeSetRepositoryInterface $attributeSetRepository
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        AbstractEntityResource $abstractEntityResource,
        AttributeSetRepositoryInterface $attributeSetRepository,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context, $coreRegistry, $abstractEntityResource);
        $this->attributeSetRepository = $attributeSetRepository;
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $this->_setTypeId();
        $attributeSet = $this->attributeSetRepository->get($this->getRequest()->getParam('id'));
        if (!$attributeSet->getId()) {
            return $this->resultRedirectFactory->create()->setPath(static::BACK_ACTION);
        }

        $this->_coreRegistry->register(static::CURRENT_ATTRIBUTE_SET, $attributeSet);

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Digidirect_AbstractEntity::abstractentity_view');
        $resultPage->getConfig()->getTitle()->prepend(__('Abstract Entities'));
        $resultPage->getConfig()->getTitle()->prepend(
            $attributeSet->getId() ? $attributeSet->getAttributeSetName() : __('New Abstract Entity')
        );
        $resultPage->addBreadcrumb(__('Digidirect'), __('Abstract Entities'));
        $resultPage->addBreadcrumb(__('Manage Abstract Entities'), __('Manage Abstract Entities'));
        return $resultPage;
    }
}
