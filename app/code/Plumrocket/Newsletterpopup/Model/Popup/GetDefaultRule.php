<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2022 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\Newsletterpopup\Model\Popup;

use Plumrocket\Newsletterpopup\Model\Config\Source\Devices;
use Plumrocket\Newsletterpopup\Model\Popup\Condition\Combine;
use Plumrocket\Newsletterpopup\Model\Popup\Condition\General;

/**
 * @since 4.3.0
 */
class GetDefaultRule
{

    /**
     * Get popup default rule.
     *
     * @return array
     */
    public function execute(): array
    {
        return [
            'type' => Combine::class,
            'attribute' => null,
            'operator' => null,
            'value' => '1',
            'is_value_processed' => null,
            'aggregator' => 'all',
            'conditions' => [
                [
                    'type' => General::class,
                    'attribute' => 'current_device',
                    'operator' => '()',
                    'value' => [
                        Devices::DESKTOP,
                        Devices::TABLET
                    ],
                    'is_value_processed' => false,
                ]
            ],
        ];
    }
}
