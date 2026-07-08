<?php

namespace Digidirect\Feed\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Framework\Registry;
use Digidirect\Feed\Model\RuleFactory;
use Digidirect\Feed\Model\RuleRepository;

abstract class Rule extends Action
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
     * @var RuleFactory
     */
    protected $ruleFactory;

    /**
     * @var RuleRepository
     */
    protected $ruleRepository;

    /**
     * Rule constructor.
     * @param Context $context
     * @param Registry $registry
     * @param ForwardFactory $resultForwardFactory
     * @param RuleFactory $ruleFactory
     * @param RuleRepository $ruleRepository
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ForwardFactory $resultForwardFactory,
        RuleFactory $ruleFactory,
        RuleRepository $ruleRepository
    ) {
        $this->context = $context;
        $this->registry = $registry;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->ruleFactory = $ruleFactory;
        $this->ruleRepository = $ruleRepository;

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
        $resultPage->getConfig()->getTitle()->prepend(__('Filters'));

        return $resultPage;
    }

    /**
     * Current template model
     *
     * @return \Digidirect\Feed\Model\Rule
     */
    public function initModel()
    {
        if ($id = $this->getRequest()->getParam('id')) {
            $model = $this->ruleRepository->getById($id);
        } else {
            $model = $this->ruleFactory->create();
        }

        if ($this->getRequest()->getParam('type')) {
            $model->setType($this->getRequest()->getParam('type'));
        }

        if ($this->getRequest()->getParam('feed')) {
            $model->setFeedIds([$this->getRequest()->getParam('feed')]);
        }

        $this->registry->register('current_model', $model);

        return $model;
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->context->getAuthorization()->isAllowed('Digidirect_Feed::feed');
    }
}
