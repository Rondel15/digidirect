<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Controller\Index;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Stdlib\StringUtils;
use Plumrocket\Newsletterpopup\Model\SubscriberEncoded;

class History extends Action
{

    /**
     * @var \Plumrocket\Newsletterpopup\Model\SubscriberEncoded
     */
    private $subscriberEncoded;

    /**
     * @param \Magento\Backend\App\Action\Context                 $context
     * @param \Plumrocket\Newsletterpopup\Model\SubscriberEncoded $subscriberEncoded
     */
    public function __construct(
        Context $context,
        SubscriberEncoded $subscriberEncoded
    ) {
        $this->subscriberEncoded = $subscriberEncoded;
        parent::__construct($context);
    }

    /**
     * Add history.
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute(): Json
    {
        if ($actionText = $this->getRequest()->getParam('npaction')) {
            $string = new StringUtils;
            $actionText = $string->substr(strip_tags($actionText), 0, 200);
            $this->subscriberEncoded->history($actionText);
        }

        return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData(['error' => 0, 'messages' => []]);
    }
}
