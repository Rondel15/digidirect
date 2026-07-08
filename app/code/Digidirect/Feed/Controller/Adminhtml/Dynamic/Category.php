<?php

namespace Digidirect\Feed\Controller\Adminhtml\Dynamic;

use Digidirect\Feed\Model\Dynamic\CategoryFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Backend\Model\View\Result\ForwardFactory;

/**
 * @SuppressWarnings(PHPMD)
 * @codingStandardsIgnoreFile
 */
abstract class Category extends \Magento\Backend\App\Action
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
     * @var \ForwardFactory
     */
    protected $resultForwardFactory;
    
    /**
     * @var CategoryFactory
     */
    protected $dynamicCategoryFactory;

    /**
     * Category constructor.
     * @param Context $context
     * @param Registry $registry
     * @param ForwardFactory $resultForwardFactory
     * @param CategoryFactory $categoryFactory
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ForwardFactory $resultForwardFactory,
        CategoryFactory $categoryFactory
    ) {
        $this->context = $context;
        $this->registry = $registry;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->dynamicCategoryFactory = $categoryFactory;       

        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function _initPage($resultPage)
    {
        $resultPage->setActiveMenu('Digidirect_Utilities::Digidirect');

        $resultPage->getConfig()->getTitle()->prepend(__('Shopping Feeds'));
        $resultPage->getConfig()->getTitle()->prepend(__('Category Mapping'));

        return $resultPage;
    }

    /**
     * {@inheritdoc}
     * @return |Digidirect\Feed\Model\Dynamic\Category
     */
    public function initModel()
    {
        $model = $this->dynamicCategoryFactory->create();

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
        return $this->context->getAuthorization()->isAllowed('Digidirect_Feed::feed_dynamic_category');
    }
}
