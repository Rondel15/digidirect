<?php

namespace Digidirect\Feed\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Digidirect\Feed\Model\FeedFactory;
use Digidirect\Feed\Model\FeedRepository;

abstract class Feed extends Action
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
     * @var FeedFactory
     */
    protected $feedFactory;

    /**
     * @var feedRepository
     */
    protected $feedRepository;

    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $backendSession;

    /**
     * Feed constructor.
     * @param Context $context
     * @param Registry $registry
     * @param FeedFactory $feedFactory
     * @param FeedRepository $feedRepository
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FeedFactory $feedFactory,
        FeedRepository $feedRepository
    ) {
        $this->context = $context;
        $this->registry = $registry;
        $this->feedFactory = $feedFactory;
        $this->feedRepository = $feedRepository;
        $this->backendSession = $context->getSession();

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
        $resultPage->getConfig()->getTitle()->prepend(__('Feeds'));

        return $resultPage;
    }

    /**
     * Current feed model
     * @return \Digidirect\Feed\Model\Feed
     */
    protected function initModel()
    {
        if ($id = $this->getRequest()->getParam('id')) {
            $model = $this->feedRepository->getById($id);
        } else {
            $model = $this->feedFactory->create();
        }

        $this->registry->register('current_model', $model);

        return $model;
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->context->getAuthorization()->isAllowed('Digidirect_Feed::feed_feed');
    }
}
