<?php

namespace Digidirect\Feed\Controller\Adminhtml\Feed;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Digidirect\Feed\Controller\Adminhtml\Feed;
use Digidirect\Feed\Model\FeedFactory;
use Digidirect\Feed\Model\FeedRepository;

class Library extends Feed
{
    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $layout;

    /**
     * Library constructor.
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
        $this->layout = $context->getView()->getLayout();

        parent::__construct($context, $registry, $feedFactory, $feedRepository);
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        if ($this->getRequest()->getParam('pattern')) {
            $content = $this->layout->createBlock('Digidirect\Feed\Block\Adminhtml\Feed\Library')
                ->setPattern($this->getRequest()->getParam('pattern'))
                ->setTemplate('Digidirect_Feed::feed/library/preview.phtml')
                ->toHtml();
        } else {
            $content = $this->layout->createBlock('Digidirect\Feed\Block\Adminhtml\Feed\Library')
                ->setTemplate('Digidirect_Feed::feed/library.phtml')
                ->toHtml();
        }

        /** @var \Magento\Framework\App\Response\Http\Interceptor $response */
        $response = $this->getResponse();

        return $response
            ->setBody($content);
    }

    public function _processUrlKeys()
    {
        return true;
    }
}
