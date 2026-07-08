<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;

class Preview extends Action
{
    /**
     * @var \Plumrocket\Newsletterpopup\Model\Preview
     */
    private $preview;

    /**
     * @param \Magento\Framework\App\Action\Context     $context
     * @param \Plumrocket\Newsletterpopup\Model\Preview $preview
     */
    public function __construct(
        Context $context,
        \Plumrocket\Newsletterpopup\Model\Preview $preview
    ) {
        parent::__construct($context);
        $this->preview = $preview;
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $this->preview->setIsPreviewMode(true);

        /** @var \Magento\Framework\View\Result\Page $resultPage */
        return $this->resultFactory->create(ResultFactory::TYPE_PAGE);
    }
}
