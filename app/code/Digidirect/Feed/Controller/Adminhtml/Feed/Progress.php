<?php

namespace Digidirect\Feed\Controller\Adminhtml\Feed;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Digidirect\Feed\Controller\Adminhtml\Feed;
use Digidirect\Feed\Model\FeedFactory;
use Digidirect\Feed\Model\FeedRepository;
use Digidirect\Feed\Model\Feed\Exporter;

class Progress extends Feed
{
    /**
     * @var Exporter
     */
    protected $exporter;

    /**
     * Progress constructor.
     * @param Context $context
     * @param Registry $registry
     * @param FeedFactory $feedFactory
     * @param FeedRepository $feedRepository
     * @param Exporter $exporter
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FeedFactory $feedFactory,
        FeedRepository $feedRepository,
        Exporter $exporter
    ) {
        $this->exporter = $exporter;

        parent::__construct($context, $registry, $feedFactory, $feedRepository);
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $feed = $this->initModel();

        $progress = $this->exporter->getHandler($feed)->toJson();

        /** @var \Magento\Framework\App\Response\Http\Interceptor $response */
        $response = $this->getResponse();
        $response->representJson(\Zend_Json::encode($progress));
    }
}
