<?php

namespace Digidirect\Feed\Controller\Adminhtml\Feed;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Digidirect\Feed\Controller\Adminhtml\Feed;
use Digidirect\Feed\Model\FeedFactory;
use Digidirect\Feed\Model\FeedRepository;
use Digidirect\Feed\Model\Feed\Copier;

class Duplicate extends Feed
{
    /**
     * @var Copier
     */
    protected $copier;

    /**
     * Duplicate constructor.
     * @param Context $context
     * @param Registry $registry
     * @param FeedFactory $feedFactory
     * @param FeedRepository $feedRepository
     * @param Copier $copier
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FeedFactory $feedFactory,
        FeedRepository $feedRepository,
        Copier $copier
    ) {
        $this->copier = $copier;

        parent::__construct($context, $registry, $feedFactory, $feedRepository);
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        try {
            $feed = $this->initModel();
            $this->copier->copy($feed);
            $this->messageManager->addSuccessMessage(__('Feed was successfully duplicated.'));
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Something went wrong while trying to duplicate the feed.'));
        }

        return $resultRedirect->setPath('*/*/');
    }
}
