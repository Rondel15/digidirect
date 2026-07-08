<?php

namespace Digidirect\Feed\Controller\Adminhtml\Feed;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Digidirect\Feed\Controller\Adminhtml\Feed;
use Digidirect\Feed\Model\FeedFactory;
use Digidirect\Feed\Model\FeedRepository;
use Digidirect\Feed\Model\Feed\Deliverer;

class ValidateFtp extends Feed
{
    /**
     * @var Deliverer
     */
    protected $deliverer;

    /**
     * ValidateFtp constructor.
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

            $params = $this->getRequest()->getParam('feed');
            if (is_array($params)) {
                $feed->addData($params);
            }
            $this->deliverer->validate($feed);
            $message = __('A connection was successfully established with "%1"', $feed->getFtpHost());
            $status = Deliverer::STATUS_SUCCESS;
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $message = $e->getMessage();
            $status = Deliverer::STATUS_ERROR;
        } catch (\Exception $e) {
            $message = __('Something went wrong while trying to validate ftp.');
            $status = Deliverer::STATUS_ERROR;
        }

        /** @var \Magento\Framework\App\Response\Http\Interceptor $response */
        $response = $this->getResponse();
        $response->representJson(\Zend_Json::encode([
            'status' => $status,
            'message' => $message
        ]));
    }
}
