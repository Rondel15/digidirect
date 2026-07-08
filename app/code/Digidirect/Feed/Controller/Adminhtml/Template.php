<?php

namespace Digidirect\Feed\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Framework\Registry;
use Digidirect\Feed\Model\TemplateFactory;
use Digidirect\Feed\Model\TemplateRepository;

abstract class Template extends Action
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
     * @var TemplateFactory
     */
    protected $templateFactory;

    /**
     * @var templateRepository
     */
    protected $templateRepository;

    /**
     * Template constructor.
     * @param Context $context
     * @param Registry $registry
     * @param ForwardFactory $resultForwardFactory
     * @param TemplateFactory $templateFactory
     * @param TemplateRepository $templateRepository
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ForwardFactory $resultForwardFactory,
        TemplateFactory $templateFactory,
        TemplateRepository $templateRepository
    ) {
        $this->context = $context;
        $this->registry = $registry;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->templateFactory = $templateFactory;
        $this->templateRepository = $templateRepository;

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
        $resultPage->getConfig()->getTitle()->prepend(__('Templates'));

        return $resultPage;
    }

    /**
     * Current template model
     *
     * @return \Digidirect\Feed\Model\Template
     */
    public function initModel()
    {
        if ($id = $this->getRequest()->getParam('id')) {
            $model = $this->templateRepository->getById($id);
        } else {
            $model = $this->templateFactory->create();
        }

        $this->registry->register('current_model', $model);

        return $model;
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->context->getAuthorization()->isAllowed('Digidirect_Feed::feed_template');
    }
}
