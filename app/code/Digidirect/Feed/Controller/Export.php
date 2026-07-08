<?php

namespace Digidirect\Feed\Controller;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Digidirect\Feed\Model\FeedRepository;
use Digidirect\Feed\Model\Feed\Exporter;

abstract class Export extends Action
{
    /**
     * @var FeedRepository
     */
    protected $feedRepository;

    /**
     * @var Exporter
     */
    protected $exporter;

    /**
     * Export constructor.
     * @param Context $context
     * @param FeedRepository $feedRepository
     * @param Exporter $exporter
     */
    public function __construct(
        Context $context,
        FeedRepository $feedRepository,
        Exporter $exporter
    ) {
        $this->feedRepository = $feedRepository;
        $this->exporter = $exporter;

        parent::__construct($context);
    }

    /**
     * Current feed model
     *
     * @return false|\Digidirect\Feed\Model\Feed
     */
    protected function getFeed()
    {
        if ($id = $this->getRequest()->getParam('id')) {
            return $this->feedRepository->getById($id);
        }

        return false;
    }
}
