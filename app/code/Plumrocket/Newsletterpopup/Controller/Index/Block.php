<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2020 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Controller\Index;

use Magento\Framework\App\Action\Action;
use Plumrocket\Newsletterpopup\Block\Template;

class Block extends Action
{
    public function execute()
    {
        // set "currentProductId" as argument instead of setting by setter because "currentProductId" used in _construct
        $blockArguments = [
            'data' => [
                'currentProductId' => (int) $this->getRequest()->getParam('productId'),
            ]
        ];

        /** @var \Plumrocket\Newsletterpopup\Block\Template $block */
        $block = $this->_view->getLayout()->createBlock(Template::class, '', $blockArguments);

        $this->getResponse()
            ->clearHeader('Location')
            // ->clearRawHeader('Location')
            ->setHttpResponseCode(200)
            ->setBody($block->toHtml());
    }
}
