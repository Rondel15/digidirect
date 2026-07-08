<?php

namespace Digidirect\CollectAbstractEntityMsi\Controller\Availability;

use Digidirect\Faq\Api\CategoryRepositoryInterface;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class Get
 * @package Digidirect\CollectAbstractEntityMsi\Controller\Availability
 */
class Get extends \Magento\Framework\App\Action\Action
{
    /**
     * Constants
     */
    const ACTION = 'collectplace_availability_get';

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;


    /**
     * Get constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $this->_view->loadLayout(self::ACTION);
        $layout = $this->_view->getLayout();
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData(
            ['content' => (string) $layout->renderNonCachedElement('product.store.availability')]
        );
    }
}
