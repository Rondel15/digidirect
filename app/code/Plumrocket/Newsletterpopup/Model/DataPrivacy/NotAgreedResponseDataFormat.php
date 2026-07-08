<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2020 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\Newsletterpopup\Model\DataPrivacy;

use Magento\Framework\Message\MessageInterface;

/**
 * Create specific json response for our js logic
 *
 * @since v3.10.0
 */
class NotAgreedResponseDataFormat
{
    /**
     * We use type "error" because our js know how to work only with this type
     *
     * @param $message
     * @return array
     */
    public function execute($message): array
    {
        return [
            'error' => 1,
            'messages' => [
                MessageInterface::TYPE_ERROR => [
                    $message
                ]
            ],
            'hasSuccessTextPlaceholders' => false,
        ];
    }
}
