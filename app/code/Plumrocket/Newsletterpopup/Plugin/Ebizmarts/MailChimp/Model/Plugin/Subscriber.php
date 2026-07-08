<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2020 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */
declare(strict_types=1);

namespace Plumrocket\Newsletterpopup\Plugin\Ebizmarts\MailChimp\Model\Plugin;

use Magento\Framework\App\Request\Http;
use Plumrocket\Newsletterpopup\Helper\Data;

/**
 * @since 4.0.1
 */
class Subscriber
{
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    private $request;

    /**
     * @param \Magento\Framework\App\Request\Http $request
     */
    public function __construct(Http $request)
    {
        $this->request = $request;
    }

    public function aroundBeforeSubscribe(
        \Ebizmarts\MailChimp\Model\Plugin\Subscriber $subject,
        \Closure $proceed,
        $subscriber,
        $email
    ) {
        if ($this->request->getRouteName() === Data::SECTION_ID) {
            return [$email];
        }

        return $proceed($subscriber, $email);
    }
}
