<?php
namespace Digidirect\CollectStoreLocator\Controller\Ajax\Locator;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Block
 * @package Digidirect\CollectStoreLocator\Controller\Ajax\Locator
 */
class Block extends Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * Block constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        JsonFactory $resultJsonFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        $resultPage = $this->resultPageFactory->create();

        $layout = $resultPage->addHandle('digidirect_collect_locator_block')->getLayout();
        $block = $layout->getBlock('storelocator.wrapper')->toHtml();

        $result->setData(['output' => $block]);
        return $result;
    }
}
