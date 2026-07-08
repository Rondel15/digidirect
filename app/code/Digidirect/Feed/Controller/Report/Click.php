<?php

namespace Digidirect\Feed\Controller\Report;

use Digidirect\Feed\Controller\Report;
use Magento\Framework\App\Action\Context;
use Digidirect\Feed\Model\ReportFactory;

class Click extends Report
{
    /**
     * @var ReportFactory
     */
    protected $reportFactory;

    /**
     * @param ReportFactory $reportFactory
     * @param Context $context
     */
    public function __construct(
        Context $context,
        ReportFactory $reportFactory
    ) {
        $this->reportFactory = $reportFactory;

        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $feed = $this->getRequest()->getParam('feed');
        $product = $this->getRequest()->getParam('product');
        $session = $this->getRequest()->getParam('session');

        $this->reportFactory->create()
            ->addClick($session, $feed, $product);
    }
}
