<?php

namespace Digidirect\Feed\Controller\Adminhtml\Dynamic;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Digidirect\Feed\Model\Dynamic\AttributeFactory;
use Magento\Backend\Model\View\Result\ForwardFactory;

abstract class Attribute extends Action
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * @var AttributeFactory
     */
    protected $attributeFactory;

    /**
     * Attribute constructor.
     * @param Context $context
     * @param Registry $registry
     * @param ForwardFactory $resultForwardFactory
     * @param AttributeFactory $attributeFactory
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ForwardFactory $resultForwardFactory,
        AttributeFactory $attributeFactory
    ) {
        $this->context = $context;
        $this->registry = $registry;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->attributeFactory = $attributeFactory;

        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     * @param \Magento\Backend\Model\View\Result\Page\Interceptor $resultPage
     * @return \Magento\Backend\Model\View\Result\Page\Interceptor
     */
    protected function initPage($resultPage)
    {
        $resultPage->setActiveMenu('Digidirect_Utilities::Digidirect');
        $resultPage->getConfig()->getTitle()->prepend(__('Shopping Feeds'));
        $resultPage->getConfig()->getTitle()->prepend(__('Dynamic Attributes'));

        return $resultPage;
    }

    /**
     * Current feed model
     * @return \Digidirect\Feed\Model\Feed
     */
    protected function initModel()
    {
        $model = $this->attributeFactory->create();

        if ($this->getRequest()->getParam('id')) {
            $model->getResource()->load($model, $this->getRequest()->getParam('id'));
        }

        $this->registry->register('current_model', $model);

        return $model;
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->context->getAuthorization()->isAllowed('Digidirect_Feed::feed_dynamic_attribute');
    }
}
