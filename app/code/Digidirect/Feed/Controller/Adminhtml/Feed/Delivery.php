<?php

namespace Digidirect\Feed\Controller\Adminhtml\Feed;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Digidirect\Feed\Controller\Adminhtml\Feed;
use Digidirect\Feed\Model\FeedFactory;
use Digidirect\Feed\Model\FeedRepository;
use Digidirect\Feed\Model\Feed\Deliverer;

class Delivery extends Feed
{
    /**
     * @var Deliverer
     */
    protected $deliverer;

    /**
     * Delivery constructor.
     * @param Context $context
     * @param Registry $registry
     * @param FeedFactory $feedFactory
     * @param FeedRepository $feedRepository
     * @param Deliverer $deliverer
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FeedFactory $feedFactory,
        FeedRepository $feedRepository,
        Deliverer $deliverer
    ) {
        $this->deliverer = $deliverer;

        parent::__construct($context, $registry, $feedFactory, $feedRepository);
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        try {
            $feed = $this->initModel();
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return $this->resultRedirectFactory->create()->setPath('*/*');
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Something went wrong while trying to get feed.'));
            return $this->resultRedirectFactory->create()->setPath('*/*');
        }

        try {
            $this->deliverer->delivery($feed);
            $this->messageManager->addSuccessMessage(
                __('Feed was successfully delivered to "%1"', $feed->getFtpHost())
            );
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Something went wrong while trying to delivery feed.'));
        }

        return $this->resultRedirectFactory->create()->setPath('*/*/edit', ['id' => $feed->getId()]);
    }
}
